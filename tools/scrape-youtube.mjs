#!/usr/bin/env node
/**
 * scrape-youtube.mjs — MellogangVisuals (situs statis)
 *
 * Ambil daftar video publik dari kanal YouTube MellogangVisuals lalu tulis
 * `site/data/youtube.json` + unduh cover tiap video ke
 * `frontend/assets/video/frames/yt-<id>.jpg`.
 *
 * KENAPA TERPISAH DARI tools/social-fetcher/worker.js?
 * `worker.js` itu milik aplikasi CodeIgniter, bukan milik situs statis:
 *   - dipanggil Admin\SocialController dengan `--job=<id> --api=<baseURL>`,
 *     jadi dia butuh baris antrian di tabel `social_fetch_job` supaya jalan;
 *   - hasilnya dikirim balik lewat HTTP POST/PATCH ke API PHP untuk di-cache
 *     di tabel `social_post` — artinya butuh server + database hidup;
 *   - dia CommonJS dan bergantung pada Playwright + minimist-mini.
 * Build situs statis tidak punya PHP, database, maupun job id. Yang dibutuhkan
 * cuma satu berkas JSON di disk yang bisa dibaca saat build, dan harus bisa
 * dijalankan ulang kapan saja tanpa efek samping. Karena itu skrip ini berdiri
 * sendiri: ESM, tanpa dependensi npm, output ke file.
 *
 * Pemakaian:
 *   node tools/scrape-youtube.mjs                # scrape + unduh thumbnail
 *   node tools/scrape-youtube.mjs --skip-thumbs  # JSON saja
 *   node tools/scrape-youtube.mjs --force-thumbs # unduh ulang semua thumbnail
 *
 * Sifat idempotent: JSON ditulis ulang apa adanya, dan thumbnail yang sudah ada
 * + valid dilewati. Frame lama milik proyek (mis. `indra-suci-88.jpg`) tidak
 * pernah tertimpa karena berkas hasil skrip ini selalu berprefiks `yt-`.
 */

import { execFile } from 'node:child_process';
import { promisify } from 'node:util';
import { writeFile, mkdir, stat, rename, unlink, open } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const execFileAsync = promisify(execFile);

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const CHANNEL_URL = process.env.YT_CHANNEL_URL || 'https://www.youtube.com/@mellogangvisuals/videos';
const OUT_JSON = path.join(ROOT, 'site', 'data', 'youtube.json');
const FRAMES_DIR = path.join(ROOT, 'frontend', 'assets', 'video', 'frames');
const THUMB_WEB_PREFIX = '/assets/video/frames';
// curl tetap menyimpan badan respons error, jadi apa pun di bawah 5KB dianggap gagal.
const MIN_THUMB_BYTES = 5 * 1024;

const args = new Set(process.argv.slice(2));
const SKIP_THUMBS = args.has('--skip-thumbs');
const FORCE_THUMBS = args.has('--force-thumbs');

/** Tulis JSON dengan indent 2 spasi, newline LF, diakhiri newline. */
async function writeJson(file, data) {
  const text = JSON.stringify(data, null, 2).replace(/\r\n/g, '\n') + '\n';
  await mkdir(path.dirname(file), { recursive: true });
  await writeFile(file, text, 'utf8');
}

/** Jalankan yt-dlp; lempar error yang jelas kalau gagal. */
async function fetchChannel() {
  const ytArgs = ['--no-cache-dir', '--flat-playlist', '--skip-download', '-J', CHANNEL_URL];
  let stdout;
  try {
    // stderr sengaja diabaikan: yt-dlp di mesin ini menulis warning Python
    // (RequestsDependencyWarning) walaupun exit code-nya 0.
    ({ stdout } = await execFileAsync('yt-dlp', ytArgs, {
      maxBuffer: 64 * 1024 * 1024,
      windowsHide: true,
    }));
  } catch (err) {
    const detail = String(err.stderr || err.message || '').trim();
    throw new Error(
      'yt-dlp gagal membaca ' + CHANNEL_URL + '\n' +
        '  perintah: yt-dlp ' + ytArgs.join(' ') + '\n' +
        '  exit code: ' + (err.code ?? 'n/a') + '\n' +
        '  pesan: ' + (detail || '(tidak ada output stderr)')
    );
  }

  let payload;
  try {
    payload = JSON.parse(stdout);
  } catch (err) {
    throw new Error('Output yt-dlp bukan JSON yang valid: ' + err.message);
  }

  const entries = Array.isArray(payload.entries) ? payload.entries : [];
  if (entries.length === 0) {
    throw new Error('yt-dlp jalan tapi tidak menemukan satu pun video di ' + CHANNEL_URL);
  }

  return {
    channel: payload.channel || payload.uploader || payload.title || 'mellogangvisuals',
    entries,
  };
}

/**
 * Normalisasi entri kanal. Urutan dari yt-dlp sudah "terbaru dulu", jadi kita
 * pertahankan apa adanya — sort ulang justru berisiko mengacak urutan karena
 * `timestamp` tidak selalu terisi pada mode --flat-playlist.
 */
function normalize(entries) {
  const seen = new Set();
  const videos = [];
  for (const entry of entries) {
    const id = typeof entry.id === 'string' ? entry.id.trim() : '';
    if (!id || seen.has(id)) continue;
    seen.add(id);
    videos.push({
      id,
      title: String(entry.title || '').trim(),
      duration: Number.isFinite(entry.duration) ? Math.round(entry.duration) : null,
      url: 'https://www.youtube.com/watch?v=' + id,
      thumb: THUMB_WEB_PREFIX + '/yt-' + id + '.jpg',
    });
  }
  return videos;
}

/** Berkas dianggap cover valid kalau JPEG (FF D8 FF) dan lebih besar dari 5KB. */
async function isValidJpeg(file) {
  let info;
  try {
    info = await stat(file);
  } catch {
    return false;
  }
  if (!info.isFile() || info.size < MIN_THUMB_BYTES) return false;

  const fh = await open(file, 'r');
  try {
    const buf = Buffer.alloc(3);
    const { bytesRead } = await fh.read(buf, 0, 3, 0);
    return bytesRead === 3 && buf[0] === 0xff && buf[1] === 0xd8 && buf[2] === 0xff;
  } finally {
    await fh.close();
  }
}

/** Unduh satu URL ke berkas tujuan; kembalikan HTTP status code sebagai string. */
async function curlTo(url, dest) {
  const { stdout } = await execFileAsync(
    'curl',
    ['-sS', '-L', '--max-time', '45', '-w', '%{http_code}', '-o', dest, url],
    { windowsHide: true }
  );
  return String(stdout || '').trim().slice(-3);
}

/**
 * Ambil cover terbaik: maxresdefault dulu, jatuh ke hqdefault kalau 404 atau
 * hasilnya bukan JPEG yang layak. Nama tujuan selalu `yt-<id>.jpg` sehingga
 * frame lama di FRAMES_DIR tidak pernah tertimpa.
 */
async function fetchThumb(id) {
  const dest = path.join(FRAMES_DIR, 'yt-' + id + '.jpg');
  if (!FORCE_THUMBS && (await isValidJpeg(dest))) {
    return { ok: true, source: 'cached', bytes: (await stat(dest)).size };
  }

  const tmp = dest + '.part';
  const candidates = [
    ['maxresdefault', 'https://i.ytimg.com/vi/' + id + '/maxresdefault.jpg'],
    ['hqdefault', 'https://i.ytimg.com/vi/' + id + '/hqdefault.jpg'],
  ];
  const tried = [];

  for (const [label, url] of candidates) {
    let status = '000';
    try {
      status = await curlTo(url, tmp);
    } catch (err) {
      tried.push(label + ': curl error ' + String(err.message || err).trim());
      await unlink(tmp).catch(() => {});
      continue;
    }
    if (status !== '200') {
      tried.push(label + ': HTTP ' + status);
      await unlink(tmp).catch(() => {});
      continue;
    }
    if (!(await isValidJpeg(tmp))) {
      let size = 0;
      try {
        size = (await stat(tmp)).size;
      } catch {}
      tried.push(label + ': bukan JPEG valid atau terlalu kecil (' + size + ' bytes)');
      await unlink(tmp).catch(() => {});
      continue;
    }
    await rename(tmp, dest);
    return { ok: true, source: label, bytes: (await stat(dest)).size };
  }

  await unlink(tmp).catch(() => {});
  return { ok: false, reason: tried.join('; ') };
}

async function main() {
  const { channel, entries } = await fetchChannel();
  const videos = normalize(entries);
  console.log('Kanal: ' + channel + ' — ' + videos.length + ' video');

  const failed = [];
  if (SKIP_THUMBS) {
    console.log('--skip-thumbs aktif, unduhan cover dilewati.');
  } else {
    await mkdir(FRAMES_DIR, { recursive: true });
    for (const video of videos) {
      const res = await fetchThumb(video.id);
      if (res.ok) {
        console.log('  ok    yt-' + video.id + '.jpg  ' + res.source + ', ' + res.bytes + ' bytes  — ' + video.title);
      } else {
        failed.push({ id: video.id, title: video.title, reason: res.reason });
        console.error('  GAGAL yt-' + video.id + '.jpg  — ' + video.title + ' :: ' + res.reason);
      }
    }
  }

  await writeJson(OUT_JSON, {
    fetchedAt: new Date().toISOString(),
    channel,
    videos,
  });
  console.log('Ditulis: ' + path.relative(ROOT, OUT_JSON) + ' (' + videos.length + ' video)');

  if (failed.length) {
    console.error('\n' + failed.length + ' cover gagal diunduh:');
    for (const f of failed) console.error('  - ' + f.id + ' :: ' + f.title);
    process.exitCode = 2;
  }
}

main().catch((err) => {
  console.error(err && err.message ? err.message : err);
  process.exit(1);
});
