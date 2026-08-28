# FINDINGS — Lampiran: tabel string baris-per-baris

Lampiran dari `FINDINGS.md` §3. Tabel ini basis kerja Fase 2.
Sitasi `path:1` karena semua HTML minified satu baris — penanda posisi adalah nama blok/selector.

# 2. TABEL STRING

Kolom "ID" = padanan Bahasa Indonesia yang **sudah ada di kamus `I18N.id`** (`frontend/script.js:38-53`).
`(kosong)` = tidak ada padanan ID sama sekali; string tampil dalam bahasa Inggris walau tombol ID aktif.

## 2.1 Komponen global (muncul di banyak halaman)

| Halaman (URL) | Lokasi / selector atau key i18n | Teks EN | Teks ID |
|---|---|---|---|
| gen-B | `a.skip[href="#main"]` (hanya `/`, `/about/`, `/book/`, `/portfolio/`) | Skip to content | (kosong) |
| semua | `a.brand > img[alt]` | MellogangVisuals | (kosong) |
| semua | `span.brand-wordmark` | MellogangVisuals | (kosong) |
| gen-B | `nav.nav-links a[href="/"]` — key `nav.home` | Home | Beranda |
| gen-B | `nav.nav-links a[href="/portfolio/"]` — key `nav.portfolio` | **Portofolio** (ejaan ID dipakai di kamus EN) | Portofolio |
| gen-B | `nav.nav-links a[href="/about/"]` — key `nav.about` | About Us | Tentang Kami |
| gen-B | `nav.nav-links a[href="/book/"]` — key `nav.book` | Book Now | Pesan |
| gen-B | `.lang-switch[aria-label]` | Bahasa | (kosong) |
| gen-B | `.lang-btn[data-lang=id]` / `[data-lang=en]` | ID / EN | ID / EN |
| gen-B | `#themeToggle[aria-label]` (statis di HTML) | Switch theme | (kosong) |
| gen-B | `#themeToggle` innerHTML — `script.js:16` | `☾` / `☼` | (kosong) |
| gen-B | `#themeToggle[aria-label]` runtime — `script.js:16` | Switch to light theme / Switch to dark theme | (kosong) |
| `/` saja | `#mobileThemeToggle` textContent — `script.js:16` | Dark theme / Light theme | (kosong) |
| gen-B | `#menuButton[aria-label]` | Open navigation | (kosong) |
| gen-B | `#menuButton` | `☰` | (kosong) |
| `/` | `nav.nav-links[aria-label]` | Primary | (kosong) |
| gen-B | `a.btn.btn-primary.btn-small[href="/book/"]` — key `btn.book.small` (hanya di `/`) | Book Now | Pesan |
| gen-B | tombol yang sama di `/about/`, `/portfolio/`, 10 halaman detail — **tanpa `data-i18n`** | Book Now | (kosong) |
| gen-B | `.footer-title` (kolom 1) | MellogangVisuals | (kosong) |
| gen-B | `footer p` — key `footer.tagline` (hanya di `/`) | Photo and video production for weddings, events, brands and destinations. | Produksi foto dan video untuk pernikahan, brand, acara, dan destinasi. |
| gen-B | `footer p` di `/about/`, `/book/`, `/portfolio/` — **tanpa `data-i18n`** | Photo and video production for weddings, events, brands and destinations. | (kosong) |
| gen-B | `.footer-title` — key `footer.nav` (hanya di `/`) | Navigate | Navigasi |
| gen-B | `.footer-title` "Navigate" di halaman lain — tanpa key | Navigate | (kosong) |
| gen-B | `.footer-title` — key `footer.contact` (hanya di `/`) | Contact | Kontak |
| gen-B | `.footer-title` "Contact" di halaman lain — tanpa key | Contact | (kosong) |
| gen-B | `footer a[href^=mailto]` | mellogang@wyna.dev | (sama) |
| gen-B | `footer a[href*="wa.me"]` — key `footer.whatsapp` (hanya di `/`) | WhatsApp the studio | WhatsApp studio |
| gen-B | link WhatsApp di `/about/`, `/book/` — tanpa key | WhatsApp the studio | (kosong) |
| gen-B | `.social-icon img[alt]` | Instagram / YouTube | (kosong) |
| gen-B | `.footer-bottom span` | © 2026 MellogangVisuals | (kosong) |
| gen-B | `.footer-bottom span` | mellogang.wyna.dev | (kosong) |
| gen-A | `.footer-bottom span` | © 2026 mellogang.wyna.dev | (kosong) |
| gen-A | `nav.nav-links a[href="/packages/"]` | Packages | (kosong) |
| gen-A | `nav.nav-links a[href="/workflow/"]` | Workflow | (kosong) |
| gen-A | `nav a[href="/about/"]` — key `nav.about` (teks HTML "About" **ditimpa** runtime) | About | Tentang Kami |
| gen-A | `nav a[href="/portfolio/"]` — key `nav.portfolio` (teks HTML "Portfolio" **ditimpa** runtime) | Portfolio | Portofolio |
| gen-A | `nav.nav-links a[href="/contact/"]` | Contact | (kosong) |
| gen-A | `a.button.button-primary.button-small[href="/#booking"]` | Book a date | (kosong) |
| gen-A | `a.wa-float` | WhatsApp ↗ | (kosong) |
| gen-A | `.footer-title` | mellogang.wyna.dev | (kosong) |
| gen-A | `.footer-title` | Explore | (kosong) |
| gen-A | `.footer-title` (packages/*, workflow) | Start a conversation / Contact | (kosong) |
| gen-A | `footer a[href*="wa.me"]` | WhatsApp the studio | (kosong) |

## 2.2 `/` — Beranda (`frontend/index.html:1`)

| Lokasi / selector atau key | Teks EN | Teks ID |
|---|---|---|
| `<title>` | MellogangVisuals — Photo & Video Production | (kosong) |
| `meta[name=description]` | MellogangVisuals — photo and video production for weddings, events, brands and destinations. | (kosong) |
| `meta[property="og:title"]` | MellogangVisuals — Photo & Video Production | (kosong) |
| `meta[property="og:description"]` | Photo and video production from Bali for brands, couples and meaningful occasions. | (kosong) |
| `.hero .eyebrow` (tanpa key) | MellogangVisuals · Bali | (kosong) |
| `h1.display-title` — `hero.title` | Visuals`<br>`with purpose. | Visual`<br>`dengan tujuan. |
| `p.hero-copy` — `hero.copy` | Photo and video production for weddings, brands, events and destinations. We work with a clear brief, a considered process and an honest visual point of view. | Produksi foto dan video untuk pernikahan, brand, acara, dan destinasi. Kami bekerja dengan brief yang jelas, proses yang tertata, dan sudut pandang visual yang jujur. |
| `.hero-actions a.btn-primary` — `hero.cta1` | Book a project ↗ | Pesan project ↗ |
| `.hero-actions a.btn-outline` — `hero.cta2` | View portofolio | Lihat portofolio |
| `.hero-meta span` — `hero.meta1` | Bali, Indonesia | Bali, Indonesia |
| `.hero-meta span` — `hero.meta2` | Photo & video | Foto & video |
| `.hero-meta span` — `hero.meta3` | Available for selected dates | Tersedia untuk tanggal terpilih |
| `.hero-image img[alt]` | MellogangVisuals wedding documentation | (kosong) |
| `.hero-stamp` | Photo`<br>`& video`<br>`maker | (kosong) |
| `.hero-caption small` | Selected work | (kosong) |
| `.hero-caption strong` | **Stories, carefully made.** | (kosong) |
| `.dark-works .eyebrow` — `works.eyebrow` | Portofolio | Portofolio |
| `.dark-works h2.display` — `works.title` | Selected works. | Karya pilihan. |
| `.section-note` — `works.note` | A selection of prewedding, destination, corporate and event projects from the MellogangVisuals archive. | Sebagian project prewedding, destinasi, corporate, dan event dari arsip MellogangVisuals. |
| kartu 1 `img[alt]` / `small` / `h3` / `p` | Indra & Suci prewedding / Prewedding · 2024 / Indra & Suci / Watch project | (kosong) |
| kartu 2 | Puncak Bukit Lestari promotional film / Destination · 2023 / Puncak Bukit Lestari / Watch project | (kosong) |
| kartu 3 | Wedding ceremony portrait / Wedding / Ceremony stories | (kosong) |
| kartu 4 | Cultural event documentation / Event / Cultural event | (kosong) |
| kartu 5 | PT Bank Mandiri Taspen project / Corporate · 2023 / Mandiri Taspen | (kosong) |
| `.work-card .work-arrow` (x5) | ↗ | (kosong) |
| `a.arrow-link[href="/portfolio/"]` — `works.viewall` | View all projects → | Lihat semua project → |
| `.about-strip .eyebrow` — `about.eyebrow` | About us | Tentang kami |
| `.about-strip h2.display` — `about.title` | **Clear work.`<br>`Good people.** | Kerja rapi.`<br>`Tim yang enak diajak kerja. |
| `.about-copy p` — `about.copy` | MellogangVisuals is a Bali-based photo and video team. We help clients turn a brief, a place or a milestone into work that feels direct and well made. | MellogangVisuals adalah tim foto dan video yang berbasis di Bali. Kami bantu klien mengubah brief, lokasi, atau momen penting menjadi karya yang terasa pas dan selesai. |
| `a.arrow-link[href="/about/"]` — `about.link` | More about the studio → | Selengkapnya tentang studio → |
| `.about-image img[alt]` | MellogangVisuals cultural event location | (kosong) |
| `.cta .eyebrow` — `cta.eyebrow` | Have a project in mind? | Punya rencana project? |
| `.cta h2.display` — `cta.title` | **Let's talk dates.** (apostrof tipografis) | Ngobrolin tanggal. |
| `.cta p` — `cta.copy` | Tell us what you are planning and we will reply with the next practical step. | Ceritain apa yang mau kamu garap, nanti kami balas dengan langkah berikutnya. |
| `.cta a.btn-primary` — `cta.btn` | Book now ↗ | Pesan sekarang ↗ |

## 2.3 `/about/` (`frontend/about/index.html:1`)

| Lokasi / selector | Teks EN | Teks ID |
|---|---|---|
| `<title>` | About Us — MellogangVisuals | (kosong) |
| `meta[name=description]` | About MellogangVisuals, a Bali-based photo and video production team. | (kosong) |
| — | **tidak ada `og:title` / `og:description` / `og:image`** | — |
| `.page-hero .eyebrow` | About us | (kosong) |
| `h1.display` | **A focused team`<br>`behind the frame.** | (kosong) |
| `.page-hero p` | MellogangVisuals is a Bali-based photo and video production team working across weddings, events, destinations, corporate projects and brand content. | (kosong) |
| `.about-copy .eyebrow` | How we work | (kosong) |
| `.about-copy h2.display` | **Professional from brief to delivery.** | (kosong) |
| `.about-copy p` (1) | We start by understanding the purpose of the project, then plan the right crew, schedule and visual approach. On set, we keep the process organised and collaborative. In post, we focus on **a clean finish that serves the brief**. | (kosong) |
| `.about-copy p` (2) | Our work is built around clear communication, reliable production and **images that remain useful after** the launch, event or celebration is over. | (kosong) |
| `.about-image img[alt]` | MellogangVisuals — Bali | (kosong) |
| `.section-dark .eyebrow` | What clients can expect | (kosong) |
| `.section-dark h2.display` | **Clear process.`<br>`Consistent work.** | (kosong) |
| `.point strong` (1) / `span` | Pre-production / Scope, references, schedule and practical planning before the shoot. | (kosong) |
| `.point strong` (2) / `span` | Production / A prepared crew, **calm communication and attention to the details** that matter. | (kosong) |
| `.point strong` (3) / `span` | Post-production / Selection, editing, colour and delivery handled to the agreed brief. | (kosong) |
| `.cta .eyebrow` | Work with us | (kosong) |
| `.cta h2.display` | Tell us the brief. | (kosong) |
| `.cta p` | We will respond with the next practical step. | (kosong) |
| `.cta a.btn-primary` | Book Now ↗ | (kosong) |
| **setelah `</html>`** | **`түз`** ← karakter nyasar, lihat §4 | — |

Catatan: footer `/about/` kehilangan link "About Us" (kolom Navigate hanya Home / Portofolio / Book Now).

## 2.4 `/portfolio/` (`frontend/portfolio/index.html:1`)

| Lokasi / selector | Teks EN | Teks ID |
|---|---|---|
| `<title>` | Portofolio — MellogangVisuals | (kosong) |
| `meta[name=description]` | Selected photo and video projects by MellogangVisuals. | (kosong) |
| `.page-hero .eyebrow` | Portofolio | (kosong) |
| `h1.display` | Selected`<br>`projects. | (kosong) |
| `.page-hero p` | Commercial films, prewedding sessions, destination work and documentary coverage. Open a project to watch the film or browse the supporting gallery. | (kosong) |
| `.works-filter .filter` x5 | All / Film / Photo / Commercial / Wedding | (kosong) |
| kartu 1 `img[alt]` / `small` / `h3` / `p` | Indra & Suci / Prewedding · 2024 / Indra & Suci / Film + gallery | (kosong) |
| kartu 2 | Eka & Nanda / Prewedding · 2024 / Eka & Nanda / Film + gallery | (kosong) |
| kartu 3 | Puncak Bukit Lestari / Destination · 2023 / Puncak Bukit Lestari | (kosong) |
| kartu 4 | The Blooms Garden Bali / Commercial · 2023 / The Blooms Garden | (kosong) |
| kartu 5 | The Blooms Garden promotional video / Commercial · 2023 / Blooms Garden Promo | (kosong) |
| kartu 6 | PT Bank Mandiri Taspen / Corporate · 2023 / Mandiri Taspen | (kosong) |
| kartu 7 | Wedding ceremony / Wedding / Ceremony stories | (kosong) |
| kartu 8 | Cultural event / Event / Cultural event | (kosong) |
| kartu 9 | Pohen Hill Camp / Destination · 2023 / Pohen Hill Camp | (kosong) |
| kartu 10 | CoinFest Asia 2022 / Event · 2022 / CoinFest Asia | (kosong) |
| `.cta .eyebrow` | Start a project | (kosong) |
| `.cta h2.display` | Book your date. | (kosong) |
| `.cta p` | We will review the brief and reply with availability. | (kosong) |
| `.cta a.btn-primary` | Book Now ↗ | (kosong) |
| **setelah `</html>`** | **blob sampah generasi LLM** — lihat §4 | — |

Catatan: footer `/portfolio/` kehilangan link "Portofolio" **dan** seluruh baris WhatsApp.

## 2.5 Halaman detail portofolio (10 halaman)

Kerangka statis bersama:

| Lokasi / selector | Teks EN | Teks ID |
|---|---|---|
| `.detail-meta span` label | Year / Format / Source | (kosong) |
| `.detail-aside h3.display` | Project notes. | (kosong) |
| `a[data-detail-source]` (HTML) | Watch project ↗ *atau* View Instagram ↗ | (kosong) |
| `a[data-detail-source]` (runtime, `script.js:20`) | **Watch on YouTube ↗** *atau* **View on Instagram ↗** ← menimpa HTML | (kosong) |
| `.source-links a.btn-outline[href="/book/"]` | Book a project | (kosong) |
| `[data-detail-video]` kosong, `script.js:20` | Video preview is available through the project source. | (kosong) |
| galeri `img[alt]`, `script.js:20` | `{judul} — frame {n}` | (kosong) |
| `.footer-bottom a[href="/portfolio/"]` — **key `nav.portfolio`** | Back to portofolio → **(BUG: ditimpa runtime jadi "Portofolio")** | Portofolio |
| `document.title` runtime, `script.js:20` | `{judul PROJECTS} — MellogangVisuals` | (kosong) |

### Per halaman — HTML (fallback) vs `PROJECTS` (yang benar-benar tampil)

| URL | `<title>` HTML | meta description | h1 HTML | **judul PROJECTS** | eyebrow HTML | **kategori PROJECTS** | Year | Format |
|---|---|---|---|---|---|---|---|---|
| `/portfolio/indra-suci/` | Indra & Suci — MellogangVisuals | Indra & Suci — MellogangVisuals prewedding project. | Indra & Suci | Indra & Suci | Prewedding | Prewedding | 2024 | Film + Photo |
| `/portfolio/eka-nanda/` | Eka & Nanda — MellogangVisuals | Eka & Nanda — MellogangVisuals prewedding project. | Eka & Nanda | Eka & Nanda | Prewedding | Prewedding | 2024 | Film + Photo |
| `/portfolio/bukit-lestari/` | Puncak Bukit Lestari — MellogangVisuals | Puncak Bukit Lestari — MellogangVisuals destination film. | Puncak Bukit Lestari | Puncak Bukit Lestari | Commercial / Destination | **Destination / Villa** (beda) | 2023 | Promotional film |
| `/portfolio/pohen-camp/` | Pohen Hill Camp — MellogangVisuals | Pohen Hill Camp — MellogangVisuals destination film. | Pohen Hill Camp | Pohen Hill Camp | Destination / Glamping | Destination / Glamping | 2023 | Promotional film |
| `/portfolio/blooms-short/` | The Blooms Garden Bali — MellogangVisuals | The Blooms Garden Bali — MellogangVisuals commercial project. | The Blooms Garden Bali | **The Blooms Garden — Best Scenes** (beda) | Destination / Commercial | **Destination / Teaser** (beda) | 2023 | Commercial film |
| `/portfolio/blooms-promo/` | Blooms Garden Promo — MellogangVisuals | The Blooms Garden promotional video — MellogangVisuals. | Blooms Garden Promo | **The Blooms Garden — Promo** (beda) | Destination / Commercial | Destination / Commercial | 2023 | Promotional film |
| `/portfolio/mandiri-taspen/` | Mandiri Taspen — MellogangVisuals | PT Bank Mandiri Taspen — MellogangVisuals corporate project. | Mandiri Taspen | **PT Bank Mandiri Taspen** (beda) | Corporate | Corporate | 2023 | Corporate film |
| `/portfolio/coinvest-asia/` | CoinFest Asia 2022 — MellogangVisuals | CoinFest Asia 2022 — MellogangVisuals event coverage. | CoinFest Asia 2022 | CoinFest Asia 2022 | Event | Event | 2022 | Event film |
| `/portfolio/wedding-ceremony/` | Ceremony Stories — MellogangVisuals | Wedding ceremony stories — MellogangVisuals. | Ceremony stories | **Wedding Ceremony** (beda) | Wedding | Wedding | — | Photo + Gallery / Source Instagram |
| `/portfolio/cultural-event/` | Cultural Event — MellogangVisuals | Cultural event documentation — MellogangVisuals. | Cultural event | **Cultural Event** (beda kapital) | Event | Event | — | Photo + Gallery / Source Instagram |

Semua ID: **(kosong)**. Tidak satu pun string halaman detail masuk kamus i18n.

### Deskripsi proyek — dua versi berbeda

| Slug | Deskripsi di HTML (`p[data-detail-description]`, 2x per halaman) | **Deskripsi di `PROJECTS` (script.js) — yang tampil** |
|---|---|---|
| indra-suci | Prewedding session with an emphasis on natural movement, location and the relationship between the couple. | **Prewedding film of a couple in traditional Balinese attire, shot across tropical outdoor locations.** |
| eka-nanda | A location-led prewedding film with a relaxed pace and a clear focus on the couple. | **Prewedding film of a couple in traditional attire holding lanterns at dusk.** |
| bukit-lestari | Promotional film for a destination property, built around landscape, atmosphere and place. | **Promotional film for Puncak Lestari Camp in Bedugul, combining landscape, lodging and lifestyle footage.** |
| pohen-camp | Promotional film for Pohen Hill Camp in Bedugul, featuring the grounds, glamping tents and the experience. | sama |
| blooms-short | Short-form promotional edit for a destination in Bali. | **Vertical short-form teaser for The Blooms Garden, highlighting the outdoor space and evening atmosphere.** |
| blooms-promo | A promotional video focused on the experience, setting and visual identity of the destination. | **Promotional video for The Blooms Garden, covering the grounds, dining and outdoor activities.** |
| mandiri-taspen | Corporate learning centre documentation and promotional video production. | **Corporate facility film for Mantap Learning Center, covering the training rooms, amenities and lobby.** |
| coinvest-asia | Event coverage of CoinFest Asia 2022, capturing talks, booths and the crowd. | sama |
| wedding-ceremony | Ceremony coverage with a documentary approach to people, details and atmosphere. | sama |
| cultural-event | Environmental and documentary coverage for cultural events and public celebrations. | sama |

## 2.6 `/book/` (`frontend/book/index.html:1`)

| Lokasi / selector | Teks EN | Teks ID |
|---|---|---|
| `<title>` | Book Now — MellogangVisuals | (kosong) |
| `meta[name=description]` | Book a photo or video project with MellogangVisuals. | (kosong) |
| `.page-hero .eyebrow` | Book now | (kosong) |
| `h1.display` | **Start with`<br>`the details.** | (kosong) |
| `.page-hero p` | Share the basics of your project. The form will prepare a WhatsApp message so we can continue the conversation directly. | (kosong) |
| `.book-intro .eyebrow` | First step | (kosong) |
| `.book-intro h2.display` | **A clear brief makes a better production.** | (kosong) |
| `.book-intro p` | Choose the closest service, tell us your date and location, then add anything important. We will confirm availability and follow up with the next step. | (kosong) |
| `.book-preview img[alt]` | MellogangVisuals production | (kosong) |
| `.book-preview strong` | **Built around your project.** | (kosong) |
| `.book-preview span` | Wedding · Event · Brand · Destination | (kosong) |
| `#bookForm h2` | Project details | (kosong) |
| `label[for=bookPackage]` | Project type | (kosong) |
| `#bookPackage option` 1-6 | Wedding photo & video / Prewedding session / Event documentation / Corporate / brand production / Destination / property video / Other project | (kosong) |
| `label[for=bookName]` | Name | (kosong) |
| `#bookName[placeholder]` | Your name | (kosong) |
| `label[for=bookDate]` | Preferred date | (kosong) |
| `label[for=bookLocation]` | Location | (kosong) |
| `#bookLocation[placeholder]` | Location or city | (kosong) |
| `label[for=bookNotes]` | Project notes (optional) | (kosong) |
| `#bookNotes[placeholder]` | Tell us briefly what you need, the scale of the project or any reference.&nbsp;(spasi trailing) | (kosong) |
| `p.book-note` | **Your information is not submitted to a database from this static form.** WhatsApp opens with the details you provide. | (kosong) |
| `button[type=submit]` | Continue on WhatsApp ↗ | (kosong) |
| `#waLink` (hidden) | Continue | (kosong) |
| **template WA**, `script.js:22` | *(tidak ada versi EN)* | Hi MellogangVisuals, saya {nama}. Saya ingin menanyakan ketersediaan untuk {paket} pada tanggal {tgl}. Lokasi: {lokasi}. Catatan: {catatan} Mohon info detail dan langkah selanjutnya ya. |
| fallback label, `script.js:22` | photo/video production | (dipakai di kalimat ID) |
| placeholder WA, `script.js:22` | — | `[nama]` / `[tanggal acara]` / `[lokasi]` |

Catatan: `/book/` adalah satu-satunya halaman gen-B tanpa tombol "Book Now" di navbar; footer-nya kehilangan link "Book Now".
Copy WA **hanya Bahasa Indonesia**, tidak ikut language switcher — user EN tetap dapat pesan berbahasa Indonesia.

## 2.7 `/contact/` (`frontend/contact/index.html:1`)

| Lokasi / selector | Teks EN | Teks ID |
|---|---|---|
| `<title>` | About Us — MellogangVisuals (judul salah untuk halaman kontak) | (kosong) |
| `meta[http-equiv=refresh]` | 0;url=/about/ | — |
| `body > p` | Redirecting to [About Us](data-i18n=nav.about)… | Redirecting to Tentang Kami… (setengah diterjemahkan) |

## 2.8 `/packages/` (`frontend/packages/index.html:1`) — Generasi B (basi)

| Lokasi / selector | Teks EN | Teks ID |
|---|---|---|
| `<title>` | Packages — mellogang.wyna.dev | (kosong) |
| `meta[name=description]` | Packages by mellogang.wyna.dev — wedding, event, prewedding, company, ceremony, and graduation. | (kosong) |
| `.page-hero .eyebrow` | Packages | (kosong) |
| `h1` | Choose the story.`<br>`We'll shape the rest. | (kosong) |
| `.page-hero p` | Every project starts with a simple conversation. Pick the closest package below, then send us your name and date—we'll take it from there. | (kosong) |
| `.category-tabs a` x7 | All packages / Wedding / Event / Prewedding / Company / Ceremony / Graduation | (kosong) |
| kartu 1 `img[alt]` / `.package-category` / `h3` / `p` / `strong` / `.text-link` | Wedding / Wedding / Wedding stories / Full-day celebrations, intimate ceremonies, and everything in between. / Custom quote / Book this → | (kosong) |
| kartu 2 | Event / Event / Event coverage / A visual record of the room, the people, and the energy. / Custom quote / Book this → | (kosong) |
| kartu 3 | Prewedding / Prewedding / Prewedding sessions / A relaxed session with room for real connection and beautiful light. / Custom quote / Book this → | (kosong) |
| kartu 4 | Company / Company / Company & brand / Campaigns, profiles, launches, and content with a human point of view. / Custom quote / Book this → | (kosong) |
| kartu 5 | Ceremony / Ceremony / Ceremony coverage / The details, rituals, and atmosphere that make a ceremony yours. / Custom quote / Book this → | (kosong) |
| kartu 6 | Graduation / Graduation / Graduation memories / A milestone session for the people who made it through. / Custom quote / Book this → | (kosong) |
| `footer p` | Photo and film for days that deserve to be remembered properly. | (kosong) |

## 2.9 `/packages/{slug}/` x6 — Generasi B (basi)

Kerangka bersama: `.page-hero .eyebrow`, `h1` 2-baris, `p`, `a.button-primary` = **Book this package ↘**,
`a.button-secondary` = **Back to packages**, `.detail-card .eyebrow` = **What's included**, `h2`, `p`,
4x `<li>`, `a.button-primary` = **Send your date ↗**. Semua ID **(kosong)**.

| URL | `<title>` | meta description | eyebrow | h1 | intro p | h2 | p | li x4 | img alt |
|---|---|---|---|---|---|---|---|---|---|
| `/packages/wedding/` | Wedding Packages — mellogang.wyna.dev | Wedding packages by mellogang.wyna.dev. | Wedding packages | The day,`<br>`honestly. | From the nervous morning to the last dance, we make space for the big feeling and the small details. | **A thoughtful starting point.** | We'll tailor the final scope to your date, location, crew, and the way you want the finished story to feel. | Full-day photo & film coverage / Cinematic highlight film / A calm, experienced crew / Online delivery of final work | Wedding by Mellogang Visuals |
| `/packages/event/` | Event Packages — mellogang.wyna.dev | Event packages by mellogang.wyna.dev. | Event packages | Keep the room`<br>`alive. | Conferences, launches, concerts, and celebrations—coverage that catches what the schedule cannot. | Coverage with energy. | We keep up with the room, find the human moments, and deliver visuals that still feel alive after the lights go down. | Photo and video coverage / Highlight reel options / Fast social-ready selects / Flexible crew size | Event by Mellogang Visuals |
| `/packages/prewedding/` | Prewedding Packages — mellogang.wyna.dev | Prewedding packages by mellogang.wyna.dev. | Prewedding packages | A day before`<br>`the day. | Unhurried, personal, and built around the two of you—not a checklist of poses. | Room for real connection. | We create a relaxed visual session with enough direction to feel confident and enough space to feel like yourselves. | Location and mood planning / Photo-first storytelling / Optional motion teaser / Guidance without over-directing | Prewedding by Mellogang Visuals |
| `/packages/company/` | Company Packages — mellogang.wyna.dev | Company and brand packages by mellogang.wyna.dev. | Company packages | Make the work`<br>`visible. | Brand films, company profiles, and launch content with a human point of view. | **Considered visual communication.** | We translate what your team does into images and motion that feel clear, credible, and still human. | Creative treatment support / Interview and profile filming / Product or event coverage / Web and social deliverables | Company work by Mellogang Visuals |
| `/packages/ceremony/` | Ceremony Packages — mellogang.wyna.dev | Ceremony packages by mellogang.wyna.dev. | Ceremony packages | Hold on to`<br>`the feeling. | The rituals, details, people, and atmosphere that make a ceremony yours. | The atmosphere matters. | We stay alert to the details and quiet gestures that turn a formal ceremony into a memory you can return to. | Ceremony photo coverage / Atmosphere-focused film / Detail and family moments / Private online gallery | Ceremony by Mellogang Visuals |
| `/packages/graduation/` | Graduation Packages — mellogang.wyna.dev | Graduation packages by mellogang.wyna.dev. | Graduation packages | The next chapter`<br>`starts here. | A milestone session for the people who made it through and the people who helped them get there. | Celebrate the people. | Portraits, groups, family, and the proud little pauses that make the milestone feel real. | Portrait and group coverage / Campus or chosen location / Fast preview selects / Print-ready final gallery | Graduation by Mellogang Visuals |

Tombol CTA-nya mengarah ke `/?package=wedding#booking` dst. — anchor `#booking` **tidak ada** di
`frontend/index.html` (grep `id="booking"` → 0 hit).

## 2.10 `/workflow/` (`frontend/workflow/index.html:1`) — Generasi B (basi)

| Lokasi / selector | Teks EN | Teks ID |
|---|---|---|
| `<title>` | Workflow — mellogang.wyna.dev | (kosong) |
| `meta[name=description]` | Simple production workflow by mellogang.wyna.dev. | (kosong) |
| `.page-hero .eyebrow` | Workflow | (kosong) |
| `h1` | **Simple on purpose.**`<br>`Serious about the work. | (kosong) |
| `.page-hero p` | Booking should feel like a conversation, **not an enterprise procurement process**. Here's how we move from a first message to a finished story. | (kosong) |
| `.step-number` 01 / `h3` / `p` | 01 — First hello / Choose a package / Send your name, package, and date through the short form. WhatsApp opens with a ready-to-send message. | (kosong) |
| `.step-number` 02 / `h3` / `p` | 02 — Clear the details / We check the date / We reply with availability and ask only what matters: location, timing, crew, and what you want to remember. | (kosong) |
| `.step-number` 03 / `h3` / `p` | 03 — Lock it in / Confirm the plan / Once the scope is clear, we confirm the booking and send the next steps in one clean thread. | (kosong) |
| `.step-number` 04 / `h3` / `p` | 04 — The shoot / Stay in the moment / Our job is to direct **when needed**, disappear **when needed**, and make the whole day feel easy. | (kosong) |
| `.step-number` 05 / `h3` / `p` | 05 — The edit / **Shape the feeling** / We select, edit, color, and finish the work with attention to rhythm, light, and the story underneath. | (kosong) |
| `.step-number` 06 / `h3` / `p` | 06 — Delivered / Keep it forever / Your final work is delivered through a private online gallery or agreed delivery channel. | (kosong) |
| `.cta-band .eyebrow` | Ready to start? | (kosong) |
| `.cta-band h2` | Tell us your date. | (kosong) |
| `.cta-band p` | The first message can be as simple as a name, a package, and a date. | (kosong) |
| `.cta-band a.button-primary` | Open booking form ↗ | (kosong) |
| `footer p` | **Simple booking. Thoughtful production.** | (kosong) |

`/workflow/` **tidak punya** `link[rel=icon]`. Ejaan campur: `colour` di `/about/` vs `color` di `/workflow/`.

---

