# FINDINGS — Audit Route & Inventaris Copy

Fase 0 (recon) dari megaprompt "Route audit + copy/i18n overhaul".
Tanggal: 2026-08-29. Commit dasar: `278b125`.

Dokumen ini **hanya melaporkan**. Tidak ada file situs yang diubah selama fase ini,
kecuali dua hal operasional yang diminta terpisah oleh user (lihat §6).

Setiap klaim di bawah berasal dari file yang benar-benar dibuka atau dari respons API
yang benar-benar dipanggil. Yang belum terverifikasi ditandai eksplisit.

---

## 0. Jawaban pertanyaan pembuka: bagaimana situs Vercel dan aplikasi CI4 terhubung?

**Jawaban: tidak terhubung sama sekali. Keduanya duplikasi manual yang hidup terpisah
di satu repo.**

Ini menjawab prasyarat yang megaprompt minta diselesaikan sebelum apa pun: aman
mengganti seluruh copy situs statis tanpa menyentuh aplikasi order.

Tiga pengecekan independen, semuanya negatif:

1. **Grep marker situs statis ke seluruh `app/Views`** — `site-shell` 0 hit,
   `Visuals with purpose` 0, `nav-links` 0, `mellogang-monogram` 0, `data-i18n` 0,
   `lang-switch` 0, `PROJECTS=` 0.
2. **Tidak ada generator.** `grep -rIl "frontend/"` atas semua `*.php|*.js|*.json|*.md|*.yml`
   di repo = **nol file**. Tidak ada yang menulis ke atau membaca dari `frontend/`.
3. **`builds` bukan generator situs** — itu skrip stabilitas bawaan CodeIgniter
   (`php builds [release|development]`, menukar `codeigniter4/framework` ↔
   `codeigniter4/codeigniter4` di `composer.json`). Isi `tools/` cuma
   `tools/screenshots/{build_pdf.php,capture.js}` dan
   `tools/social-fetcher/{worker.js,login-ig.js,minimist-mini.js}`. Tidak ada
   `package.json` di root.

Dua sistem i18n yang sama sekali tidak berbagi apa pun:

| | Situs statis (`frontend/`) | Aplikasi CI4 (`app/`) |
|---|---|---|
| Penyimpanan pilihan | `localStorage['mellogang-lang']` | cookie `mllang`, **httponly** |
| Default | **ID** (`script.js:56`) | **EN** (`app/Support/I18n.php:16-19`) |
| Kamus | objek `I18N` inline, 29 key | `loadDictionaries()`, ~146 key |
| Format key | `nav.home`, `hero.title` | `global.*`, `auth.*`, `dashboard.*` |

Cookie CI4 di-set `httponly=true` (`app/Support/I18n.php:57-69`), jadi JS situs statis
tidak akan pernah bisa membacanya walau satu domain. Konsekuensi nyata: pengunjung yang
memilih ID di situs statis lalu masuk ke aplikasi order akan dapat Inggris lagi.
Ini bukan bug yang diminta diperbaiki, tapi perlu diputuskan nanti.

---

## 1. Peta route publik

### 1.1 Yang benar-benar di-deploy

Diverifikasi lewat Vercel API (`GET /v9/projects?limit=100`, token dari `creds.txt`):

| Properti | Nilai |
|---|---|
| Project | `mellogang` (`prj_RBPIjXSigqgDZCCuuLYGGspbWtup`) |
| **Root Directory** | **`frontend`** |
| Repo | `ryuken25/mellogangvisuals`, branch `main` |
| Framework | none (statis murni) |
| Domain | `mellogang.vercel.app`, `mellogangvisual.wyna.dev`, `mellogang.wyna.dev` |

Dari 29 project di akun Vercel, **hanya satu** yang terhubung ke repo ini. Artinya:

> **Pohon statis di root repo (`index.html`, `styles.css`, `script.js`, `about/`,
> `contact/`, `packages/`, `portfolio/`, `workflow/`, `assets/`) tidak di-deploy ke mana
> pun.** Itu duplikat mati, 12 halaman HTML, hasil commit `edf0eec`.

**Live tertinggal 2 commit dari repo.** Deploy produksi terakhir 2026-08-13 06:48 =
commit `1121ab4`. Commit `8c6b489` dan `278b125` (07:43 & 07:48) belum pernah ter-deploy.
Jadi sebagian temuan "dari situs live" di megaprompt sudah tidak berlaku untuk repo.

### 1.2 `frontend/` — 23 halaman

`cleanUrls:true` + `trailingSlash:true` → `frontend/about/index.html` disajikan di `/about/`.

| URL | File | `<title>` | meta desc | Gen |
|---|---|---|---|---|
| `/` | `frontend/index.html` | MellogangVisuals — Photo & Video Production | ya | A |
| `/about/` | `frontend/about/index.html` | About Us — MellogangVisuals | ya | A |
| `/book/` | `frontend/book/index.html` | Book Now — MellogangVisuals | ya | A |
| `/contact/` | `frontend/contact/index.html` | About Us — MellogangVisuals | **tidak** | stub |
| `/portfolio/` | `frontend/portfolio/index.html` | Portofolio — MellogangVisuals | ya | A |
| `/portfolio/blooms-promo/` | `.../blooms-promo/index.html` | Blooms Garden Promo — MellogangVisuals | ya | A |
| `/portfolio/blooms-short/` | `.../blooms-short/index.html` | The Blooms Garden Bali — MellogangVisuals | ya | A |
| `/portfolio/bukit-lestari/` | `.../bukit-lestari/index.html` | Puncak Bukit Lestari — MellogangVisuals | ya | A |
| `/portfolio/coinvest-asia/` | `.../coinvest-asia/index.html` | CoinFest Asia 2022 — MellogangVisuals | ya | A |
| `/portfolio/cultural-event/` | `.../cultural-event/index.html` | Cultural Event — MellogangVisuals | ya | A |
| `/portfolio/eka-nanda/` | `.../eka-nanda/index.html` | Eka & Nanda — MellogangVisuals | ya | A |
| `/portfolio/indra-suci/` | `.../indra-suci/index.html` | Indra & Suci — MellogangVisuals | ya | A |
| `/portfolio/mandiri-taspen/` | `.../mandiri-taspen/index.html` | Mandiri Taspen — MellogangVisuals | ya | A |
| `/portfolio/pohen-camp/` | `.../pohen-camp/index.html` | Pohen Hill Camp — MellogangVisuals | ya | A |
| `/portfolio/wedding-ceremony/` | `.../wedding-ceremony/index.html` | Ceremony Stories — MellogangVisuals | ya | A |
| `/packages/` | `frontend/packages/index.html` | Packages — mellogang.wyna.dev | ya | **B** |
| `/packages/ceremony/` | … | Ceremony Packages — mellogang.wyna.dev | ya | **B** |
| `/packages/company/` | … | Company Packages — mellogang.wyna.dev | ya | **B** |
| `/packages/event/` | … | Event Packages — mellogang.wyna.dev | ya | **B** |
| `/packages/graduation/` | … | Graduation Packages — mellogang.wyna.dev | ya | **B** |
| `/packages/prewedding/` | … | Prewedding Packages — mellogang.wyna.dev | ya | **B** |
| `/packages/wedding/` | … | Wedding Packages — mellogang.wyna.dev | ya | **B** |
| `/workflow/` | `frontend/workflow/index.html` | Workflow — mellogang.wyna.dev | ya | **B** |

### 1.3 Temuan terbesar: `frontend/` adalah DUA generasi desain yang ditempel

Riwayat git membuktikan pemisahannya:

- `ed1109f fix: isolate static frontend deployment root` — menyalin seluruh pohon statis
  root ke `frontend/`.
- `82d4170 feat: rebuild studio portfolio and booking experience` — membangun ulang
  `frontend/index.html`, `about/`, `book/`, `portfolio/*`, `script.js`, `styles.css`.
  **`frontend/packages/*` dan `frontend/workflow/` tidak ada di daftar file commit itu.**

| | Generasi A (14 halaman, hidup) | Generasi B (8 halaman, basi) |
|---|---|---|
| Halaman | `/`, `/about/`, `/book/`, `/portfolio/` + 10 detail | `/packages/` + 6 sub, `/workflow/` |
| Item nav | Home · Portofolio · About Us · Book Now | Packages · Workflow · About · Portfolio · Contact |
| Lang switch | ada | **tidak ada** |
| Theme toggle | ada | **tidak ada** |
| Class tombol | `btn btn-primary btn-small` | `button button-primary button-small` |
| Brand di `<title>` | MellogangVisuals | mellogang.wyna.dev |
| Jumlah `data-i18n` | 9–36 per halaman | 4–5 (cuma fragmen nav) |

**Generasi B tampil rusak di produksi.** Class yang dipakai markup-nya tapi **tidak punya
satu pun aturan** di `frontend/styles.css` (diverifikasi per file; class yang sama memang
ada di `styles.css` root yang mati):

- `frontend/packages/index.html` — `.button`, `.button-primary`, `.button-small`,
  `.category-tabs`, `.footer-inner`, `.package-body`, `.package-card`, `.package-category`,
  `.package-grid`, `.package-meta`, `.text-link`, `.wa-float`
- `frontend/packages/wedding/index.html` (mewakili 6 halaman detail) — `.button`,
  `.button-primary`, `.button-secondary`, `.button-small`, `.detail-card`, `.detail-grid`,
  `.footer-inner`, `.wa-float`
- `frontend/workflow/index.html` — `.button`, `.button-primary`, `.button-small`,
  `.cta-band`, `.footer-inner`, `.step`, `.step-number`, `.steps`, `.wa-float`

Cek silang: `.button-primary` → 1 aturan di `styles.css` root, **0** di
`frontend/styles.css`. Sama untuk `.package-grid`, `.package-card`, `.category-tabs`,
`.wa-float`, `.footer-inner`, `.text-link`.

**Generasi B juga pulau terputus.** Graf link masuk atas `frontend/`:

| Target | Link masuk dari |
|---|---|
| `/packages/` | hanya `/packages/*` dan `/workflow/` |
| `/workflow/` | hanya `/packages/*` dan `/workflow/` |
| `/contact/` | hanya `/packages/*` dan `/workflow/` |

Nol link dari `/`, `/about/`, `/book/`, `/portfolio/`, atau halaman detail mana pun.

**CTA-nya mati.** Header Generasi B memakai
`<a class="button button-primary button-small" href="/#booking">Book a date</a>`.
`grep -c 'id="booking"' frontend/index.html` → **0**. Anchor itu tidak ada. Mati di 8 halaman.

### 1.4 `/contact/` bukan halaman, tapi stub redirect client-side

`frontend/contact/index.html`, 406 byte, isinya `meta http-equiv="refresh"` +
`location.replace('/about/')`. Bukan HTTP 301/308. 8 halaman masih menautinya (semuanya
di pulau Generasi B). Lebih tepat jadi entri `redirects` di `vercel.json`.

### 1.5 Route publik CI4 (konteks, di luar scope)

`app/Config/Routes.php`, `setAutoRoute(false)`:

| Method | Route | Controller |
|---|---|---|
| GET | `/` | `Public\HomeController::index` |
| GET | `/katalog` | `Public\KatalogController::index` |
| GET | `/portofolio` | `Public\PortofolioController::index` |
| GET | `/kontak` | `Public\KontakController::index` |
| GET | `/showcase` | `Public\ShowcaseController::index` |
| GET | `/status-pesanan` | `Public\StatusController::index` |
| GET | `/invoice/(:segment)` | `Public\InvoiceController::show` |
| GET | `/lang/(:segment)` | `BaseController::setLanguage` |
| GET/POST | `/login`, `/register`, `/logout`, `/auth/*` | `AuthController` |
| GET/POST | `/profile*` | `ProfileController` |

Perhatikan tabrakan penamaan: statis `/portfolio/` (Inggris) vs CI4 `/portofolio`
(Indonesia). URL beda, sumber data beda (objek `PROJECTS` statis vs tabel `social_post`).
Kalau nanti keduanya disatukan di satu domain, ini harus diputuskan.

### 1.6 Integritas link `frontend/`

536 referensi diekstrak dari 23 file (`href`, `src`, `srcset`, `poster`, `action`,
`data-src`, `og:*`). Hasil: **OK 461 · EXTERNAL 42 · SKIP 4 · MISSING 29**.

| Target hilang | Jenis | Ref | Perujuk |
|---|---|---|---|
| `/assets/vendor/gsap.min.js` | aset | **14** | `/`, `/about/`, `/book/`, `/portfolio/`, 10 halaman detail |
| `/assets/vendor/ScrollTrigger.min.js` | aset | **14** | 14 halaman yang sama |

`frontend/assets/vendor/` tidak ada. `find . -iname '*gsap*'` = nol hasil di seluruh repo.
**28 404 keras per crawl penuh.** `gsapMotion()` (`frontend/script.js:17`) punya guard
`!window.gsap` jadi tidak ada exception — animasi scroll-nya sekadar mati diam-diam.
(1 "MISSING" sisanya false positive dari ekstraktor pada `contact/index.html`.)

Yang **bersih** dan harus dipertahankan:

- Link tanpa trailing slash: **0**
- Link relatif (bukan root-absolute): **0**
- Gambar / CSS / JS hilang selain GSAP: **0**
- `<img>` tanpa `alt`: **0** di 23 halaman

> Ini membatalkan bug #6 di megaprompt. Trailing slash sudah kanonik dan konsisten;
> tidak ada hop redirect yang dibayar di mana pun.

Host eksternal: `wa.me` 20 ref (nomor `6282236004917`), `www.youtube.com` 12,
`www.instagram.com` 6, `mailto:` 4. Embed YouTube runtime memakai
`www.youtube-nocookie.com` (dibangun di `detail()`).

### 1.7 Halaman detail portofolio

10 slug direktori cocok persis dengan 10 key objek `PROJECTS` (`frontend/script.js:1-12`).
Tidak ada slug yatim, tidak ada entri data yatim. Semua 10 ditaut dari `/portfolio/`;
5 di antaranya juga tampil di `/`.

| Slug | `<title>` unik | Link kembali | Prev/Next |
|---|---|---|---|
| 10 slug, semuanya | ya | ya (footer + nav) | **tidak ada, semua** |

**Bug: halaman menimpa dirinya sendiri.** `detail()` (`frontend/script.js:18`) menulis
ulang `<h1>`, kategori, tahun, deskripsi, dan `document.title` dari `PROJECTS` setelah JS
jalan. Di 4 halaman isinya **berbeda** dari yang di-serve:

| Slug | `<title>`/`<h1>` yang di-serve | `PROJECTS[...].title` runtime |
|---|---|---|
| `blooms-promo` | Blooms Garden Promo | The Blooms Garden — Promo |
| `blooms-short` | The Blooms Garden Bali | The Blooms Garden — Best Scenes |
| `mandiri-taspen` | Mandiri Taspen | PT Bank Mandiri Taspen |
| `wedding-ceremony` | Ceremony Stories | Wedding Ceremony |

Kategori juga melenceng: `bukit-lestari` di-serve `Commercial / Destination`, JS menulis
`Destination / Villa`; `blooms-short` di-serve `Destination / Commercial`, JS menulis
`Destination / Teaser`. Crawler dan pengguna tanpa JS melihat satu versi, pengguna dengan
JS melihat versi lain, dan teksnya berubah di depan mata.

`PROJECTS` adalah sumber kebenaran saat runtime, jadi HTML-nya semestinya **di-generate
dari situ**, bukan dipelihara paralel dengan tangan.

### 1.8 Slug tidak seragam

| Pola | Slug |
|---|---|
| Nama klien / pasangan | `eka-nanda`, `indra-suci`, `mandiri-taspen`, `coinvest-asia` |
| Nama tempat / venue | `bukit-lestari`, `pohen-camp`, `blooms-promo`, `blooms-short` |
| Tipe proyek generik | `cultural-event`, `wedding-ceremony` |

Tiga ketidakkonsistenan:

1. **Sumbu campur.** `wedding-ceremony` dan `cultural-event` itu *kategori*, bukan proyek —
   keduanya entri sumber Instagram dengan `video:''` dan satu gambar galeri. Mereka terbaca
   sebagai halaman taksonomi yang nyasar ke namespace proyek.
2. **Satu klien dipecah per deliverable.** `blooms-promo` dan `blooms-short` klien yang sama
   (The Blooms Garden) dengan format ditempel di belakang, sementara proyek lain tidak
   pernah menyebut format.
3. **Typo masuk URL.** Slug-nya `coinvest-asia`, tapi event dan seluruh copy menyebut
   **CoinFest** Asia 2022. Typo-nya sudah menyebar ke 3 nama file aset
   (`frontend/assets/video/frames/coinvest-asia-{20,50,80}.jpg`) dan key `PROJECTS`.

**Usul satu aturan:** `<subjek>-<tahun>` — `<subjek>` adalah nama klien, pasangan, atau
venue dalam kebab-case (tidak pernah tipe deliverable, tidak pernah kategori), `<tahun>`
adalah 4 digit yang sudah ada di `PROJECTS[...].year`. Kalau satu subjek punya dua
deliverable di tahun yang sama, dan hanya saat itu, tambahkan token format:
`<subjek>-<tahun>-<format>`.

| Sekarang | Usul | Catatan |
|---|---|---|
| `indra-suci` | `indra-suci-2024` | |
| `eka-nanda` | `eka-nanda-2024` | |
| `bukit-lestari` | `puncak-bukit-lestari-2023` | samakan dengan judul tampilan |
| `pohen-camp` | `pohen-hill-camp-2023` | |
| `blooms-short` | `blooms-garden-2023-teaser` | subjek+tahun sama → token format sah |
| `blooms-promo` | `blooms-garden-2023-promo` | |
| `mandiri-taspen` | `bank-mandiri-taspen-2023` | |
| `coinvest-asia` | `coinfest-asia-2022` | **memperbaiki typo** |
| `wedding-ceremony` | butuh subjek nyata | sekarang kategori, bukan proyek |
| `cultural-event` | butuh subjek nyata | sekarang kategori, bukan proyek |

Sufiks tahun bikin aturannya mekanis: menghapus pertimbangan "ini klien atau venue?" saat
tabrakan, sekaligus memberi tanggal pada arsip yang akan terus tumbuh, dan membuat urutan
prev/next bisa diturunkan dari slug. **Rename apa pun wajib disertai `redirects` di
`vercel.json`** — slug lama sudah bisa terindeks.

---

## 2. Mekanisme i18n saat ini

### 2.1 Bahasa

- **Kamus: objek JS inline** `const I18N={id:{...},en:{...}}` di `frontend/script.js:20-55`.
  Bukan JSON, bukan file `app/Language/`, bukan dua HTML terpisah. **29 key per bahasa.**
- **Penerapan** (`frontend/script.js:56`, `function lang()`):
  - `let cur = localStorage.getItem('mellogang-lang') || 'id'`
  - `apply = l => { document.documentElement.lang = l;
    $$('[data-i18n]').forEach(el => { const k = el.dataset.i18n;
    if (I18N[l] && I18N[l][k] != null) el.innerHTML = I18N[l][k] });
    $$('.lang-btn').forEach(b => b.classList.toggle('active', b.dataset.lang === l)) }`
  - Klik `.lang-btn` → set `cur`, tulis localStorage, `apply(cur)`.
- Dipanggil pada `DOMContentLoaded` (`frontend/script.js:57`), lewat
  `<script src="/script.js" defer>` di akhir body.
- Key yang tidak ada di kamus **dilewati** (guard `!= null`) → teks markup (Inggris) tetap
  tampil. Ini alasan situs terlihat "setengah tertranslate", bukan menampilkan key mentah.

### 2.2 Jawaban tegas

| Pertanyaan | Jawaban | Bukti |
|---|---|---|
| Persisten antar halaman? | **Ya**, localStorage same-origin — tapi hanya di halaman yang memuat `script.js` | `script.js:56` |
| Punya URL sendiri? | **Tidak.** Tidak ada `?lang=`, `/id/`, `/en/` | grep `URLSearchParams\|location.search\|hreflang` = 0 |
| `<html lang>` berubah? | **Ya saat toggle** — tapi 23/23 file di-hardcode `lang="id"` padahal isi markup-nya Inggris | `script.js:56`; 23 file |
| Flash bahasa saat first paint? | **Ya, di SEMUA halaman.** Nol anti-FOUC bahasa (grep `mellogang-lang` di HTML = 0). Markup fallback Inggris, default ID → user default melihat Inggris dulu lalu flip ke ID | `frontend/index.html:1` + `script.js:56` |
| Flash tema? | **Ada di 9 halaman**; 14 halaman punya guard inline | §2.3 |
| Default ID atau EN? | **ID**, dari kode | `script.js:56` `localStorage.getItem(store)\|\|'id'` |

### 2.3 Anti-FOUC tema per halaman

Guard: `<script>try{const t=localStorage.getItem('mellogang-theme');if(t)document.documentElement.dataset.theme=t}catch(e){}</script>`

- **Punya (14):** `/`, `/about/`, `/book/`, `/portfolio/`, dan 10 `/portfolio/<slug>/`
- **Tidak punya (9, flash tema):** `/contact/`, `/workflow/`, `/packages/` + 6 sub-paket

`meta theme-color` juga tidak konsisten: 12× `#f6f0e7`, 2× `#f6efe3`, 8× `#0a0e0d`
(halaman paket & workflow memakai warna gelap padahal tema default terang).

Tema sendiri: `frontend/script.js:14` baca `localStorage['mellogang-theme']`, set
`document.documentElement.dataset.theme`. Token di `frontend/styles.css:2`
(`:root{--bg:#f6efe3;...}`, terang = default) dan `styles.css:3` (`html[data-theme=dark]`).
**Tidak ada `prefers-color-scheme` sama sekali** (grep = 0) → preferensi OS diabaikan,
pengunjung baru selalu dapat terang.

### 2.4 Inventaris key i18n

Kamus 29 key ID / 29 key EN. HTML memakai 28 key unik.

- **(a) Dipakai di HTML tapi tidak ada di kamus: TIDAK ADA.** Bersih — tidak ada key yang
  bisa bocor ke layar.
- **(b) Ada di kamus tapi tidak dipakai (dead): `btn.book`** — yang dipakai hanya
  `btn.book.small` (`script.js:23` / `script.js:40`).
- **(c) Paritas EN/ID: UTUH.** 29 = 29.

**Tapi paritas utuh itu menyesatkan.** Empat key nilainya identik di kedua bahasa, jadi
efektif tidak diterjemahkan — dan dua di antaranya salah eja di sisi Inggris:

| Key | ID | EN | Masalah |
|---|---|---|---|
| `nav.portfolio` | Portofolio | **Portofolio** | ejaan ID di kamus EN (`script.js:39`) |
| `works.eyebrow` | Portofolio | **Portofolio** | sama (`script.js:45`) |
| `hero.cta2` | Lihat portofolio | **View portofolio** | ejaan ID di kamus EN (`script.js:43`) |
| `hero.meta1` | Bali, Indonesia | Bali, Indonesia | wajar |

> Ini akar bug #3 megaprompt. Label nav yang bocor bahasa **bukan** sekadar teks markup
> yang salah — salah ejanya ada **di dalam kamus EN itu sendiri**, jadi tidak bisa
> diperbaiki dengan menyunting HTML saja.

Dua key berisi HTML mentah dan di-inject lewat `innerHTML`, bukan `textContent`:
`hero.title` dan `about.title` (`script.js:24,31,41,48`) mengandung `<br>` dan
`<span class="gradient">`. Ini membatasi bagaimana copy baru boleh ditulis.

### 2.5 Cakupan i18n per halaman — inilah masalah sebenarnya

Jumlah atribut `data-i18n` per halaman:

```
/                       36   lang-switch: ya          memuat script.js: ya
/portfolio/             11   ya                       ya
/about/                 11   ya                       ya
/book/                  11   ya                       ya
/portfolio/<slug>/ ×10   9   ya                       ya
/packages/               5   TIDAK ADA                ya
/workflow/               5   TIDAK ADA                ya
/packages/<x>/ ×6        4   TIDAK ADA                ya
/contact/                1   TIDAK ADA                TIDAK MEMUAT script.js
```

**Halaman non-home praktis hanya menerjemahkan link navigasi.** Semua isi halaman —
heading, paragraf, label form, opsi dropdown, caption — Inggris keras dan tidak akan
pernah berubah walau tombol ID ditekan.

Halaman Generasi B lebih buruk: **tetap memuat `script.js`** tapi tidak punya lang switch.
Jadi 4–5 link nav-nya ikut berubah ke Indonesia sementara seluruh isi halaman tetap
Inggris → halaman campur bahasa **tanpa cara apa pun untuk mengubahnya**. Di
`frontend/packages/index.html` teks link-nya `About`, lalu kamus menimpanya jadi
`About Us` / `Tentang Kami`.

`<title>` dan `<meta name="description">` **tidak punya mekanisme i18n sama sekali**
(0 `<title data-i18n>`); `lang()` hanya menyentuh elemen `[data-i18n]`.

### 2.6 Form booking menghasilkan pesan campur bahasa

`booking()` (`frontend/script.js:19`) menyusun pesan WhatsApp yang **selalu Indonesia**:

```
Hi MellogangVisuals, saya ${nama}. Saya ingin menanyakan ketersediaan untuk ${label}
pada tanggal ${d}. Lokasi: ${lokasi}. Catatan: ${catatan} Mohon info detail dan
langkah selanjutnya ya.
```

Placeholder fallback juga Indonesia (`[nama]`, `[tanggal acara]`, `[lokasi]`), tanggal
diformat `Intl.DateTimeFormat('id-ID')`. Tapi `label` diambil dari
`select.options[selectedIndex].text` — dan seluruh `<option>` di
`frontend/book/index.html` **berbahasa Inggris** tanpa `data-i18n`. Hasilnya kalimat
Indonesia dengan potongan Inggris di tengah, apa pun bahasa yang dipilih pengunjung.

### 2.7 Aksesibilitas kontrol bahasa & tema

Markup (identik di 14 halaman):

```html
<div class="lang-switch" role="group" aria-label="Bahasa">
  <button class="lang-btn active" data-lang="id" type="button">ID</button>
  <button class="lang-btn" data-lang="en" type="button">EN</button>
</div>
<button class="theme-toggle" id="themeToggle" type="button" aria-label="Switch theme">☾</button>
```

- **Keyboard: jalan.** Keduanya `<button type="button">` native, handler `click` terpicu
  Enter/Space.
- **`aria-pressed` / `aria-current`: NOL di seluruh situs.** grep `aria-pressed` = 0;
  `aria-current` hanya 22× `="page"` untuk link nav. State aktif bahasa cuma class
  `.active` → **tidak terekspos ke screen reader sama sekali.** Pengguna SR tidak tahu ID
  atau EN yang sedang aktif.
- `aria-label="Bahasa"` **di-hardcode Indonesia** di 14 halaman, tidak pernah ikut toggle.
- `#themeToggle`: `aria-label` di-update JS jadi `"Switch to light theme"` /
  `"Switch to dark theme"` — **selalu Inggris**, tanpa `aria-pressed`.
- `#mobileThemeToggle` **hanya ada di `/index.html`**, tanpa `aria-label` di markup.
- **Focus ring: tidak ada satu pun aturan `:focus-visible`.**
  `grep focus-visible frontend/styles.css` = **0**. Yang ada hanya `.skip:focus`
  (`styles.css:5`) dan `.field input/select/textarea:focus` (`styles.css:11`). Untuk
  `.lang-btn`, `.theme-toggle`, `.menu-button`, `.btn`, `.nav-links a` cuma ada `:hover`.
  Outline UA tidak dihapus jadi ring bawaan masih muncul, tapi tidak dikustom dan
  kontrasnya berisiko di atas `--surface` krem.
- `.skip` (skip link) hanya di 4 halaman: `/`, `/about/`, `/book/`, `/portfolio/`.
  19 halaman lain tanpa skip link.
---

## 3. Inventaris string

Metode: teks node dari 23 file HTML (tag distrip, inline script dibuang, trim, dedupe), ditambah
atribut `alt` / `aria-label` / `placeholder` / `<title>` / `meta description` / `og:*`, ditambah
string runtime di `frontend/script.js`.

Tabel baris-per-baris lengkap ada di lampiran `FINDINGS-strings.md`. Bagian ini merangkum
angka dan temuan yang menentukan bentuk pekerjaan Fase 2.

### 3.1 Hitungan

| Kategori | Unik |
|---|---|
| Teks node terlihat (body, heading, label, tombol, `<title>`) | 292 |
| `alt` unik yang belum tercakup teks node | 21 |
| `meta[name=description]` unik | 22 |
| `og:description` | 1 |
| `aria-label` + `placeholder` unik | 7 |
| String runtime `script.js` (label tema, label sumber, pesan video kosong, template WhatsApp + 3 placeholder, 6 deskripsi `PROJECTS`, 4 judul `PROJECTS`, 2 kategori `PROJECTS`, template alt galeri) | 26 |
| **Subtotal** | **369** |
| dikurangi 2 blob sampah (`түз`, blob `/portfolio/`) | −2 |
| **Total string terlihat user** | **367** |
| dikurangi 3 glyph murni (`↗`, `☰`, `☾`) | −3 |
| **String copy sesungguhnya** | **364** |

### 3.2 Cakupan bahasa — inilah ukuran pekerjaannya

| | Jumlah | % |
|---|---|---|
| Punya padanan ID (key `data-i18n` benar-benar terpasang) | **28** | **7,6%** |
| **Belum punya padanan ID** | **339** | **92,4%** |

Sebaran per halaman:

| Halaman | String terlihat | Punya ID | Belum |
|---|---|---|---|
| `/` | ~60 | 24 | ~36 |
| `/about/` | ~30 | 4 (nav saja) | ~26 |
| `/book/` | ~35 | 4 (nav saja) | ~31 |
| `/portfolio/` | ~40 | 4 (nav saja) | ~36 |
| `/portfolio/<slug>/` ×10 | ~13 masing-masing | 4 + 1 (salah pakai) | ~9 masing-masing |
| `/packages/` + 6 sub | ~30 / ~14 | 2 (dan justru **merusak** label nav) | sisanya |
| `/workflow/` | ~30 | 2 (idem) | ~28 |
| `/contact/` | 1 | 1 | 0 |

> **Hanya beranda yang benar-benar dwibahasa.** Semua halaman lain cuma menerjemahkan 4 item
> navigasi; seluruh badan teksnya tetap Inggris apa pun bahasa yang dipilih pengunjung.

Ini mengubah sifat Fase 2. Megaprompt meminta "satu sumber kebenaran untuk semua string, tidak
ada teks ditulis langsung di markup halaman". Dengan cakupan 7,6%, itu bukan pekerjaan menyunting
kamus — itu **membangun sistem string dari nol untuk 339 string**, di 23 file yang tidak punya
build step (§4.7).

### 3.3 Grep "string wajib mati" — 19 dari 19 ada di repo

| Status | String | Lokasi |
|---|---|---|
| ADA¹ | `Visuals with purpose.` | `frontend/index.html`, `script.js:41` |
| ADA | `Stories, carefully made.` | `frontend/index.html` |
| ADA | `Clear work. Good people.` | `frontend/index.html`, `script.js:48` |
| ADA | `A focused team behind the frame.` | `frontend/about/index.html` |
| ADA | `Professional from brief to delivery.` | `frontend/about/index.html` |
| ADA | `Clear process. Consistent work.` | `frontend/about/index.html` |
| ADA | `Start with the details.` | `frontend/book/index.html` |
| ADA | `A clear brief makes a better production.` | `frontend/book/index.html` |
| ADA | `Built around your project.` | `frontend/book/index.html` |
| ADA | `Let's talk dates.` | `frontend/index.html`, `script.js:51` |
| ADA | `…an honest visual point of view` | `frontend/index.html`, `script.js:42` |
| ADA | `…images that remain useful after the launch…` | `frontend/about/index.html` |
| ADA | `…calm communication and attention to the details…` | `frontend/about/index.html` |
| ADA | `…a clean finish that serves the brief` | `frontend/about/index.html` |
| ADA | `Your information is not submitted to a database from this static form.` | `frontend/book/index.html` |

¹ `Visuals with purpose.` tidak ketemu sebagai satu string utuh karena **terpecah markup**:
`Visuals<br><span class="gradient">with purpose.</span>`. Ini penting untuk verifikasi akhir —
grep polos atas daftar itu akan memberi false negative. Pengecekan harus dilakukan atas teks
yang sudah distrip tag.

Perhatikan juga: **9 dari 15 string itu ada di dua tempat** — markup HTML *dan* kamus
`script.js`. Menghapus dari satu sisi saja tidak cukup.

### 3.4 Pola AI-writing — bukan cuma frasanya

**(a) Headline dua frasa dipisah titik — 16 kemunculan.**

| File | Selector | Teks |
|---|---|---|
| `index.html` | `h1.display-title` | Visuals **.** with purpose. |
| `index.html` | `.about-strip h2` | **Clear work. Good people.** |
| `index.html` | `.hero-caption strong` | Stories, carefully made. |
| `about/index.html` | `h1.display` | A focused team **.** behind the frame. |
| `about/index.html` | `.section-dark h2` | **Clear process. Consistent work.** |
| `book/index.html` | `h1.display` | Start with **.** the details. |
| `portfolio/index.html` | `h1.display` | Selected **.** projects. |
| `workflow/index.html` | `h1` | **Simple on purpose. Serious about the work.** |
| `workflow/index.html` | `footer p` | **Simple booking. Thoughtful production.** |
| `packages/index.html` | `h1` | **Choose the story. We'll shape the rest.** |
| `packages/wedding/` | `h1` | The day, **.** honestly. |
| `packages/event/` | `h1` | Keep the room **.** alive. |
| `packages/prewedding/` | `h1` | A day before **.** the day. |
| `packages/company/` | `h1` | Make the work **.** visible. |
| `packages/ceremony/` | `h1` | Hold on to **.** the feeling. |
| `packages/graduation/` | `h1` | The next chapter **.** starts here. |

Plus kamus ID: `script.js:24` (`hero.title` = "Visual`<br>`dengan tujuan.") dan `script.js:31`
(`about.title` = "Kerja rapi.`<br>`Tim yang enak diajak kerja.").

> **14 dari 14 halaman utama memakai bentuk yang sama:** frasa pendek + `<br>` + frasa bergradien.
> Tidak ada satu pun headline berupa kalimat utuh atau pernyataan konkret. Ini bukan beberapa
> headline yang kebetulan buruk — ini template yang diterapkan ke seluruh situs, dan versi ID-nya
> menyalin template yang sama. Menggantinya berarti mengganti pola, bukan menambal kalimat.

**(b) Kata sifat kosong — 8 kemunculan dari 6 kata:**
`thoughtful` (packages/wedding, workflow), `considered` (index, packages/company, script.js:42),
`honest` (index, script.js:42), `honestly` (packages/wedding `<h1>`), `meaningful`
(og:description). Tidak ditemukan: purposeful, intentional, crafted, curated, seamless, timeless,
authentic, elevated.

Di luar daftar megaprompt tapi pola identik: `calm` (2×), `clean` (2×), `practical` (2× — frasa
"the next practical step" muncul nyaris identik di `/` dan `/about/`), `simple` (4×).

**(c) Kata kerja brosur: nol kemunculan.** Tidak ada elevate, capture moments, bring your vision
to life, tell your story, transform. Ini satu-satunya kategori yang bersih.

**(d) Daftar tiga/empat berulang — 11 kemunculan, bukan 4.**

| × | Frasa | Lokasi |
|---|---|---|
| 6 | `weddings, events, brands and destinations` | `about/`, `book/`, `index.html` (2×), `portfolio/`, `script.js:52` |
| 2 | `weddings, brands, events and destinations` | `index.html`, `script.js:42` |
| 2 | `prewedding, destination, corporate and event` | `index.html`, `script.js:46` |
| 1 | `weddings, events, destinations, corporate projects and brand content` | `about/` |

Perhatikan dua varian pertama: daftar yang sama dengan **urutan ditukar**, seolah divariasikan
supaya tidak terlihat diulang. Itu justru penanda paling jelas.

**(e) Em-dash sisipan dan "not X, but Y":**
`packages/event/` → "celebrations—coverage that catches what the schedule cannot";
`packages/prewedding/` → "built around the two of you—not a checklist of poses".
(8 em-dash lain hanyalah pemisah brand di `<title>`, bukan sisipan retoris.)

**(f) Kata "details" dipakai 12× di seluruh situs** dalam frasa berbeda-beda —
"Start with the details", "Project details", "the details you provide", "attention to the details
that matter", "The details, rituals…", "Detail and family moments", "We stay alert to the
details", "the small details", "Clear the details", "people, details and atmosphere" (2×).
Kata itu sudah kehilangan arti.

**(g) Tiga tagline brand berbeda untuk entitas yang sama:**
"Photo and video production for weddings, events, brands and destinations." (Generasi A) ·
"Photo and film for days that deserve to be remembered properly." (Generasi B) ·
"Simple booking. Thoughtful production." (footer `/workflow/`).

### 3.5 Audit alt text

`<img>` tanpa `alt`: **0** — bagus. Tapi isinya banyak yang tidak mendeskripsikan apa pun:

| Masalah | Contoh | Jumlah |
|---|---|---|
| Cuma nama brand | `alt="MellogangVisuals"` (logo/monogram di header) | 22 |
| Nama brand + kategori, bukan isi foto | `alt="Ceremony by Mellogang Visuals"`, `alt="Wedding by Mellogang Visuals"`, `alt="Event by Mellogang Visuals"`, `alt="Graduation by Mellogang Visuals"`, `alt="Company work by Mellogang Visuals"`, `alt="Prewedding by Mellogang Visuals"` | 6 |
| Nama brand + lokasi generik | `alt="MellogangVisuals — Bali"`, `alt="MellogangVisuals production"`, `alt="MellogangVisuals cultural event location"` | 3 |
| Deskriptif, layak dipertahankan | `alt="Indra & Suci prewedding"`, `alt="Puncak Bukit Lestari promotional film"`, `alt="Wedding ceremony portrait"`, `alt="PT Bank Mandiri Taspen project"` | 8 |

Alt galeri di halaman detail **dibuat runtime** oleh `detail()` dari template, menimpa alt HTML —
jadi memperbaiki alt di markup saja tidak cukup untuk halaman-halaman itu.

Aturan yang perlu ditegakkan di Fase 2: alt mendeskripsikan **isi foto**, bukan nama studio.
Logo boleh `alt="MellogangVisuals"` karena itu memang isinya; foto karya tidak boleh.

### 3.6 Bug copy/i18n yang menghalangi penulisan ulang

Tiga hal ini harus diperbaiki lebih dulu, kalau tidak copy baru akan langsung rusak lagi.

**1. Kamus EN memakai ejaan Indonesia.**
`script.js:39` `en['nav.portfolio']='Portofolio'`, `script.js:43` `en['hero.cta2']='View portofolio'`,
`script.js:45` `en['works.eyebrow']='Portofolio'`. Memilih EN tidak pernah menghasilkan
"Portfolio". Bahkan di 8 halaman Generasi B yang markup-nya sudah benar (`Portfolio`), JS
**merusaknya** jadi `Portofolio` saat load.

**2. Link "Back to portofolio →" hancur saat load di 10 halaman detail — terverifikasi.**

Markup: `<a href="/portfolio/" data-i18n="nav.portfolio">Back to portofolio →</a>`
`lang()` menjalankan `el.innerHTML = I18N[l]['nav.portfolio']` → isinya jadi **`Portofolio`** saja.
Kata "Back to" dan panah `→` hilang. Terjadi di **kedua bahasa** (nilai kamusnya identik).

Diverifikasi: 10 dari 10 halaman detail memakai markup ini.

Dampaknya berlipat karena 10 halaman itu **tidak punya blok footer navigasi sama sekali** (§4.5) —
link ini satu-satunya navigasi di footer mereka, dan ia berubah jadi satu kata ambigu. Akar
masalahnya: satu key i18n dipakai untuk dua peran berbeda (label nav dan teks link kembali).

**3. Nav Generasi B dirusak oleh kamus Generasi A.**
Di `/packages/*` dan `/workflow/`: `<a data-i18n="nav.about">About</a>` → jadi "About Us" /
"Tentang Kami", dan `<a data-i18n="nav.portfolio">Portfolio</a>` → jadi "Portofolio". Halaman ini
tidak punya switcher, jadi label berubah tanpa kendali pengunjung, dan `About` melebar jadi
`About Us` → pergeseran layout saat load.

Hasil di mode ID (default): nav berbunyi
**`Packages / Workflow / Tentang Kami / Portofolio / Contact`** — tiga kata Inggris bercampur dua
kata Indonesia dalam satu baris, karena `nav.packages`, `nav.workflow`, dan `nav.contact` tidak
ada di kamus mana pun.
---

## 4. Status SEO teknis

### 4.1 File infrastruktur

| Artefak | Status | Bukti |
|---|---|---|
| `frontend/sitemap.xml` | **TIDAK ADA** | `ls` → No such file |
| `frontend/robots.txt` | **TIDAK ADA** | `ls` → No such file |
| `frontend/404.html` | **TIDAK ADA** | `ls` → No such file |
| Konfigurasi 404 di `vercel.json` | **TIDAK ADA** | tidak ada `routes`/`rewrites`/`redirects`/`errorPage` |
| `robots.txt` lain di repo | ada 1, bukan milik frontend | `public/robots.txt` = docroot CodeIgniter, bukan tree yang di-deploy |

Konsekuensi: setiap URL salah menghasilkan halaman 404 default Vercel — tanpa header
situs, tanpa nav, tanpa jalan balik, tanpa merek.

### 4.2 Matriks metadata per halaman

Rekap dari 23 halaman (detail per halaman ada di lampiran recon):

| Tag | Cakupan |
|---|---|
| `<title>` | 23/23 — tapi **1 pasang duplikat** (`/about/` dan `/contact/` sama-sama "About Us — MellogangVisuals") |
| `meta description` | 22/23 (hanya `/contact/` tidak punya) — semuanya **unik**, tidak ada duplikat |
| `<link rel=canonical>` | **1/23** — hanya `/contact/`, dan itu pun relatif (`/about/`), bukan absolut |
| `og:title` / `og:description` / `og:image` | **1/23** — hanya `/` |
| `og:url` / `og:type` / `og:site_name` | **0/23** |
| `twitter:card` | **0/23** |
| `hreflang` | **0/23** |
| JSON-LD (`application/ld+json`) | **0/23** — tidak ada `LocalBusiness`, `Organization`, `VideoObject`, `BreadcrumbList` |
| `meta robots` | **0/23** |
| `<html lang>` | 23/23 di-hardcode `id` — **isi markup-nya Inggris** |

**Inkonsistensi merek di `<title>`:** 15 halaman memakai suffix `MellogangVisuals`,
8 halaman Generasi B memakai nama domain `mellogang.wyna.dev`. Berdampingan:
`Wedding Packages — mellogang.wyna.dev` vs `Indra & Suci — MellogangVisuals`.

**`meta theme-color` tidak konsisten:** 12× `#f6f0e7`, 2× `#f6efe3`, 8× `#0a0e0d`.

### 4.3 OG image

Satu og:image global, dan itu pun cuma terpasang di satu halaman.

- Deklarasi satu-satunya: `frontend/index.html` → `/assets/brand/mellogang-og.jpg`
- File **ADA dan valid**: 32.840 byte, JPEG baseline, **1200×630** — rasio sudah benar
- Masalah: nilainya **path relatif**, bukan URL absolut. Facebook/WhatsApp/LinkedIn butuh
  absolut (`https://mellogang.wyna.dev/assets/...`); sebagian scraper akan gagal resolve.

**10 halaman portofolio seharusnya punya OG sendiri.** Datanya sudah tersedia — setiap
`cover:` di objek `PROJECTS` menunjuk file yang terverifikasi ada di disk:

| Halaman | Cover siap pakai |
|---|---|
| `/portfolio/indra-suci/` | `assets/video/frames/indra-suci-88.jpg` |
| `/portfolio/eka-nanda/` | `assets/video/eka-nanda.jpg` |
| `/portfolio/bukit-lestari/` | `assets/video/frames/bukit-lestari-12.jpg` |
| `/portfolio/pohen-camp/` | `assets/video/frames/pohen-camp-20.jpg` |
| `/portfolio/blooms-short/` | `assets/video/frames/blooms-short-62.jpg` |
| `/portfolio/blooms-promo/` | `assets/video/frames/blooms-promo-12.jpg` |
| `/portfolio/mandiri-taspen/` | `assets/video/frames/mandiri-taspen-38.jpg` |
| `/portfolio/coinvest-asia/` | `assets/video/frames/coinvest-asia-20.jpg` |
| `/portfolio/wedding-ceremony/` | `assets/brand/instagram/ig-05.jpg` |
| `/portfolio/cultural-event/` | `assets/brand/instagram/ig-02.jpg` |

Hanya tag-nya yang tidak pernah ditulis ke `<head>`.

Aset yatim: `assets/video/frames/pohen-short-{20,50,80}.jpg` ada di disk, tidak dirujuk
halaman mana pun.

### 4.4 Konsistensi nav — matriks bukti

`Y` = ada · `Y*` = ada **dan** `aria-current="page"` · `—` = TIDAK ADA

| Halaman | Home | Portfolio | About | Book | Packages | Workflow | Contact |
|---|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| `/` | **Y\*** | Y | Y | Y | — | — | — |
| `/about/` | Y | Y | **Y\*** | Y | — | — | — |
| `/book/` | Y | Y | Y | **Y\*** | — | — | — |
| `/portfolio/` | Y | **Y\*** | Y | Y | — | — | — |
| 10× `/portfolio/<slug>/` | Y | **Y\*** | Y | Y | — | — | — |
| `/packages/` | **—** | Y | Y | **—** | **Y\*** | Y | Y |
| 6× `/packages/<sub>/` | **—** | Y | Y | **—** | **Y\*** (salah) | Y | Y |
| `/workflow/` | **—** | Y | Y | **—** | Y | **Y\*** | Y |
| `/contact/` | n/a | n/a | n/a | n/a | n/a | n/a | n/a |

Yang hilang, ditegaskan:

- **8 halaman kehilangan link `Home`** (semua `/packages/**` + `/workflow/`). Satu-satunya
  jalan pulang adalah logo.
- **8 halaman kehilangan link `Book Now`.** Halaman yang paling dekat dengan niat beli
  justru tidak menaut ke halaman booking — baik dari header maupun footer.
- **15 halaman kehilangan `Packages`, `Workflow`, `Contact`** → pulau yatim.
- **6 halaman memberi `aria-current="page"` pada URL yang salah**: sub-paket menandai
  `<a href="/packages/">` sebagai halaman aktif. Melaporkan lokasi keliru ke screen reader.

**Mobile menu** (`<nav class="mobile-menu">`) menyimpang lagi secara terpisah:

- `/workflow/` menghilangkan `Workflow` dari mobile menu-nya sendiri, padahal 7 halaman
  `/packages/**` menyertakannya — inkonsistensi *di dalam* Generasi B.
- **`aria-current` tidak pernah dipakai di mobile-menu mana pun (0/22).** Di viewport
  <680px `.nav-links` disembunyikan (`styles.css:12`), jadi penanda halaman aktif hilang
  total untuk semua pengguna, bukan cuma screen reader.
- `#mobileThemeToggle` **hanya ada di `/index.html`** → di 21 halaman lain, mobile tidak
  punya cara mengganti tema.

### 4.5 Konsistensi footer — matriks bukti

| Halaman | Judul blok | Home | Portfolio | About | Book | Sosial | Email | WhatsApp |
|---|---|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| `/` | Navigate | **Y** | Y | Y | Y | Y | Y | Y |
| `/about/` | Navigate | Y | Y | — (self) | Y | Y | Y | Y |
| `/book/` | Navigate | Y | Y | Y | — (self) | Y | Y | Y |
| `/portfolio/` | Navigate | Y | — (self) | Y | Y | Y | Y | **— HILANG** |
| 10× `/portfolio/<slug>/` | **TIDAK ADA BLOK** | — | — | — | — | **—** | **—** | **—** |
| `/packages/` | **Explore** | **—** | Y | **—** | **—** | **—** | **—** | Y |
| 6× `/packages/<sub>/` | **Explore** | **—** | **—** | **—** | **—** | **—** | **—** | Y |
| `/workflow/` | **Explore** | **—** | Y | **—** | **—** | **—** | **—** | Y |
| `/contact/` | **TIDAK ADA `<footer>`** | | | | | | | |

Yang hilang, ditegaskan:

1. **10 halaman detail portofolio tidak punya footer navigasi sama sekali** — 43% situs.
   Footer lengkapnya cuma `© 2026 MellogangVisuals` + `Back to portofolio →`.
2. `/portfolio/` kehilangan tautan WhatsApp di blok Contact, padahal `/`, `/about/`,
   `/book/` punya.
3. 8 halaman Generasi B kehilangan ikon sosial dan alamat email; kontaknya cuma WhatsApp.
4. Judul blok berbeda: `Navigate` vs `Explore`; `Contact` vs `Start a conversation`.
5. Copyright berbeda: `© 2026 MellogangVisuals` vs `© 2026 mellogang.wyna.dev`.
6. **Aturan self-link tidak seragam:** `/` menampilkan `Home` di footer-nya sendiri,
   sementara `/about/`, `/book/`, `/portfolio/` menghapus link ke dirinya sendiri.
7. **`data-i18n` hilang di footer `/portfolio/`** — judul `Navigate`/`Contact` dan tagline
   tanpa atribut, jadi saat mode ID footer halaman portofolio tetap Inggris.

> Ini menjawab bug #1 megaprompt, dan kondisinya lebih buruk dari yang dilaporkan:
> bukan cuma "About Us hilang di /about/", tapi **tiga blok navigasi terpisah per file
> (nav desktop, nav mobile, footer) yang ketiganya sudah saling menyimpang.**

### 4.6 Header CTA

| Kategori | Jumlah | Halaman |
|---|:--:|---|
| Punya `<a class="btn btn-primary btn-small" href="/book/">Book Now</a>` | **13** | `/`, `/about/`, `/portfolio/`, 10 detail |
| Tidak punya — memang halaman itu sendiri | 1 | `/book/` |
| Punya CTA **berbeda dan rusak** (`/#booking`) | **8** | `/packages/**`, `/workflow/` |
| Tidak punya header sama sekali | 1 | `/contact/` |

CTA Generasi B rusak tiga kali:

1. **Tidak ter-style** — `.button`, `.button-primary`, `.button-small` tidak ada di
   `frontend/styles.css`. Render sebagai tautan telanjang.
2. **Anchor mati** — `href="/#booking"`, `grep -rl 'id="booking"' frontend/` = NONE.
3. **Tidak ikut disembunyikan di mobile** — media query `styles.css:12` menyasar
   `.nav-tools>.btn-primary`; CTA Generasi B bukan anak `.nav-tools`, jadi di layar sempit
   ia tetap muncul sebagai tautan telanjang di samping tombol hamburger.

**Dan `data-i18n` hilang di 12 dari 13 CTA Generasi A** — hanya `/index.html` yang punya
`data-i18n="btn.book.small"`. Di `/about/`, `/portfolio/`, dan 10 halaman detail tulisannya
`Book Now` polos, tidak pernah berubah jadi "Pesan" di mode ID.

> Jawaban untuk bug #2 megaprompt: CTA hilang di `/book/` itu **benar dan disengaja**
> (halaman itu sendiri). Yang tidak disengaja adalah 8 halaman dengan CTA rusak. Aturannya
> perlu ditulis eksplisit: CTA tampil di semua halaman kecuali `/book/`.

**Fitur mati tambahan:** `/packages/<slug>/` menaut ke `/?package=<slug>#booking`.
`frontend/script.js` tidak pernah membaca `URLSearchParams`/`location.search`, jadi
`<select id="bookPackage">` tidak pernah dipreseleksi. Fitur ini mati total.

### 4.7 Duplikasi struktur: berapa file untuk mengubah satu link nav?

**Nav dan footer di-copy-paste utuh ke setiap file. Tidak ada partial, komponen, atau build step.**

Bukti: tidak ada `package.json` di root (satu-satunya ada di `video/`, proyek Remotion
terpisah); `builds` adalah skrip toggle stabilitas CodeIgniter; `tools/` cuma berisi
screenshot dan social-fetcher; tidak ada `.github/workflows/`; nol file di luar `frontend/`
yang menyebut path `frontend/`.

Untuk mengubah satu link nav:

- **22 file** untuk `<nav class="nav-links">`
- **22 file** lagi untuk `<nav class="mobile-menu">` (blok terpisah, file yang sama)
- **+12 file** untuk blok footer

Sampai **3 blok terpisah per file**, dan ketiganya sudah terbukti menyimpang hari ini.
Ini akar penyebab bug #1 dan #2 megaprompt — bukan kelalaian satu kali, tapi konsekuensi
struktural yang pasti berulang.

### 4.8 Aksesibilitas

| Atribut | Cakupan |
|---|---|
| `aria-pressed` | **0/23** — di seluruh situs |
| `aria-current` pada `.lang-btn` | **0/23** |
| `aria-current` pada mobile-menu | **0/22** |
| `:focus-visible` di `styles.css` | **0** kemunculan |
| `.skip` (skip link) | 4/23 — hilang di 19 halaman |
| `aria-label` pada `#themeToggle` (markup) | 4/23 — 10 halaman detail cuma punya glyph `☾` |
| `aria-label` pada `#menuButton` | 5/23 — hilang di 18 halaman, cuma glyph `☰` |
| `aria-controls` / `aria-expanded` pada `#menuButton` | **1/23** — hanya `/index.html` |
| `aria-label` pada `<nav class="nav-links">` | **1/23** — hanya `/index.html` |

- **State bahasa tidak pernah terekspos ke screen reader.** Aktif hanya lewat class
  `.active` (`styles.css:6`). Pengguna SR tidak punya cara apa pun tahu ID atau EN yang
  sedang aktif.
- **`#themeToggle` melaporkan aksi, bukan state.** `aria-label` di-update JS jadi
  "Switch to light/dark theme" — selalu Inggris, tanpa `aria-pressed`. Tanpa JS: nol info.
- `aria-label="Bahasa"` di-hardcode Indonesia di 15 halaman, termasuk saat mode EN.
- **Focus ring tidak pernah dirancang.** Hanya `.skip:focus` dan field form yang punya
  gaya fokus. Semua tautan nav, tautan footer, `.btn`, `.theme-toggle`, `.menu-button`,
  `.lang-btn`, `.work-card` bergantung pada ring bawaan browser — tanpa jaminan kontras di
  atas latar krem, dan tanpa pemisahan fokus keyboard vs klik mouse.
- Halaman dengan dua `<nav>` (desktop + mobile) keduanya tanpa nama → daftar landmark
  screen reader menampilkan dua "navigation" yang tidak bisa dibedakan.

---

## 5. Konfigurasi Vercel

Ada **dua** `vercel.json` di repo: satu di root, satu di `frontend/`. Keduanya
**identik byte-per-byte** (diverifikasi `diff`, exit 0):

```json
{
  "cleanUrls": true,
  "trailingSlash": true,
  "headers": [{"source":"/(.*)","headers":[
    {"key":"X-Content-Type-Options","value":"nosniff"},
    {"key":"Referrer-Policy","value":"strict-origin-when-cross-origin"}]}]
}
```

**Efek kedua flag:**

- `cleanUrls: true` — `.html` distrip dari URL yang disajikan; request ke `/about.html`
  di-308 ke bentuk bersih.
- `trailingSlash: true` — bentuk kanonik **memakai** trailing slash; `/about` di-308 ke
  `/about/`.
- Gabungannya: `frontend/about/index.html` kanonik di `/about/`.
- **Karena semua link internal sudah ditulis dengan trailing slash (0 pelanggaran di 536
  referensi), tidak ada hop redirect yang dibayar di mana pun.** Ini sudah benar dan harus
  dipertahankan lewat refactor apa pun.

**Apakah keduanya konflik?** Tidak dalam arti "setelan bertentangan" — isinya identik.
Tapi **hanya satu yang pernah dibaca**: Vercel membaca `vercel.json` dari Root Directory
project, yaitu `frontend/`. Root `vercel.json` inert.

Bahaya sebenarnya adalah **drift**: dua salinan yang harus disunting serempak tanpa ada
yang menegakkan. Digabung dengan pohon statis duplikat di root (§1.1), salah setel Root
Directory akan diam-diam men-deploy situs lama — yang tidak punya `/book/`, tidak punya
i18n, tidak punya halaman detail portofolio — dan **kelihatan seperti situs yang bekerja**,
bukan gagal dengan berisik.

**Yang tidak ada di konfigurasi:** `redirects` (nol), `rewrites` (nol),
`Content-Security-Policy`, `Strict-Transport-Security`, `X-Frame-Options`, dan
`Cache-Control` panjang untuk `/assets/**`.

---

## 6. Operasional: kredensial dan domain

Dua hal ini diminta user di luar megaprompt dan **sudah dikerjakan**, dicatat di sini
supaya jejaknya jelas.

### 6.1 `creds.txt` sudah di-gitignore

- Ditambahkan blok eksplisit di `.gitignore` (`creds.txt`, `*.creds.txt`, `creds/`).
- Diverifikasi: `git check-ignore -v creds.txt` → `.gitignore:157`.
- **File itu belum pernah ter-commit** (status `??` sebelum perubahan), jadi tidak ada
  riwayat git yang perlu di-purge dan tidak ada kunci yang perlu dirotasi karena repo.
- Catatan keamanan di dalam file itu sendiri sudah benar dan tetap berlaku: isinya
  kredensial hidup, plaintext.

### 6.2 `https://mellogang.wyna.dev/` sudah aktif

Diagnosis: DNS **sudah** benar sejak awal —
`mellogang.wyna.dev → cname.vercel-dns.com` (diverifikasi `nslookup` via 8.8.8.8).
Yang kurang: domainnya belum terdaftar di project Vercel, jadi tidak ada sertifikat TLS
diterbitkan → handshake gagal (curl exit 35, HTTP 000).

Tindakan: `POST /v10/projects/prj_.../domains` dengan `{"name":"mellogang.wyna.dev"}`
→ `verified: true`. Verifikasi ulang: `curl -L https://mellogang.wyna.dev/` → **200**.

Domain project sekarang: `mellogang.vercel.app`, `mellogangvisual.wyna.dev` (lama, masih
aktif), `mellogang.wyna.dev` (baru).

**Belum diputuskan:** apakah `mellogangvisual.wyna.dev` (ejaan lama, tanpa "s") mau
di-308-kan ke `mellogang.wyna.dev` supaya cuma ada satu host kanonik. Selama dua host
menyajikan konten identik tanpa canonical absolut (§4.2), keduanya bersaing di indeks.
Ini perlu dibereskan bersamaan dengan pemasangan canonical.

### 6.3 Baseline test

- `vendor/` **tidak ada** dan `tests/e2e/node_modules/` **tidak ada** → suite PHPUnit dan
  Playwright **tidak bisa dijalankan** di checkout ini tanpa `composer install` dan
  `npm install` lebih dulu.
- Toolchain tersedia: PHP 8.2.12, Composer 2.9.2, Node v24.1.0, npm 11.3.0.
- Relevansi: seluruh pekerjaan megaprompt ini ada di `frontend/` (HTML/CSS/JS statis) dan
  **tidak menyentuh PHP sama sekali**, jadi risiko regresi ke suite PHPUnit nol. Tapi
  baseline-nya tetap perlu dibangun sebelum bisa mengklaim "tetap lulus".

---

## 7. Register bug megaprompt — vonis per item

| # | Klaim megaprompt | Vonis | Bukti |
|---|---|---|---|
| 1 | Footer nav berubah-ubah antar halaman | **TERKONFIRMASI, lebih parah** | §4.5. Bukan cuma About hilang di `/about/`: 10 halaman detail tidak punya blok footer sama sekali, `/portfolio/` kehilangan WhatsApp, Generasi B kehilangan sosial+email+Home+Book, judul blok dan copyright berbeda. |
| 2 | CTA header hilang di `/book/` | **TERKONFIRMASI, tapi benar** | §4.6. Hilang di `/book/` memang disengaja. Yang tidak disengaja: 8 halaman dengan CTA rusak, dan `data-i18n` hilang di 12 dari 13 CTA. Aturannya harus ditulis eksplisit. |
| 3 | Label nav bocor bahasa ("Portofolio" di EN) | **TERKONFIRMASI, akar lebih dalam** | §2.4. Salah ejanya ada **di kamus EN itu sendiri** (`script.js:39`, `:43`, `:45`), bukan cuma di markup. Memilih EN tidak pernah menghasilkan "Portfolio". Di halaman Generasi B yang markup-nya sudah benar, JS justru merusaknya saat runtime. |
| 4 | String asing `түз` di akhir `/about/` | **TERKONFIRMASI, sumber ditemukan, lebih luas dari yang dilaporkan** | §7.1 di bawah. Sudah diperbaiki. |
| 5 | Bahasa tidak punya URL | **TERKONFIRMASI** | §2.2. Murni client-side, nol `?lang=`/`/id/`/`/en/`, nol `hreflang`. Versi Indonesia tidak bisa dishare dan tidak bisa diindeks. |
| 6 | Trailing slash tidak konsisten | **TIDAK TERKONFIRMASI — sudah bersih** | §1.6. 536 referensi internal: 0 tanpa trailing slash, 0 relatif. `cleanUrls`+`trailingSlash` sudah menghasilkan satu bentuk kanonik. Tidak ada yang perlu diperbaiki. |
| 7 | Route detail portofolio belum diverifikasi | **TERVERIFIKASI — hidup, tapi bermasalah** | §1.7. 10/10 hidup, title unik, punya link kembali. **Tidak ada prev/next di satu pun.** OG per proyek tidak ada. 4 dari 10 punya title/isi statis yang **bertentangan** dengan yang ditulis JS saat runtime. |
| 8 | Slug tidak seragam | **TERKONFIRMASI, plus typo** | §1.8. Tiga sumbu penamaan bercampur. Bonus: `coinvest-asia` typo — seharusnya **CoinFest**. |

### 7.1 Bug #4 diperiksa tuntas: dua file, dan yang kedua kebocoran output model

Megaprompt melaporkan satu string Kiril di akhir `/about/`. Pemindaian seluruh 23 file untuk
"apa pun yang muncul setelah `</html>`" menemukan **dua** file kotor.

**`frontend/about/index.html`** — 7 byte: satu spasi + `d1 82 d2 af d0 b7` (UTF-8 `түз`).

**`frontend/portfolio/index.html`** — 165 byte, jauh lebih parah:

```
"}..........................................................线蕉assistant to=functions.write_file
(commentary)  સપ jsonikwembu  大发快三怎么json еиз
```

Ini bukan sisa i18n rusak dan bukan paste tidak sengaja dari manusia. Ini **token stream mentah
sebuah model** yang ikut tertulis ke file — potongan `assistant to=functions.write_file` adalah
penanda protokol tool-call — bercampur teks spam judi berbahasa Mandarin (`大发快三`) yang khas
muncul sebagai derau di korpus latih.

**Penelusuran sumber:** `git log -S` menunjuk keduanya ke commit yang sama,
`82d4170 feat: rebuild studio portfolio and booking experience` — commit yang membangun
Generasi A. Jadi model yang menulis file-file itu membocorkan output mentahnya, lalu ikut
ter-commit dan ter-deploy. Pohon statis legacy di root **bersih**, jadi kontaminasinya persis
dua file.

**Dampak yang tidak boleh diremehkan:** teks spam judi berbahasa Mandarin di halaman publik
adalah pola yang dipakai mesin pencari untuk menandai situs terkena injeksi spam. Ini bukan
sekadar cacat kosmetik.

**Status: sudah diperbaiki.** Kedua file dipotong tepat di `</html>`
(`about` 5244→5238 byte, `portfolio` 6798→6634 byte). Verifikasi ulang seluruh 23 file: nol sisa
setelah `</html>`; `grep` `түз` dan `functions.write_file` di seluruh repo mengembalikan kosong.

### 7.2 Bug tambahan yang tidak ada di megaprompt

| # | Temuan | Dampak |
|---|---|---|
| T1 | `frontend/` berisi **dua generasi desain**; 8 halaman Generasi B memakai 12 class CSS yang sudah dihapus | 8 halaman tampil tanpa style di produksi, terindeks, off-brand |
| T2 | `/assets/vendor/gsap.min.js` + `ScrollTrigger.min.js` dirujuk 14 halaman, direktorinya tidak ada | 28 request 404 per crawl; animasi scroll mati diam-diam |
| T3 | Pohon statis duplikat di root repo (13 file, isi berbeda) tidak di-deploy ke mana pun | Risiko menyunting salinan yang salah; footgun Root Directory |
| T4 | Halaman detail portofolio menimpa `<h1>`, deskripsi, dan `document.title` sendiri dari `PROJECTS` | Teks berubah di depan mata; crawler dan pengguna melihat versi berbeda |
| T5 | `/contact/` stub meta-refresh, bukan 308; masih ditaut 8 halaman | Soft-redirect, title duplikat dengan `/about/` |
| T6 | `?package=<slug>` tidak pernah dibaca `script.js` | Preseleksi paket mati total di 6 halaman |
| T7 | Pesan WhatsApp selalu Indonesia, tapi label paketnya diambil dari `<option>` Inggris | Pesan campur bahasa apa pun bahasa yang dipilih |
| T8 | Live tertinggal 2 commit dari repo (deploy terakhir = `1121ab4`) | Sebagian temuan "dari live" sudah tidak berlaku untuk repo |
| T9 | `#mobileThemeToggle` hanya di `/index.html` | 21 halaman: mobile tidak bisa ganti tema |
| T10 | Font dimuat dari `fonts.googleapis.com` (`styles.css:1`) | Melanggar aturan repo sendiri di CLAUDE.md ("No CDN fonts — woff2 self-hosted") |
| T11 | Palet `frontend/styles.css` (krem `#f6efe3` / emas `#c69a61`) tidak sama dengan brand di CLAUDE.md (`#00F5B8` / `#0A0E0D`) | Dokumentasi dan kode tidak sinkron |
| T12 | Link "Back to portofolio →" **hancur saat load** di 10 halaman detail — `lang()` menimpa `innerHTML` dengan nilai key `nav.portfolio`, jadi tersisa satu kata "Portofolio" (§3.6) | Satu-satunya navigasi footer di 10 halaman jadi ambigu, di kedua bahasa |
| T13 | Satu key i18n (`nav.portfolio`) dipakai untuk dua peran berbeda: label nav dan teks link kembali | Akar T12; pasti berulang kalau skema key tidak dibenahi |

### 7.3 Bug CI4 lama yang masih hidup (`bugging.md`, di luar scope)

Dicek ulang kelimanya. **Sudah dibereskan:** #1 (`whereNotIn` + `LOWER`), #3
(`Editor/TugasController`). **Masih ada:**

| Bug | Lokasi | Isi |
|---|---|---|
| #2 | `app/Controllers/Admin/PembayaranController.php:30` | `"LOWER(p.status_verifikasi) = '{$st}'"` — variabel di-embed langsung ke string SQL |
| #4 | `app/Controllers/Admin/PemesananController.php:106` | `->where('LOWER(status_verifikasi)', 'valid')` — CI4 memperlakukannya sebagai nama kolom |
| #5 | `app/Controllers/Public/StatusController.php:436` | sama dengan #4 |

**Dan satu yang belum pernah dicatat:**
`app/Controllers/Editor/DashboardController.php:77` dan `:96` mem-filter
`NOT IN ('done', 'revisi selesai')` — memakai **spasi**, padahal CLAUDE.md menyatakan
status sudah dinormalisasi kanonik ke `revisi_selesai` (migration `100005`). Nilai
berspasi itu tidak akan pernah cocok lagi, jadi pop-up tugas editor akan **ikut
menampilkan tugas yang sudah `revisi_selesai`** — persis regresi yang Bug #1 dulu
dimaksudkan untuk dicegah.

Seluruh pola `LOWER()` di atas juga melanggar aturan yang ditulis repo sendiri di
CLAUDE.md: "All status values are stored as canonical snake_case — no more
`LOWER(col) = '...'` queries (would defeat indexes)."

---

## 8. Yang butuh keputusan sebelum Fase 1 jalan

### 8.1 Strategi URL bahasa — **wajib diputuskan user**

Megaprompt menetapkan bahasa default situs adalah **Indonesia**. Tiga opsi:

| Opsi | Bentuk | Untung | Rugi |
|---|---|---|---|
| **A** | `/id/...` + `/en/...`, `/` redirect ke `/id/` | SEO terbaik, simetris | 46 file HTML (23×2), semua link internal harus sadar prefiks |
| **B** | ID di `/`, EN di `/en/` | ID dapat URL terpendek, EN tetap terindeks, 23 file tambahan | Asimetris; `hreflang` tetap wajib |
| **C** | Tetap client-side, diperbaiki | Paling murah — perbaiki default, tambah `?lang=`, benahi `<html lang>` | Versi ID **tetap tidak bisa diindeks**; masalah pokok tidak terselesaikan |

**Rekomendasi: B.** Alasannya spesifik untuk repo ini, bukan preferensi umum:

1. Situs ini **tidak punya build step sama sekali** (§4.7). A berarti memelihara 46 file
   HTML dengan tangan; B berarti 23. Selisihnya besar justru karena tidak ada generator.
2. Mayoritas klien lokal, jadi ID pantas dapat URL tanpa prefiks.
3. Konten sekarang **99% Inggris keras** (§2.5). Membangun `/en/` dari markup yang sudah
   ada jauh lebih murah daripada menerjemahkan dua arah sekaligus.

**Tapi ada prasyarat yang harus diakui jujur:** apa pun opsi yang dipilih, tanpa build step
setiap perubahan copy harus disalin tangan ke 23 atau 46 file. Sebelum menyentuh copy,
sebaiknya diputuskan dulu apakah mau **memperkenalkan generator kecil** (satu skrip Node
yang merender halaman dari satu file data + partial nav/footer). Kalau tidak, target
megaprompt "satu sumber kebenaran untuk semua string" dan "satu partial nav dan footer
dipakai semua halaman" secara teknis **tidak bisa dipenuhi** — hanya bisa didekati dengan
disiplin manual yang sudah terbukti gagal (§4.4, §4.5).

### 8.2 Nasib `/packages/**` dan `/workflow/` — **wajib diputuskan user**

8 halaman, tampil tanpa style, yatim dari nav utama, CTA mati, merek lama. Tiga pilihan:

1. **Port ke Generasi A** — nav, class `.btn`, lang switch, theme toggle, CTA `/book/`,
   merek MellogangVisuals. Paling banyak kerjanya, tapi mempertahankan konten paket yang
   mungkin masih bernilai jual.
2. **Hapus + `redirects` ke `/book/`** — paling bersih, dan konten paketnya toh tidak
   pernah tertaut dari situs utama.
3. **Biarkan** — kondisi terburuk, karena terindeks dalam keadaan rusak.

Ini bukan keputusan teknis, ini keputusan bisnis: **apakah daftar paket masih dipakai
menjual?** Perlu jawaban user.

### 8.3 Pohon statis duplikat di root — usul: hapus

Tidak di-deploy ke mana pun (§1.1). Menghapus `index.html`, `styles.css`, `script.js`,
`about/`, `contact/`, `packages/`, `portfolio/`, `workflow/`, `assets/` di root plus root
`vercel.json` akan menghilangkan seluruh kelas kesalahan "menyunting salinan yang salah".
Perlu konfirmasi user karena ini penghapusan, tapi risikonya nol.

### 8.4 GSAP — usul: hapus tag-nya

Animasi sudah mati di produksi sejak lama (guard `!window.gsap` selalu true). Dua pilihan:
vendor file-nya ke `frontend/assets/vendor/` (~70KB, menghidupkan animasi), atau hapus dua
`<script>` tag itu (menghapus 28 404, nol perubahan visual karena memang sudah mati).
Usul: hapus tag-nya sekarang, hidupkan animasi belakangan kalau memang diinginkan —
ini keputusan desain, dan user sudah bilang desain akan diganti nanti.

### 8.5 Dua host bersaing

`mellogangvisual.wyna.dev` dan `mellogang.wyna.dev` menyajikan konten identik tanpa
canonical absolut. Usul: `mellogang.wyna.dev` jadi kanonik, `mellogangvisual.wyna.dev`
di-308 ke sana. Perlu konfirmasi user karena host lama mungkin sudah dibagikan ke klien.
