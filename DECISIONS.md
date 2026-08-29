# DECISIONS — MellogangVisuals v2

Dokumen ini mencatat keputusan teknis yang diambil selama overhaul.
Versi: 2026-06-14 (mulai).

## 1. Stack tetap CodeIgniter 4

- Tidak ditulis ulang ke Laravel/Node. Alasan: (a) PHP/CI4 murah & universal
  untuk shared hosting, (b) logika bisnis (state machine revisi, snapshot
  pembayaran, RBAC) sudah ada dan kompleks, (c) target "1x jalan, no bug"
  tidak realistis kalau rewrite dari nol.
- Upgrade target: **CodeIgniter 4.7.x stabil** (saat ini `^4.0` di
  composer.json, akan di-pin ke `^4.7`).
- Runtime: **PHP 8.2** (sudah tersedia di environment: PHP 8.2.12).
- DB: MySQL/MariaDB, InnoDB, utf8mb4_unicode_ci.

## 2. Hosting: VPS untuk social fetch

- App PHP sendiri ringan & jalan di shared hosting.
- Tapi Playwright (Chromium) untuk auto-fetch YouTube/IG butuh Node + RAM.
- Catatan jujur ke Atar: fitur social fetch → **VPS kecil (~1GB RAM)**
  dengan Node + Chromium terinstall. Sisanya tetap ringan.

## 3. Skema disatukan

- Skema lama (yang sudah ada + yang modified via MIG baru) **tidak di-drop**.
  Semua perubahan skema lewat migration baru **idempotent** (`tableExists` /
  `fieldExists` guard).
- Backfill: `email_verified_at` di-set ke `created_at` untuk user lama
  (akun seeder) supaya tidak terkunci verifikasi.

## 4. Status: normalisasi kanonik (lowercase, snake_case)

Nilai kanonik yang dipakai seluruh aplikasi via `App\Support\Status`:

### status_pemesanan
- `menunggu_pembayaran`
- `menunggu_pelunasan`
- `menunggu_verifikasi`
- `lunas`
- `revisi_pelanggan`
- `revisi_diproses`
- `serah_terima_hasil`
- `selesai`
- `batal`
- `ditolak`

### status_produksi
- `pra_produksi`
- `shooting`
- `cut_to_cut`
- `finishing`
- `done`
- `revisi`
- `revisi_selesai`

### status_verifikasi
- `menunggu`
- `valid`
- `ditolak`

Mapping data-fix dijalankan di migration `NormalizeStatusValues` untuk
mengkonversi varian lama (mis. `'Pra produksi'` → `'pra_produksi'`,
`'Menunggu pembayaran'` → `'menunggu_pembayaran'`). Setelah itu, **tidak
ada lagi query `LOWER(col) = '...'`** — index kepakai.

## 5. Auth overhaul

- **Email normalizer**: `App\Libraries\EmailNormalizer`. Aturan:
  - trim + strtolower
  - `googlemail.com` → `gmail.com`
  - Untuk `gmail.com`: hapus semua `.` di local, potong di `+` pertama
  - Untuk domain lain: potong di `+` pertama, **titik tetap**
  - Simpan hasil di `user.email_canonical` (UNIQUE) — lookup/dedup pakai
    kolom ini, kolom `email` tetap menyimpan apa yang diketik user.
- **Register OTP**: form baru dengan field sesuai. Saat submit: buat user
  dengan `email_verified_at = NULL`, generate OTP 6 digit + token acak,
  simpan hash di `auth_token(type=verify_email, expires=+15m)`. Halaman
  verifikasi menerima OTP atau klik link. Login diblokir sampai verified.
- **Google OAuth**: pakai `league/oauth2-google`. Route
  `/auth/google/redirect` & `/auth/google/callback`. Lookup by
  `google_id` atau `email_canonical`. Auto-link atau auto-create dengan
  `auth_provider='google'`, `email_verified_at=now`.
- **Lockout**: tiap gagal `failed_login_attempts++`. Saat >= 4 → set
  `locked_until = now + 30m` + generate token `unlock` + kirim email
  berisi link unlock. Login ditolak selama lock. CI4 Throttler dipasang
  di endpoint login/register/OTP-resend/unlock (basis IP).
- **CSRF**: diaktifkan di semua POST.

## 6. SMTP

- Pakai CI4 Email service bawaan. Konfigurasi via `.env`. TIDAK pakai
  PHPMailer (overkill).
- `App\Libraries\Mailer` sebagai wrapper. Try/catch semua, tidak pernah
  melempar ke flow user (cukup log + flash warning).
- 4 jenis email:
  1. Verifikasi akun (OTP + link)
  2. Buka kunci akun (link unlock)
  3. Invoice (HTML + PDF attachment via dompdf)
  4. Hasil siap (idempotent, hanya jika ada `link_hasil`)

## 7. Deliverable: Google Drive link

- Editor/admin menempel URL Drive di field `jadwal_produksi.link_hasil`.
- Validasi host `drive.google.com` / `docs.google.com`. App **tidak
  mem-proxy file** — cuma menyimpan & meneruskan link.
- Pemicu email "hasil siap" di `Editor/TugasController::update` saat
  status produksi bergerak ke `finishing` / `done` / `revisi_selesai`
  **dan** ada `link_hasil`. **Idempotent**: hash link, kalau sama
  dengan `link_hasil_hash` yang tersimpan → **skip kirim**.
- Halaman status publik + dashboard pelanggan tampilkan tombol "Unduh
  Hasil" yang rapi saat order di `serah_terima_hasil` / `selesai`.

## 8. Social fetcher (Playwright)

- Worker Node ada di `tools/social-fetcher/`. Dipanggil via
  `proc_open`/`exec` (background) dari endpoint
  `POST /admin/social/fetch` (admin-only, CSRF).
- Dashboard polling `GET /admin/social/status?job=<id>` tiap ~3 detik.
- Halaman portofolio publik baca dari cache `social_post` — **tidak**
  scraping saat visitor buka.
- Mode `--fixture` untuk test deterministik (mengembalikan JSON kalengan).
- IG: pakai `storageState` dari `writable/secure/ig_state.json` (gitignored).
- Rate/selector IG bisa berubah; perlu maintenance. Catat jujur.

## 9. UI redesign

- Tema gelap-sinematik, primary teal `#00F5B8` (sampling dari logo).
- Token CSS di satu file `public/assets/css/app.css`. Hapus blok
  `<style>` inline yang tersebar.
- Font self-host (woff2) di `public/assets/fonts/`:
  Heading: Space Grotesk, Body: Inter.
- Favicon dari logo. Open Graph meta di layout utama.
- Mobile-first, AA kontras, dukung `prefers-reduced-motion`.
- Restyle markup; **nama route/URL tidak diubah**.

## 10. Security headers

- `SecureHeaders` CI4 diaktifkan + tambahan di App\Filters\SecurityHeaders:
  CSP (`default-src 'self'` + img untuk `i.ytimg.com`, IG CDN, Google
  avatar), `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options:
  nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`.
- Session: `httponly=true`, `samesite=Lax`, `secure` di production,
  `regenerate` saat login.

## 11. Testing

- **PHPUnit** (unit/integration): EmailNormalizer (tabel kasus),
  Status state machine, idempotensi email hasil, UNIQUE
  `email_canonical`.
- **Playwright e2e**: register+OTP, lockout+unlock, dot-trick,
  order flow, editor progress with Drive link, social fetch
  (fixture mode).
- **Capture email test**: CI4 Email → file-spool
  (`writable/email-spool/`) di env testing. Assert isi spool.

## 12. ./pages (screenshots + docs)

- Tool Playwright di `tools/screenshots/` login pakai akun seeder
  (admin/editor/pelanggan), screenshot setiap halaman di desktop
  1440 + mobile 390.
- Simpan ke `./pages/screenshots/<area>/<nama>.png` (area:
  publik, akun, pelanggan, admin, editor).
- Generate `./pages/README.md` (natural, bahasa Indonesia) +
  `MellogangVisuals-Pages.pdf`.

## 13. .env.example & .gitignore

- `.env.example` lengkap + komentar.
- `.gitignore` include `.env`, `ig_state.json`, `writable/secure/`,
  `writable/email-spool/`, `writable/social-debug/`, `node_modules`,
  `test-results/`, `playwright-report/`.

## 14. Catatan jujur / keterbatasan

- Social fetcher IG = area abu-abu ToS. Untuk produksi serius
  pertimbangkan Graph API resmi (perlu app review Meta).
- Email "cek Spam/Promosi" ditampilkan di setiap email & halaman
  verifikasi karena SMTP gratisan (Gmail/Yahoo) sering masuk Spam di
  first-send.
- Backup rutin tetap penting; revisi pakai `git` jadi catatan
  perubahan bisa ditelusuri.

## 15. Tahapan eksekusi (urutan)

1. Inventory (✓) + DECISIONS.md (✓) — file ini
2. `composer update` ke CI 4.7.x + install OAuth/dompdf
3. Migrations (drift fix, FK, index, normalisasi status, uang, kolom
   auth, tabel baru, link_hasil)
4. EmailNormalizer + unit test
5. Auth (OTP, Google, lockout, throttle, CSRF)
6. Mailer + 4 email + invoice PDF
7. Drive flow + idempotent email
8. UI redesign
9. Social fetcher worker + endpoint + UI
10. Hardening header/session
11. PHPUnit + Playwright
12. ./pages screenshot + README
13. .env.example + .gitignore + README + push
14. Final verify + cetak MANUAL STEPS

---

# Overhaul route + copy (2026-08-29)

Konteks: audit lengkap ada di `FINDINGS.md`, inventaris string di
`FINDINGS-strings.md`.

## 16. Situs statis dan aplikasi CI4 tetap terpisah

Diverifikasi tiga cara: nol marker situs statis di `app/Views`, nol file di luar
`frontend/` yang menyebut path `frontend/`, dan `builds` ternyata skrip
stabilitas CodeIgniter, bukan generator situs. Keduanya juga punya sistem i18n
yang tidak berbagi apa pun (localStorage vs cookie httponly, default ID vs EN).

Konsekuensi yang dipakai sepanjang pekerjaan ini: mengganti seluruh copy situs
statis tidak menyentuh aplikasi order sama sekali. Menyatukan keduanya sengaja
**tidak** dikerjakan. Kalau nanti disatukan, dua hal harus diputuskan lebih
dulu: tabrakan `/portfolio/` (statis) vs `/portofolio` (CI4), dan bagaimana
pilihan bahasa dibawa antar keduanya.

## 17. Generator statis, bukan HTML tangan

Sebelumnya 23 file HTML ditulis tangan tanpa build step. Nav desktop, nav
mobile, dan footer disalin terpisah ke tiap file, jadi mengubah satu link nav
berarti menyentuh sampai 3 blok di 22 file. Ketiganya sudah terbukti menyimpang:
10 halaman detail tidak punya blok footer sama sekali, 8 halaman kehilangan link
Home dan Book, `/workflow/` menghilangkan link Workflow dari mobile menu-nya
sendiri.

Target megaprompt ("satu partial nav dan footer dipakai semua halaman", "satu
sumber kebenaran untuk semua string") **tidak bisa dipenuhi** tanpa build step.
Karena itu `site/build.mjs` diperkenalkan.

Yang dipilih dan alasannya:

- **Output di-commit ke `frontend/`, bukan di-build di Vercel.** Vercel tetap
  menyajikan file statis tanpa build command, jadi tidak ada perubahan
  konfigurasi deploy dan tidak ada langkah baru yang bisa gagal saat produksi.
  Harganya: file hasil ikut masuk git. Diterima, dan setiap file diberi banner
  "jangan sunting" di baris pertama.
- **Tanpa dependensi.** Node murni, nol `node_modules`, nol `package.json` di
  root. Templating-nya `{{key}}` sederhana; loop dirender di JS lalu dimasukkan
  sebagai nilai mentah.
- **Build gagal keras (exit 1)** kalau ada key yatim, placeholder tak
  tergantikan, link internal mati, atau aset hilang.

Perintah:

```bash
node site/build.mjs            # render ke frontend/
node site/build.mjs --check    # validasi saja, tidak menulis
node --test site/test.mjs      # 17 tes
```

## 18. Strategi URL bahasa: ID di root, EN di /en/ (opsi B)

Bahasa default situs adalah Indonesia. Sebelumnya toggle-nya swap DOM lewat
`localStorage`, akibatnya versi Indonesia tidak bisa dishare, tidak bisa
diindeks, tidak punya `hreflang`, dan setiap halaman berkedip Inggris dulu
sebelum berganti ke Indonesia.

Tiga opsi dipertimbangkan. Dipilih **B** (ID di `/`, EN di `/en/`), bukan A
(`/id/` + `/en/`), dengan alasan spesifik repo ini:

1. Tanpa build step, opsi A berarti memelihara 46 file HTML dengan tangan; B
   berarti 23. Setelah generator ada, selisih itu mengecil, tapi B tetap memberi
   URL terpendek ke bahasa mayoritas klien.
2. Konten lama 99% Inggris keras, jadi membangun `/en/` dari markup yang sudah
   ada lebih murah daripada menerjemahkan dua arah sekaligus.

Konsekuensi: `hreflang` resiprokal plus `x-default` ke versi Indonesia wajib ada
di setiap halaman. Pengalih bahasa jadi tautan asli ke URL padanannya, bukan
tombol JS.

Judul halaman detail portofolio sengaja dibiarkan sama di kedua bahasa untuk
proyek yang namanya nama diri ("Indra & Suci", "CoinFest Asia 2022"). Membedakan
paksa akan mengarang. `hreflang` dan `canonical` yang menangani duplikasinya,
dan meta description tetap unik di 28 halaman.

## 19. Aturan slug portofolio: <subjek>-<tahun>

Sebelumnya slug bercampur tiga sumbu: nama klien (`indra-suci`), nama tempat
(`bukit-lestari`), dan tipe proyek generik (`cultural-event`).

Aturan yang ditetapkan: `<subjek>-<tahun>`, dengan `<subjek>` nama klien,
pasangan, atau venue dalam kebab-case, tidak pernah tipe deliverable atau
kategori. Kalau satu subjek punya dua deliverable di tahun yang sama, dan hanya
saat itu, tambahkan token format.

Sekaligus memperbaiki typo yang sudah masuk URL: `coinvest-asia` seharusnya
**CoinFest** Asia.

**Pengecualian yang disengaja:** `wedding-ceremony` dan `cultural-event`
dibiarkan apa adanya. Keduanya bukan proyek bertanggal, melainkan kumpulan foto
dari arsip Instagram (`video:''`, satu gambar galeri). Memberi mereka tahun
berarti mengarang. Keduanya perlu diganti nama proyek yang sebenarnya atau
dikeluarkan dari namespace proyek begitu klien memberi datanya.

Semua slug lama dapat redirect 308, dihasilkan otomatis dari `oldSlug` di
`site/data/projects.json` supaya tidak mungkin melenceng.

## 20. /packages/** dan /workflow/ dihapus, bukan diperbaiki

Delapan halaman ini sisa generasi desain lama: memakai 12 class CSS yang sudah
dihapus dari `frontend/styles.css` sehingga tayang tanpa style, nol tautan masuk
dari nav utama, CTA menunjuk anchor `/#booking` yang tidak ada, dan merek di
`<title>` masih `mellogang.wyna.dev` yang lama.

Keputusan bisnis dari pemilik: hapus, redirect 308 ke `/book/`. Isi paketnya
tidak dipindahkan ke halaman lain karena harga selalu "Custom quote" dan alur
penawaran sekarang lewat WhatsApp.

`/contact/` yang cuma stub meta-refresh juga dihapus, diganti redirect 308 asli
ke `/about/`.

## 21. Pohon statis duplikat di root repo dihapus

13 file (`index.html`, `styles.css`, `script.js`, `about/`, `contact/`,
`packages/`, `portfolio/`, `workflow/`, `assets/`, `vercel.json`) yang isinya
sudah menyimpang dari padanan `frontend/`-nya. Diverifikasi tidak di-deploy ke
mana pun: Vercel project `mellogang` memakai `rootDirectory=frontend`, dan dari
29 project di akun hanya satu yang terhubung ke repo ini.

Bahaya nyatanya bukan ukuran repo, tapi salah setel Root Directory akan
diam-diam men-deploy situs lama yang tidak punya `/book/` dan tidak punya i18n,
lalu **kelihatan seperti situs yang bekerja** alih-alih gagal dengan berisik.

## 22. GSAP dihapus, bukan di-vendor

`/assets/vendor/gsap.min.js` dan `ScrollTrigger.min.js` dirujuk 14 halaman,
direktorinya tidak pernah ada di repo. 28 request 404 per crawl. Animasinya
memang tidak pernah aktif karena `gsapMotion()` punya guard `!window.gsap`.

Tag-nya dihapus: nol perubahan visual, 28 request 404 hilang. Menghidupkan
animasi adalah keputusan desain, dan desain akan diganti terpisah.

Catatan yang belum dibereskan: `frontend/styles.css` masih memuat font dari
`fonts.googleapis.com`, bertentangan dengan aturan di CLAUDE.md ("No CDN fonts").
Dibiarkan karena menyentuh tipografi berarti menyentuh desain.

## 23. Domain kanonik: mellogang.wyna.dev

`mellogang.wyna.dev` didaftarkan ke project Vercel (DNS-nya sudah lama menunjuk
`cname.vercel-dns.com`, yang kurang cuma pendaftaran domain sehingga TLS tidak
pernah diterbitkan). `mellogangvisual.wyna.dev` (ejaan lama, tanpa "s") diubah
jadi redirect 308 ke host kanonik supaya keduanya tidak bersaing di indeks.

`creds.txt` berisi kredensial hidup dan ditambahkan ke `.gitignore`. File itu
belum pernah ter-commit, jadi tidak ada riwayat yang perlu di-purge.

---

# Restyle ke design Claude + Work dari scrape (2026-08-29)

## 24. Copy deck desain menang atas copy sebelumnya

Copy yang ditulis pagi ini (§ commit `588a595`) diganti copy deck dari project
Claude Design `e7b4942f`. Bukan karena yang lama buruk, tapi karena deck itu
memang ditulis untuk desain ini dan sebagian jelas lebih baik.

Contoh yang paling menunjukkan bedanya, alt text proyek Indra & Suci:

- lama: "Pasangan berbusana adat Bali di lokasi terbuka"
- deck: "Pasangan berbusana adat Bali berjalan bergandengan di atas rumput menuju
  pantai berpohon kelapa"

Deck juga membawa konten yang sebelumnya tidak ada: tiga poin pengiriman (foto 3
minggu, film 6 minggu, Google Drive, satu putaran revisi), section tim, dan slate
rasio di bawah setiap gambar.

Skema key dan generator tetap milik repo; yang diambil isinya.

## 25. Font di-self-host, walau desain memakai CDN

File desain memuat Archivo dan IBM Plex dari `fonts.googleapis.com`. CLAUDE.md
repo ini melarangnya ("No CDN fonts — woff2 self-hosted"), dan aturan repo menang.

Delapan woff2 diunduh ke `frontend/assets/fonts/` (~310 KB total), subset dibatasi
`latin` + `latin-ext` saja. Archivo ternyata font variabel dua sumbu (`wght`
100–900, `wdth` 62–125), jadi satu file melayani seluruh bobot display. Sumbu
lebar dipanggil lewat `font-stretch: 125%`, bukan `font-variation-settings`,
supaya cocok dengan rentang yang dideklarasikan di `@font-face`.

Blok `@font-face` hidup di antara penanda `@font-face-start` / `@font-face-end` di
`frontend/styles.css`. Regenerasi: jalankan ulang pengambilannya, lalu tempel isi
`site/data/fontface.css` di antara kedua penanda itu.

## 26. Toggle bahasa desain tidak disalin

Desain menaruh kedua bahasa di DOM sekaligus dan menyembunyikan salah satunya
dengan `display` (`{{ iEn }}` / `{{ iId }}`). Itu keharusan artboard kanvas: satu
artboard harus bisa memperagakan dua bahasa.

Situs tetap memakai URL per bahasa (§18). Menyalin pendekatan kanvas berarti
menggandakan seluruh teks di setiap halaman, mengembalikan flash bahasa yang baru
saja dihapus, dan membuat `hreflang` tidak ada artinya.

## 27. Work diambil dari kanal YouTube, bukan daftar yang ditulis tangan

`tools/scrape-youtube.mjs` membungkus `yt-dlp --flat-playlist -J` atas
`youtube.com/@mellogangvisuals/videos` dan menulis `site/data/youtube.json`.
Kanalnya berisi 21 video; situs sebelumnya hanya menampilkan 8 di antaranya.

Skrip ini sengaja terpisah dari `tools/social-fetcher/worker.js`. Worker itu milik
aplikasi CodeIgniter: ia butuh `--job=<id>`, melapor ke API PHP, dan menulis ke
tabel `social_post`. Situs statis butuh JSON di disk, bukan baris database.

Thumbnail diambil dari `i.ytimg.com/vi/<id>/maxresdefault.jpg` dengan fallback
`hqdefault.jpg`, disimpan sebagai `frontend/assets/video/frames/yt-<id>.jpg`.

**Alt text untuk video baru ditulis setelah gambarnya benar-benar dilihat**, bukan
diturunkan dari judul. Judul YouTube tidak memberi tahu apa yang ada di frame, dan
alt yang ditebak dari judul adalah alt yang berbohong.

## 28. Instagram belum bisa di-scrape

Extractor Instagram di yt-dlp ditandai *broken* dan gagal. Endpoint publik
`web_profile_info` mengembalikan `null` tanpa sesi login. Halaman profil membalas
200 tapi isinya dinding login.

Repo ini sudah mengantisipasinya: `worker.js` membaca `IG_STORAGE_STATE` dari
`writable/secure/ig_state.json`, yang dihasilkan `tools/social-fetcher/login-ig.js`.
Direktori itu tidak ada, jadi tidak ada sesi.

Scrape IG butuh login, dan memasukkan kredensial bukan sesuatu yang dikerjakan
agen. Pemilik repo menjalankan sendiri sekali:

    node tools/social-fetcher/login-ig.js

Sampai itu ada, entri foto memakai gambar IG yang sudah tersimpan di
`frontend/assets/brand/instagram/`.

## 29. Foto tim belum bisa ditarik dari project desain

`DesignSync get_file` memotong isi di 256 KiB. Keempat PNG potret tim melewati
batas itu, dan hasil unduhannya tidak utuh (tidak ada penanda `IEND`).

Daripada memasang gambar orang yang salah atau menggagalkan build, `site/build.mjs`
memeriksa keberadaan file dan **melewati section tim** selama belum ada, sambil
mencetak peringatan. Datanya (`site/data/team.json`), markup, dan efek lensanya
sudah siap; section muncul sendiri begitu keempat file diletakkan di
`frontend/assets/team/`.
