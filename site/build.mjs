#!/usr/bin/env node
// Generator situs statis MellogangVisuals.
//
//   node site/build.mjs          -> render ke frontend/
//   node site/build.mjs --check  -> validasi saja, tidak menulis file
//
// Sumber kebenaran:
//   site/data/strings.json   semua string yang terlihat user, per key, ID + EN
//   site/data/projects.json  data proyek portofolio
//   site/data/site.json      konfigurasi route, domain, kontak
//   site/templates/*.html    struktur markup (tanpa teks yang terlihat user)
//
// Output ditulis ke frontend/ dan DI-COMMIT. Jangan sunting file di frontend/
// dengan tangan — jalankan generator ini.

import { readFileSync, writeFileSync, mkdirSync, rmSync, existsSync, readdirSync, statSync } from 'node:fs';
import { dirname, join, relative } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const SITE = join(ROOT, 'site');
const OUT = join(ROOT, 'frontend');
const CHECK_ONLY = process.argv.includes('--check');

const json = (p) => JSON.parse(readFileSync(join(SITE, 'data', p), 'utf8'));
const tpl = (n) => readFileSync(join(SITE, 'templates', `${n}.html`), 'utf8');

const STRINGS = json('strings.json');
const PROJECTS = json('projects.json');
const CONFIG = json('site.json');

const LOCALES = ['id', 'en'];
const errors = [];
const warnings = [];

// ---------------------------------------------------------------- validasi

// Paritas: setiap key wajib punya ID dan EN yang terisi. Ini yang bikin
// "key cuma ada di satu bahasa" tidak mungkin lolos ke output.
for (const [key, val] of Object.entries(STRINGS)) {
  if (typeof val !== 'object' || val === null) {
    errors.push(`strings.json: "${key}" bukan objek {id, en}`);
    continue;
  }
  for (const loc of LOCALES) {
    if (!(loc in val)) errors.push(`strings.json: "${key}" tidak punya "${loc}"`);
    else if (typeof val[loc] !== 'string' || val[loc].trim() === '')
      errors.push(`strings.json: "${key}.${loc}" kosong`);
  }
  const extra = Object.keys(val).filter((k) => !LOCALES.includes(k));
  if (extra.length) errors.push(`strings.json: "${key}" punya bahasa tak dikenal: ${extra.join(', ')}`);
}

for (const p of PROJECTS) {
  for (const field of ['title', 'category', 'description']) {
    for (const loc of LOCALES) {
      const v = p[field]?.[loc];
      if (typeof v !== 'string' || v.trim() === '')
        errors.push(`projects.json: "${p.slug}".${field}.${loc} kosong`);
    }
  }
  if (!p.cover) errors.push(`projects.json: "${p.slug}" tidak punya cover`);
}

// --------------------------------------------------------------- rendering

const ESC = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' };
const esc = (s) => String(s).replace(/[&<>"]/g, (c) => ESC[c]);

// {{key}} di-escape, {{{key}}} mentah (untuk nilai yang memang berisi markup).
function render(source, ctx, where) {
  return source
    .replace(/\{\{\{\s*([\w.:-]+)\s*\}\}\}/g, (_, k) => lookup(k, ctx, where, true))
    .replace(/\{\{\s*([\w.:-]+)\s*\}\}/g, (_, k) => esc(lookup(k, ctx, where, false)));
}

function lookup(key, ctx, where, raw) {
  if (key in ctx) return ctx[key];
  errors.push(`${where}: key tidak dikenal "${key}"`);
  return '';
}

// Konteks string untuk satu locale, key i18n dipakai apa adanya.
function stringsFor(locale) {
  const out = {};
  for (const [key, val] of Object.entries(STRINGS)) out[key] = val[locale];
  return out;
}

// ------------------------------------------------------------------ routes

// Strategi B: Indonesia di root, Inggris di /en/. Diputuskan di DECISIONS.md §12.
const prefixOf = (locale) => (locale === CONFIG.defaultLocale ? '' : `${locale}/`);
const urlOf = (locale, path) => `/${prefixOf(locale)}${path}`;
const absOf = (locale, path) => `${CONFIG.origin}${urlOf(locale, path)}`;

const PAGES = [
  { name: 'home', path: '', template: 'home' },
  { name: 'portfolio', path: 'portfolio/', template: 'portfolio' },
  { name: 'about', path: 'about/', template: 'about' },
  { name: 'book', path: 'book/', template: 'book' },
  ...PROJECTS.map((p) => ({
    name: `project:${p.slug}`,
    path: `portfolio/${p.slug}/`,
    template: 'project',
    project: p,
  })),
];

// --------------------------------------------------------------- fragments

function navLinks(locale, activePath, s) {
  const items = [
    ['', s['nav.home']],
    ['portfolio/', s['nav.portfolio']],
    ['about/', s['nav.about']],
    ['book/', s['nav.book']],
  ];
  return items
    .map(([path, label]) => {
      // Halaman detail portofolio menandai "Portofolio" sebagai lokasi saat ini.
      const active = path === activePath || (path === 'portfolio/' && activePath.startsWith('portfolio/'));
      const cur = active ? ' aria-current="page"' : '';
      return `<a href="${urlOf(locale, path)}"${cur}>${esc(label)}</a>`;
    })
    .join('');
}

function langSwitch(locale, path, s) {
  return LOCALES.map((loc) => {
    const active = loc === locale;
    const label = loc.toUpperCase();
    const title = s[`lang.switch.${loc}`];
    if (active) {
      return `<span class="lang-btn active" aria-current="true" title="${esc(title)}">${label}</span>`;
    }
    return `<a class="lang-btn" href="${urlOf(loc, path)}" hreflang="${loc}" title="${esc(title)}">${label}</a>`;
  }).join('');
}

function headAlternates(path) {
  const rows = LOCALES.map(
    (loc) => `<link rel="alternate" hreflang="${loc}" href="${absOf(loc, path)}">`
  );
  rows.push(`<link rel="alternate" hreflang="x-default" href="${absOf(CONFIG.defaultLocale, path)}">`);
  return rows.join('');
}

// ------------------------------------------------------------ page builders

function projectCard(locale, p, s) {
  const meta = p.year ? `${p.category[locale]} · ${p.year}` : p.category[locale];
  return (
    `<a class="work-card reveal" data-tags="${p.tags.join(' ')}" href="${urlOf(locale, `portfolio/${p.slug}/`)}">` +
    `<img src="${p.cover}" alt="${esc(p.alt[locale])}" loading="lazy" width="1280" height="720">` +
    `<span class="work-arrow" aria-hidden="true">↗</span>` +
    `<div class="work-info"><small>${esc(meta)}</small><h3>${esc(p.title[locale])}</h3>` +
    `<p>${esc(s[p.video ? 'work.format.film' : 'work.format.photo'])}</p></div></a>`
  );
}

function galleryFigures(p, locale) {
  return (p.gallery || [])
    .map(
      (src, i) =>
        `<img src="${src}" alt="${esc(p.alt[locale])} — ${i + 1}" loading="lazy" width="1280" height="720">`
    )
    .join('');
}

function videoEmbed(p, s) {
  if (!p.video) return '';
  return (
    `<iframe src="https://www.youtube-nocookie.com/embed/${p.video}" ` +
    `title="${esc(p.title.id)}" loading="lazy" allowfullscreen ` +
    `referrerpolicy="strict-origin-when-cross-origin"></iframe>`
  );
}

// ------------------------------------------------------------------- build

const layout = tpl('layout');
const written = [];

function buildPage(locale, page) {
  const s = stringsFor(locale);
  const path = page.path;
  const p = page.project;

  const ctx = { ...s };

  // Meta per halaman.
  if (p) {
    ctx['page.title'] = `${p.title[locale]} — ${CONFIG.brand}`;
    ctx['page.description'] = p.description[locale];
    ctx['page.ogImage'] = CONFIG.origin + p.cover;
  } else {
    ctx['page.title'] = s[`meta.${page.name}.title`];
    ctx['page.description'] = s[`meta.${page.name}.description`];
    ctx['page.ogImage'] = CONFIG.origin + CONFIG.ogImage;
  }
  ctx['page.canonical'] = absOf(locale, path);
  ctx['page.lang'] = locale;
  ctx['page.alternates'] = headAlternates(path);
  ctx['page.nav'] = navLinks(locale, path, s);
  ctx['page.langSwitch'] = langSwitch(locale, path, s);
  ctx['page.bookUrl'] = urlOf(locale, 'book/');
  ctx['page.homeUrl'] = urlOf(locale, '');
  ctx['page.portfolioUrl'] = urlOf(locale, 'portfolio/');
  ctx['page.aboutUrl'] = urlOf(locale, 'about/');
  ctx['page.bookCta'] = page.name === 'book' ? '' :
    `<a class="btn btn-primary btn-small" href="${urlOf(locale, 'book/')}">${esc(s['nav.book'])}</a>`;
  ctx['og.locale'] = CONFIG.ogLocale[locale];
  ctx['site.brand'] = CONFIG.brand;
  ctx['site.whatsapp'] = CONFIG.whatsapp;
  ctx['site.email'] = CONFIG.email;
  ctx['site.instagram'] = CONFIG.instagram;
  ctx['site.youtube'] = CONFIG.youtube;
  ctx['site.year'] = String(CONFIG.year);

  // Data per template.
  if (page.template === 'home') {
    ctx['home.cards'] = PROJECTS.filter((x) => x.featured)
      .map((x) => projectCard(locale, x, s))
      .join('');
  }
  if (page.template === 'portfolio') {
    ctx['portfolio.cards'] = PROJECTS.map((x) => projectCard(locale, x, s)).join('');
    ctx['portfolio.filters'] = CONFIG.filters
      .map((tag, i) => {
        const label = s[`portfolio.filter.${tag}`];
        return `<button class="filter${i === 0 ? ' active' : ''}" type="button" data-filter="${tag}" aria-pressed="${i === 0}">${esc(label)}</button>`;
      })
      .join('');
  }
  if (page.template === 'project') {
    ctx['project.title'] = p.title[locale];
    ctx['project.category'] = p.category[locale];
    ctx['project.description'] = p.description[locale];
    ctx['project.cover'] = p.cover;
    ctx['project.alt'] = p.alt[locale];
    ctx['project.year'] = p.year || '';
    ctx['project.format'] = s[p.video ? 'work.format.film' : 'work.format.photo'];
    ctx['project.video'] = videoEmbed(p, s);
    ctx['project.gallery'] = galleryFigures(p, locale);
    ctx['project.sourceUrl'] = p.source;
    ctx['project.sourceLabel'] = s[p.video ? 'project.source.youtube' : 'project.source.instagram'];
    ctx['project.yearRow'] = p.year
      ? `<span>${esc(s['project.meta.year'])} <strong>${esc(p.year)}</strong></span>`
      : '';
    // Prev/next mengikuti urutan projects.json.
    const i = PROJECTS.indexOf(p);
    const prev = PROJECTS[i - 1];
    const next = PROJECTS[i + 1];
    ctx['project.prev'] = prev
      ? `<a class="detail-nav-prev" href="${urlOf(locale, `portfolio/${prev.slug}/`)}" rel="prev">` +
        `<small>${esc(s['project.nav.prev'])}</small><span>${esc(prev.title[locale])}</span></a>`
      : '';
    ctx['project.next'] = next
      ? `<a class="detail-nav-next" href="${urlOf(locale, `portfolio/${next.slug}/`)}" rel="next">` +
        `<small>${esc(s['project.nav.next'])}</small><span>${esc(next.title[locale])}</span></a>`
      : '';
  }
  if (page.template === 'book') {
    ctx['book.options'] = CONFIG.projectTypes
      .map((key) => `<option value="${esc(s[key])}">${esc(s[key])}</option>`)
      .join('');
    ctx['book.waTemplate'] = s['book.wa.template'];
    ctx['book.waPlaceholderName'] = s['book.wa.placeholder.name'];
    ctx['book.waPlaceholderDate'] = s['book.wa.placeholder.date'];
    ctx['book.waPlaceholderLocation'] = s['book.wa.placeholder.location'];
    ctx['book.waNotes'] = s['book.wa.notes'];
    ctx['book.dateLocale'] = locale === 'id' ? 'id-ID' : 'en-GB';
  }

  const body = render(tpl(page.template), ctx, `templates/${page.template}.html (${locale})`);
  ctx['page.body'] = body;
  const html = render(layout, ctx, `templates/layout.html (${locale}/${page.name})`);

  const dir = join(OUT, prefixOf(locale), path);
  written.push({ file: join(dir, 'index.html'), html, url: urlOf(locale, path) });
}

for (const locale of LOCALES) for (const page of PAGES) buildPage(locale, page);

// 404: satu file di root output. Vercel menyajikannya untuk semua path tak dikenal,
// termasuk di bawah /en/, jadi halamannya memilih bahasa dari pathname.
{
  const ctxFor = (locale) => {
    const s = stringsFor(locale);
    return {
      ...s,
      'page.homeUrl': urlOf(locale, ''),
      'page.portfolioUrl': urlOf(locale, 'portfolio/'),
    };
  };
  const blocks = LOCALES.map((locale) => {
    const inner = render(tpl('404-block'), ctxFor(locale), `templates/404-block.html (${locale})`);
    return `<div class="nf-block" data-nf-lang="${locale}"${locale === CONFIG.defaultLocale ? '' : ' hidden'}>${inner}</div>`;
  }).join('');
  const s = stringsFor(CONFIG.defaultLocale);
  const html = render(tpl('404'), {
    ...s,
    'site.brand': CONFIG.brand,
    'site.year': String(CONFIG.year),
    'page.lang': CONFIG.defaultLocale,
    'nf.blocks': blocks,
  }, 'templates/404.html');
  written.push({ file: join(OUT, '404.html'), html, url: '/404.html', noindex: true });
}

// sitemap.xml
{
  const urls = written
    .filter((w) => !w.noindex)
    .map((w) => {
      const path = w.url.replace(/^\/(en\/)?/, '');
      const alts = LOCALES.map(
        (loc) => `    <xhtml:link rel="alternate" hreflang="${loc}" href="${absOf(loc, path)}"/>`
      ).join('\n');
      return (
        `  <url>\n    <loc>${CONFIG.origin}${w.url}</loc>\n${alts}\n` +
        `    <xhtml:link rel="alternate" hreflang="x-default" href="${absOf(CONFIG.defaultLocale, path)}"/>\n  </url>`
      );
    })
    .join('\n');
  const xml =
    `<?xml version="1.0" encoding="UTF-8"?>\n` +
    `<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">\n` +
    `${urls}\n</urlset>\n`;
  written.push({ file: join(OUT, 'sitemap.xml'), html: xml, url: '/sitemap.xml', raw: true });
}

// vercel.json — dihasilkan supaya redirect slug tidak mungkin melenceng dari
// projects.json. Halaman lama (/packages/**, /workflow/, /contact/) sudah dihapus
// dan dialihkan 308 karena sudah terindeks.
{
  const redirects = [];
  for (const [from, to] of Object.entries(CONFIG.legacyRedirects)) {
    redirects.push({ source: `${from}/:path*`, destination: to, permanent: true });
  }
  for (const p of PROJECTS) {
    if (!p.oldSlug) continue;
    for (const loc of LOCALES) {
      redirects.push({
        source: `${urlOf(loc, `portfolio/${p.oldSlug}`)}/:path*`,
        destination: urlOf(loc, `portfolio/${p.slug}/`),
        permanent: true,
      });
    }
  }
  const config = {
    cleanUrls: true,
    trailingSlash: true,
    redirects,
    headers: [
      {
        source: '/(.*)',
        headers: [
          { key: 'X-Content-Type-Options', value: 'nosniff' },
          { key: 'Referrer-Policy', value: 'strict-origin-when-cross-origin' },
          { key: 'X-Frame-Options', value: 'SAMEORIGIN' },
          { key: 'Strict-Transport-Security', value: 'max-age=31536000; includeSubDomains' },
        ],
      },
      {
        source: '/assets/(.*)',
        headers: [{ key: 'Cache-Control', value: 'public, max-age=31536000, immutable' }],
      },
    ],
  };
  written.push({
    file: join(OUT, 'vercel.json'),
    html: JSON.stringify(config, null, 2) + String.fromCharCode(10),
    url: '/vercel.json',
    raw: true,
  });
}

// robots.txt
written.push({
  file: join(OUT, 'robots.txt'),
  html: `User-agent: *\nAllow: /\n\nSitemap: ${CONFIG.origin}/sitemap.xml\n`,
  url: '/robots.txt',
  raw: true,
});

// -------------------------------------------------------- validasi output

for (const w of written) {
  if (w.raw) continue;
  const leftover = w.html.match(/\{\{\{?\s*[\w.:-]+\s*\}?\}\}/g);
  if (leftover) errors.push(`${w.url}: placeholder tidak tergantikan: ${[...new Set(leftover)].join(', ')}`);
  if (/\bundefined\b/.test(w.html)) warnings.push(`${w.url}: mengandung kata "undefined"`);
}

// Semua link internal harus menunjuk halaman yang benar-benar dihasilkan.
{
  const known = new Set(written.map((w) => w.url));
  known.add('/404.html');
  for (const w of written) {
    if (w.raw) continue;
    for (const m of w.html.matchAll(/href="(\/[^"#?]*)"/g)) {
      const href = m[1];
      // Path berekstensi (styles.css, sitemap.xml, aset) diperiksa terhadap disk;
      // sisanya harus salah satu halaman yang benar-benar dihasilkan.
      if (/\.[a-z0-9]+$/i.test(href)) {
        if (!existsSync(join(OUT, href)) && !written.some((x) => x.url === href))
          errors.push(`${w.url}: file statis hilang -> ${href}`);
        continue;
      }
      if (!known.has(href)) errors.push(`${w.url}: link internal mati -> ${href}`);
    }
  }
}

// Aset lokal yang dirujuk harus ada di disk.
{
  for (const w of written) {
    if (w.raw) continue;
    for (const m of w.html.matchAll(/(?:src|href)="(\/assets\/[^"]+)"/g)) {
      if (!existsSync(join(OUT, m[1]))) errors.push(`${w.url}: aset hilang -> ${m[1]}`);
    }
  }
}

if (errors.length) {
  console.error(`\nBUILD GAGAL — ${errors.length} error:\n`);
  for (const e of errors) console.error(`  - ${e}`);
  process.exit(1);
}

if (CHECK_ONLY) {
  console.log(`OK (--check): ${written.length} file akan ditulis, 0 error.`);
  if (warnings.length) for (const w of warnings) console.warn(`  ! ${w}`);
  process.exit(0);
}

// ------------------------------------------------------------------ tulis

// Bersihkan hanya halaman yang kita hasilkan; assets/, styles.css, script.js
// dikelola manual dan tidak disentuh.
for (const dir of ['en', 'about', 'book', 'portfolio']) {
  const target = join(OUT, dir);
  if (existsSync(target)) rmSync(target, { recursive: true, force: true });
}

const BANNER = '<!-- DIHASILKAN OLEH site/build.mjs — jangan sunting file ini. Sunting site/data/ atau site/templates/, lalu jalankan: node site/build.mjs -->\n';

for (const w of written) {
  mkdirSync(dirname(w.file), { recursive: true });
  writeFileSync(w.file, w.raw ? w.html : BANNER + w.html, 'utf8');
}

console.log(`OK: ${written.length} file ditulis ke ${relative(ROOT, OUT)}/`);
if (warnings.length) for (const w of warnings) console.warn(`  ! ${w}`);
