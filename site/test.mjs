#!/usr/bin/env node
// Tes situs statis. Jalankan: node --test site/test.mjs
//
// Dua lapis: yang pertama menguji sumber (paritas key, key yatim, token), yang
// kedua menguji output yang benar-benar dihasilkan di frontend/. Jalankan
// `node site/build.mjs` lebih dulu kalau baru mengubah data atau template.

import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, readdirSync, statSync, existsSync } from 'node:fs';
import { join, dirname, sep } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const OUT = join(ROOT, 'frontend');
const LOCALES = ['id', 'en'];
const ORIGIN = 'https://mellogang.wyna.dev';

const read = (p) => readFileSync(p, 'utf8');
const STRINGS = JSON.parse(read(join(ROOT, 'site/data/strings.json')));
const PROJECTS = JSON.parse(read(join(ROOT, 'site/data/projects.json')));
const TEAM = JSON.parse(read(join(ROOT, 'site/data/team.json')));
const CSS = read(join(OUT, 'styles.css'));
const TOKENS = read(join(OUT, 'tokens.css'));

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
const localeOf = (p) => (urlOf(p).startsWith('/en/') ? 'en' : 'id');

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
  assert.deepEqual(broken, []);
});

test('paritas proyek: semua field dwibahasa terisi di kedua bahasa', () => {
  const broken = [];
  for (const p of PROJECTS) {
    for (const field of ['title', 'category', 'format', 'description', 'alt']) {
      for (const loc of LOCALES) {
        if (typeof p[field]?.[loc] !== 'string' || p[field][loc].trim() === '')
          broken.push(`${p.slug}.${field}.${loc}`);
      }
    }
  }
  assert.deepEqual(broken, []);
});

test('slug proyek unik, dan setiap oldSlug juga unik', () => {
  const slugs = PROJECTS.map((p) => p.slug);
  assert.equal(new Set(slugs).size, slugs.length, 'ada slug kembar');
  const old = PROJECTS.map((p) => p.oldSlug).filter(Boolean);
  assert.equal(new Set(old).size, old.length, 'ada oldSlug kembar');
});

test('token: setiap var(--…) yang dipakai stylesheet terdefinisi di tokens.css', () => {
  const defined = new Set([...TOKENS.matchAll(/(--[\w-]+)\s*:/g)].map((m) => m[1]));
  // var(--x, fallback) sah tanpa definisi token: nilainya dipasang runtime oleh
  // script (lensa tim memakai --lx/--ly), dan fallback-nya sudah ada di CSS.
  const used = new Set([...CSS.matchAll(/var\((--[\w-]+)\s*(,?)/g)].filter((m) => m[2] !== ',').map((m) => m[1]));
  const missing = [...used].filter((v) => !defined.has(v)).sort();
  assert.deepEqual(missing, [], `token tidak terdefinisi: ${missing.join(', ')}`);
});

test('font di-self-host: nol rujukan CDN, semua woff2 ada di disk', () => {
  assert.ok(!/fonts\.(googleapis|gstatic)\.com/.test(CSS), 'styles.css masih menunjuk Google Fonts');
  assert.ok(!/@import/.test(CSS), 'styles.css memakai @import');
  const urls = [...CSS.matchAll(/url\('?([^')]+\.woff2)'?\)/g)].map((m) => m[1]);
  assert.ok(urls.length >= 4, `hanya ${urls.length} woff2 dirujuk`);
  const missing = urls.filter((u) => !existsSync(join(OUT, u)));
  assert.deepEqual(missing, [], `file font hilang: ${missing.join(', ')}`);
});

// ---------------------------------------------------------------- output

test('output ada: satu halaman per rute per bahasa, plus 404, sitemap, robots', () => {
  const perLocale = 4 + PROJECTS.length; // home, portfolio, about, book + detail
  assert.equal(pages.length, perLocale * LOCALES.length);
  for (const f of ['404.html', 'sitemap.xml', 'robots.txt', 'vercel.json', 'tokens.css', 'styles.css', 'script.js'])
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
    // Dicek atas teks yang sudah distrip tag: markup memecah frasa
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
  const grab = (s, re) =>
    (s.match(re) ?? ['HILANG'])[0].replace(/ aria-current="page"/g, '').replace(/href="\/(en\/)?/g, 'href="/');
  for (const [name, re] of [
    ['nav-links', /<nav class="nav-links"[\s\S]*?<\/nav>/],
    ['mobile-menu', /<nav class="mobile-menu"[\s\S]*?<\/nav>/],
    ['footer-nav', /<nav class="footer-nav"[\s\S]*?<\/nav>/],
  ]) {
    for (const loc of LOCALES) {
      const subset = pages.filter((p) => localeOf(p) === loc);
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
    if (canonical !== ORIGIN + urlOf(f)) bad.push(`${urlOf(f)}: canonical ${canonical}`);
    if (!og.startsWith('https://')) bad.push(`${urlOf(f)}: og:image relatif`);
  }
  assert.deepEqual(bad, []);
});

test('<html lang> cocok dengan cabang URL-nya', () => {
  const bad = pages.filter((f) => !read(f).includes(`<html lang="${localeOf(f)}">`)).map(urlOf);
  assert.deepEqual(bad, []);
});

test('setiap halaman memuat tokens.css sebelum styles.css', () => {
  const bad = [];
  for (const f of htmlFiles) {
    const s = read(f);
    const t = s.indexOf('/tokens.css');
    const c = s.indexOf('/styles.css');
    if (t === -1 || c === -1 || t > c) bad.push(urlOf(f));
  }
  assert.deepEqual(bad, [], 'tokens.css harus di-link dan berada sebelum styles.css');
});

test('nol rujukan font atau skrip pihak ketiga di seluruh output', () => {
  const bad = [];
  for (const f of htmlFiles) {
    const s = read(f);
    if (/fonts\.(googleapis|gstatic)\.com/.test(s)) bad.push(`${urlOf(f)}: google fonts`);
    if (/<script[^>]+src="https?:/.test(s)) bad.push(`${urlOf(f)}: skrip eksternal`);
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
      const target = /\.[a-z0-9]+$/i.test(href) ? join(OUT, href) : join(OUT, href, 'index.html');
      if (!existsSync(target)) missing.push(`${urlOf(f)} -> ${href}`);
    }
  }
  assert.deepEqual(missing, []);
});

test('setiap cover dan frame galeri proyek ada di disk', () => {
  const missing = [];
  for (const p of PROJECTS) {
    if (!existsSync(join(OUT, p.cover))) missing.push(`${p.slug}: cover ${p.cover}`);
    for (const g of p.gallery ?? []) if (!existsSync(join(OUT, g))) missing.push(`${p.slug}: galeri ${g}`);
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
  assert.deepEqual(missing, []);
  for (const r of vercel.redirects) {
    if (r.destination.startsWith('/') && !/\.[a-z0-9]+$/i.test(r.destination))
      assert.ok(existsSync(join(OUT, r.destination, 'index.html')), `redirect ke halaman tak ada: ${r.destination}`);
  }
});

test('sitemap memuat semua halaman terindeks dengan alternate hreflang', () => {
  const xml = read(join(OUT, 'sitemap.xml'));
  for (const f of pages) assert.ok(xml.includes(`<loc>${ORIGIN}${urlOf(f)}</loc>`), `sitemap kurang ${urlOf(f)}`);
  assert.ok(!xml.includes('404'), 'sitemap tidak boleh memuat halaman 404');
  assert.equal((xml.match(/<url>/g) ?? []).length, pages.length);
});

test('alt gambar mendeskripsikan isi foto, bukan nama studio', () => {
  const lazy = [];
  for (const f of htmlFiles) {
    for (const m of read(f).matchAll(/<img[^>]*alt="([^"]*)"/g)) {
      const alt = m[1].trim();
      if (alt === '') continue; // dekoratif, sudah aria-hidden atau punya label di induknya
      if (/mellogang/i.test(alt)) lazy.push(`${urlOf(f)}: "${alt}"`);
    }
  }
  assert.deepEqual(lazy, [], `alt masih memakai nama studio: ${lazy.join(' | ')}`);
});

test('setiap class yang dipakai markup punya aturan di styles.css', () => {
  // Justru bug inilah yang membuat 8 halaman generasi lama tayang tanpa style:
  // markup-nya memakai class yang sudah dihapus dari CSS.
  const defined = new Set([...CSS.matchAll(/\.([A-Za-z][\w-]*)/g)].map((m) => m[1]));
  const used = new Set();
  for (const f of htmlFiles) {
    const body = read(f).replace(/<script[\s\S]*?<\/script>/g, '');
    for (const m of body.matchAll(/class="([^"]+)"/g)) for (const c of m[1].split(/\s+/)) used.add(c);
  }
  const missing = [...used].filter((c) => !defined.has(c)).sort();
  assert.deepEqual(missing, [], `class tanpa aturan CSS: ${missing.join(', ')}`);
});

test('section tim: kalau fotonya ada maka dirender, kalau tidak maka disembunyikan', () => {
  const ready = TEAM.every((m) => existsSync(join(OUT, m.photo)) && existsSync(join(OUT, m.candid)));
  const about = read(join(OUT, 'about', 'index.html'));
  if (ready) {
    assert.ok(about.includes('class="person__photo"'), 'foto tim ada tapi section tidak dirender');
    for (const m of TEAM) assert.ok(about.includes(m.photo), `foto ${m.key} tidak dirujuk`);
  } else {
    assert.ok(!about.includes('class="person__photo"'), 'foto tim belum ada tapi kartu tetap dirender');
  }
});
