#!/usr/bin/env node
// Tes situs statis. Jalankan: node --test site/
//
// Dua lapis: yang pertama menguji sumber (paritas key, key yatim), yang kedua
// menguji output yang benar-benar dihasilkan di frontend/. Jalankan
// `node site/build.mjs` lebih dulu kalau baru mengubah data atau template.

import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, readdirSync, statSync, existsSync } from 'node:fs';
import { join, dirname, sep } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const OUT = join(ROOT, 'frontend');
const LOCALES = ['id', 'en'];

const read = (p) => readFileSync(p, 'utf8');
const STRINGS = JSON.parse(read(join(ROOT, 'site/data/strings.json')));
const PROJECTS = JSON.parse(read(join(ROOT, 'site/data/projects.json')));

function walk(dir, ext, acc = []) {
  for (const name of readdirSync(dir)) {
    const p = join(dir, name);
    if (statSync(p).isDirectory()) walk(p, ext, acc);
    else if (name.endsWith(ext)) acc.push(p);
  }
  return acc;
}

const htmlFiles = walk(OUT, '.html').map((p) => p.split(sep).join('/'));
const pages = htmlFiles.filter((p) => !p.endsWith('404.html'));
const urlOf = (p) => '/' + p.slice(OUT.split(sep).join('/').length + 1).replace(/index\.html$/, '');

// ---------------------------------------------------------------- sumber

test('paritas ID/EN: setiap key punya kedua bahasa dan tidak kosong', () => {
  const broken = [];
  for (const [key, val] of Object.entries(STRINGS)) {
    for (const loc of LOCALES) {
      if (typeof val?.[loc] !== 'string' || val[loc].trim() === '') broken.push(`${key}.${loc}`);
    }
    for (const extra of Object.keys(val ?? {})) {
      if (!LOCALES.includes(extra)) broken.push(`${key}.${extra} (bahasa tak dikenal)`);
    }
  }
  assert.deepEqual(broken, [], `key timpang: ${broken.join(', ')}`);
});

test('paritas proyek: judul, kategori, deskripsi, dan alt ada di kedua bahasa', () => {
  const broken = [];
  for (const p of PROJECTS) {
    for (const field of ['title', 'category', 'description', 'alt']) {
      for (const loc of LOCALES) {
        if (typeof p[field]?.[loc] !== 'string' || p[field][loc].trim() === '')
          broken.push(`${p.slug}.${field}.${loc}`);
      }
    }
  }
  assert.deepEqual(broken, []);
});

test('tidak ada key yatim: semua key dipakai, semua yang dipakai terdefinisi', () => {
  const templates = walk(join(ROOT, 'site/templates'), '.html').map(read).join(' ');
  const build = read(join(ROOT, 'site/build.mjs'));
  const config = read(join(ROOT, 'site/data/site.json'));

  const undefinedKeys = [];
  for (const m of templates.matchAll(/\{\{\{?\s*([\w.:-]+)\s*\}?\}\}/g)) {
    const key = m[1];
    // Nilai yang diisi build dari data, bukan dari kamus string.
    if (/^(page|site|og)\./.test(key)) continue;
    if (key in STRINGS) continue;
    if (build.includes(`ctx['${key}']`) || build.includes(`'${key}':`)) continue;
    undefinedKeys.push(key);
  }
  assert.deepEqual(undefinedKeys, [], `dipakai tapi tidak terdefinisi: ${undefinedKeys.join(', ')}`);

  // Sebagian key dibentuk dinamis di build (`meta.${page.name}.title`,
  // `portfolio.filter.${tag}`), sebagian lagi disebut lewat site.json
  // (projectTypes). Pola template-literal diubah jadi regex supaya key yang
  // dipakai secara dinamis tidak salah dilaporkan sebagai yatim.
  const dynamic = [...build.matchAll(/[st]\[`([^`]+)`\]/g)].map(
    (m) => new RegExp('^' + m[1].replace(/[.]/g, '[.]').replace(/\$\{[^}]+\}/g, '[A-Za-z0-9_.-]+') + '$')
  );

  const unused = Object.keys(STRINGS).filter(
    (k) => !templates.includes(k) && !build.includes(`'${k}'`) && !config.includes(`"${k}"`)
      && !dynamic.some((re) => re.test(k))
  );
  assert.deepEqual(unused, [], `terdefinisi tapi tidak pernah dipakai: ${unused.join(', ')}`);
});

// ---------------------------------------------------------------- output

test('output ada: 14 halaman per bahasa, plus 404, sitemap, robots', () => {
  assert.equal(pages.length, 28, `harusnya 28 halaman, dapat ${pages.length}`);
  for (const f of ['404.html', 'sitemap.xml', 'robots.txt', 'vercel.json'])
    assert.ok(existsSync(join(OUT, f)), `${f} tidak ada`);
});

test('tidak ada placeholder atau key i18n yang bocor ke output', () => {
  const leaked = [];
  for (const f of htmlFiles) {
    const body = read(f).replace(/<script[\s\S]*?<\/script>/g, '');
    if (/\{\{/.test(body) || /data-i18n/.test(body)) leaked.push(urlOf(f));
  }
  assert.deepEqual(leaked, []);
});

test('tidak ada karakter nyasar atau isi setelah </html>', () => {
  const dirty = [];
  for (const f of htmlFiles) {
    const s = read(f);
    if (/[Ѐ-ӿ؀-ۿ一-鿿਀-੿]/.test(s)) dirty.push(`${urlOf(f)} (non-latin)`);
    const i = s.lastIndexOf('</html>');
    if (i === -1 || s.slice(i + 7).trim() !== '') dirty.push(`${urlOf(f)} (sisa setelah </html>)`);
  }
  assert.deepEqual(dirty, []);
});

test('copy yang ditolak klien tidak muncul lagi', () => {
  const BANNED = [
    'Visuals with purpose', 'Stories, carefully made', 'Clear work', 'Good people',
    'A focused team', 'behind the frame', 'Professional from brief to delivery',
    'Clear process', 'Consistent work', 'Start with the details',
    'A clear brief makes a better production', 'Built around your project',
    'talk dates', 'an honest visual point of view', 'images that remain useful',
    'calm communication', 'a clean finish that serves the brief',
    'not submitted to a database',
  ];
  const found = [];
  for (const f of htmlFiles) {
    // dicek atas teks yang sudah distrip tag: markup memecah frasa
    // ("Visuals<br>with purpose") sehingga grep polos memberi false negative.
    const text = read(f)
      .replace(/<script[\s\S]*?<\/script>/g, ' ')
      .replace(/<[^>]+>/g, ' ')
      .replace(/\s+/g, ' ');
    for (const b of BANNED) if (text.toLowerCase().includes(b.toLowerCase())) found.push(`${urlOf(f)}: ${b}`);
  }
  assert.deepEqual(found, []);
});

test('nav dan footer identik strukturnya di semua halaman satu bahasa', () => {
  const grab = (s, re) => (s.match(re) ?? ['HILANG'])[0].replace(/ aria-current="page"/g, '').replace(/href="\/(en\/)?/g, 'href="/');
  for (const [name, re] of [
    ['nav-links', /<nav class="nav-links"[\s\S]*?<\/nav>/],
    ['mobile-menu', /<nav class="mobile-menu"[\s\S]*?<\/nav>/],
    ['footer-nav', /<nav class="footer-nav"[\s\S]*?<\/nav>/],
  ]) {
    for (const loc of LOCALES) {
      const subset = pages.filter((p) => (urlOf(p).startsWith('/en/') ? 'en' : 'id') === loc);
      const variants = new Set(subset.map((p) => grab(read(p), re)));
      assert.equal(variants.size, 1, `${name} punya ${variants.size} varian di bahasa ${loc}`);
    }
  }
});

test('setiap halaman punya canonical, hreflang, OG, twitter, dan JSON-LD', () => {
  const need = ['<link rel="canonical"', 'hreflang="id"', 'hreflang="en"', 'hreflang="x-default"',
    'og:title', 'og:description', 'og:image', 'og:url', 'twitter:card', 'application/ld+json'];
  const missing = [];
  for (const f of pages) {
    const s = read(f);
    for (const tag of need) if (!s.includes(tag)) missing.push(`${urlOf(f)}: ${tag}`);
  }
  assert.deepEqual(missing, []);
});

test('canonical dan og:image absolut, dan canonical cocok dengan lokasi file', () => {
  const bad = [];
  for (const f of pages) {
    const s = read(f);
    const canonical = s.match(/canonical" href="([^"]+)"/)[1];
    const og = s.match(/og:image" content="([^"]+)"/)[1];
    if (canonical !== `https://mellogang.wyna.dev${urlOf(f)}`) bad.push(`${urlOf(f)}: canonical ${canonical}`);
    if (!og.startsWith('https://')) bad.push(`${urlOf(f)}: og:image relatif`);
  }
  assert.deepEqual(bad, []);
});

test('<html lang> cocok dengan cabang URL-nya', () => {
  const bad = [];
  for (const f of pages) {
    const expect = urlOf(f).startsWith('/en/') ? 'en' : 'id';
    if (!read(f).includes(`<html lang="${expect}">`)) bad.push(urlOf(f));
  }
  assert.deepEqual(bad, []);
});

test('meta description unik di setiap halaman', () => {
  const seen = new Map();
  for (const f of pages) {
    const d = read(f).match(/name="description" content="([^"]+)"/)[1];
    if (seen.has(d)) assert.fail(`description kembar: ${urlOf(f)} dan ${seen.get(d)}`);
    seen.set(d, urlOf(f));
  }
});

test('semua link dan aset internal menunjuk file yang ada', () => {
  const missing = [];
  for (const f of htmlFiles) {
    for (const m of read(f).matchAll(/(?:href|src)="(\/[^"#?]*)"/g)) {
      const href = m[1];
      const target = /\.[a-z0-9]+$/i.test(href)
        ? join(OUT, href)
        : join(OUT, href, 'index.html');
      if (!existsSync(target)) missing.push(`${urlOf(f)} -> ${href}`);
    }
  }
  assert.deepEqual(missing, []);
});

test('setiap slug lama punya redirect, dan tujuannya benar-benar ada', () => {
  const vercel = JSON.parse(read(join(OUT, 'vercel.json')));
  const sources = new Set(vercel.redirects.map((r) => r.source));
  const missing = [];
  for (const p of PROJECTS) {
    if (!p.oldSlug) continue;
    for (const loc of LOCALES) {
      const prefix = loc === 'id' ? '' : '/en';
      if (!sources.has(`${prefix}/portfolio/${p.oldSlug}/:path*`)) missing.push(`${loc}: ${p.oldSlug}`);
    }
  }
  assert.deepEqual(missing, [], `slug lama tanpa redirect: ${missing.join(', ')}`);

  for (const r of vercel.redirects) {
    if (r.destination.startsWith('/') && !/\.[a-z0-9]+$/i.test(r.destination))
      assert.ok(existsSync(join(OUT, r.destination, 'index.html')), `redirect ke halaman tak ada: ${r.destination}`);
  }
});

test('sitemap memuat semua halaman terindeks dengan alternate hreflang', () => {
  const xml = read(join(OUT, 'sitemap.xml'));
  for (const f of pages) {
    assert.ok(xml.includes(`<loc>https://mellogang.wyna.dev${urlOf(f)}</loc>`), `sitemap kurang ${urlOf(f)}`);
  }
  assert.ok(!xml.includes('404'), 'sitemap tidak boleh memuat halaman 404');
  assert.equal((xml.match(/<url>/g) ?? []).length, pages.length);
});

test('alt gambar mendeskripsikan isi foto, bukan nama studio', () => {
  const lazy = [];
  for (const f of htmlFiles) {
    for (const m of read(f).matchAll(/<img[^>]*alt="([^"]*)"/g)) {
      const alt = m[1].trim();
      if (alt === '') continue; // gambar dekoratif, sudah punya aria-label di induknya
      if (/^mellogangvisuals$/i.test(alt)) continue; // logo: nama merek memang isinya
      if (/mellogang/i.test(alt)) lazy.push(`${urlOf(f)}: "${alt}"`);
    }
  }
  assert.deepEqual(lazy, [], `alt masih memakai nama studio: ${lazy.join(' | ')}`);
});
