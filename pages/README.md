# MellogangVisuals — Pages

Dokumentasi visual tiap halaman aplikasi dalam tiap state role.
Tiap halaman punya dua versi: **desktop (1440×900)** dan **mobile (390 iPhone 13)**.

Screenshot diambil pakai Playwright (`tools/screenshots/capture.js`) dengan login ke akun seeder
(admin / editor / pelanggan). Sumber data: hasil `php spark db:seed DatabaseSeeder`.

> **Cara lihat lokal**: buka file PNG langsung di GitHub / VSCode / image viewer.
> Sumber kode: lihat [DECISIONS.md](../DECISIONS.md) untuk keputusan teknis &
> [README.md](../README.md) untuk setup.

---

## Halaman Publik (tanpa login)

Folder: `screenshots/publik/`

### Beranda — `/`

Landing page. Logo, navbar, dan CTA ke katalog / portofolio. Tema gelap-sinematik
dengan sentuhan teal `#00F5B8` dari logo.

| Desktop | Mobile |
|---|---|
| ![home desktop](screenshots/publik/home-desktop.png) | ![home mobile](screenshots/publik/home-mobile.png) |

### Katalog — `/katalog`

Daftar paket foto/video. Harga, kategori, dan durasi. Pelanggan login dulu untuk memesan.

| Desktop | Mobile |
|---|---|
| ![katalog desktop](screenshots/publik/katalog-desktop.png) | ![katalog mobile](screenshots/publik/katalog-mobile.png) |

### Portofolio — `/portofolio`

Galeri hasil kerja. Sumber: tabel `portofolio` + cache `social_post` (item featured
dari YouTube / Instagram yang di-fetch admin via tombol).

| Desktop | Mobile |
|---|---|
| ![portofolio desktop](screenshots/publik/portofolio-desktop.png) | ![portofolio mobile](screenshots/publik/portofolio-mobile.png) |

### Kontak — `/kontak`

Info kontak brand (alamat, telepon, email, social).

| Desktop | Mobile |
|---|---|
| ![kontak desktop](screenshots/publik/kontak-desktop.png) | ![kontak mobile](screenshots/publik/kontak-mobile.png) |

### Status Pesanan (kosong) — `/status-pesanan`

Halaman untuk melacak pesanan dengan kode. Versi ini adalah form input kode
sebelum ada data pesanan yang dimuat.

| Desktop | Mobile |
|---|---|
| ![status desktop](screenshots/publik/status-desktop.png) | ![status mobile](screenshots/publik/status-mobile.png) |

### Login — `/login`

Form login. Ada tombol "Lanjut dengan Google" kalau `GOOGLE_CLIENT_ID` dikonfigurasi.
Pesan error ditampilkan jelas (termasuk pesan "Akun dikunci sementara…").

| Desktop | Mobile |
|---|---|
| ![login desktop](screenshots/publik/login-desktop.png) | ![login mobile](screenshots/publik/login-mobile.png) |

### Register — `/register`

Form pendaftaran. Setelah submit, otomatis kirim OTP ke email (cek Spam/Promosi).
Catatan anti dot-trick: dua varian titik di Gmail dianggap satu akun.

| Desktop | Mobile |
|---|---|
| ![register desktop](screenshots/publik/register-desktop.png) | ![register mobile](screenshots/publik/register-mobile.png) |

---

## Halaman Otentikasi (transisi)

Folder: `screenshots/akun/`

### Verifikasi OTP — `/auth/verify`

Halaman setelah register / login pertama. Input 6-digit kode OTP dari email,
atau klik link otomatis. Ada tombol "Kirim ulang kode" (throttled).

| Desktop | Mobile |
|---|---|
| ![verify-otp desktop](screenshots/akun/verify-otp-desktop.png) | ![verify-otp mobile](screenshots/akun/verify-otp-mobile.png) |

### Buka Kunci Akun — `/auth/unlock`

Halaman info kalau akun terkunci karena 4x salah sandi. Link pembuka dikirim
lewat email; halaman ini tinggal menjelaskan alurnya.

| Desktop | Mobile |
|---|---|
| ![unlock desktop](screenshots/akun/unlock-desktop.png) | ![unlock mobile](screenshots/akun/unlock-mobile.png) |

---

## Halaman Pelanggan (setelah login)

Folder: `screenshots/pelanggan/`

### Dashboard Pelanggan — `/pelanggan`

Daftar pesanan milik user yang login. Status pakai pill berwarna (kanonik
`App\Support\Status`). Tiap baris ada link ke form pembayaran / status detail.

| Desktop | Mobile |
|---|---|
| ![dashboard desktop](screenshots/pelanggan/dashboard-desktop.png) | ![dashboard mobile](screenshots/pelanggan/dashboard-mobile.png) |

### Buat Pemesanan — `/pelanggan/pemesanan/buat`

Form pilih paket + tanggal + jam + lokasi. Ada cek availability 2 fotografer
per hari (lazy-check 2 jam untuk pembatalan otomatis). Submit → order dibuat
dengan status `menunggu_pembayaran`.

| Desktop | Mobile |
|---|---|
| ![pemesanan-buat desktop](screenshots/pelanggan/pemesanan-buat-desktop.png) | ![pemesanan-buat mobile](screenshots/pelanggan/pemesanan-buat-mobile.png) |

---

## Halaman Admin (role: admin)

Folder: `screenshots/admin/`

### Dashboard Admin — `/admin`

Ringkasan: jumlah paket, portofolio, pesanan, pembayaran menunggu. Plus 5
pesanan terbaru.

| Desktop | Mobile |
|---|---|
| ![dashboard desktop](screenshots/admin/dashboard-desktop.png) | ![dashboard mobile](screenshots/admin/dashboard-mobile.png) |

### Pemesanan — `/admin/pemesanan`

Daftar semua pesanan. Filter by status + cari by kode. Status pill berwarna.

| Desktop | Mobile |
|---|---|
| ![pemesanan desktop](screenshots/admin/pemesanan-desktop.png) | ![pemesanan mobile](screenshots/admin/pemesanan-mobile.png) |

### Paket — `/admin/paket`

CRUD paket foto/video. Tambah, edit, hapus paket. Centang `is_active` untuk
tampilkan di katalog publik.

| Desktop | Mobile |
|---|---|
| ![paket desktop](screenshots/admin/paket-desktop.png) | ![paket mobile](screenshots/admin/paket-mobile.png) |

### Portofolio Admin — `/admin/portofolio`

CRUD item portofolio (manual, selain cache social_post). Thumbnail upload
disimpan di `public/uploads/portofolio/`.

| Desktop | Mobile |
|---|---|
| ![portofolio desktop](screenshots/admin/portofolio-desktop.png) | ![portofolio mobile](screenshots/admin/portofolio-mobile.png) |

### Pembayaran — `/admin/pembayaran`

Daftar pembayaran dari semua pelanggan. Filter by status verifikasi
(menunggu / valid / ditolak). Tombol "Verifikasi" untuk ACC / tolak.

| Desktop | Mobile |
|---|---|
| ![pembayaran desktop](screenshots/admin/pembayaran-desktop.png) | ![pembayaran mobile](screenshots/admin/pembayaran-mobile.png) |

### Jadwal Produksi — `/admin/jadwal`

Tabel jadwal. Admin pilih editor + tanggal shooting + editing. Status produksi
diizinkan hanya sampai `cut_to_cut` (sisanya wewenang editor).

| Desktop | Mobile |
|---|---|
| ![jadwal desktop](screenshots/admin/jadwal-desktop.png) | ![jadwal mobile](screenshots/admin/jadwal-mobile.png) |

### Laporan — `/admin/laporan`

Total pemasukan, total pengeluaran, laba bersih. Tabel valid + menunggu.
Export CSV per filter. Ada CRUD pengeluaran operasional.

| Desktop | Mobile |
|---|---|
| ![laporan desktop](screenshots/admin/laporan-desktop.png) | ![laporan mobile](screenshots/admin/laporan-mobile.png) |

### Users — `/admin/users`

Manajemen user. Edit role, hapus user. Admin tidak boleh menghapus dirinya sendiri.

| Desktop | Mobile |
|---|---|
| ![users desktop](screenshots/admin/users-desktop.png) | ![users mobile](screenshots/admin/users-mobile.png) |

### Social Cache — `/admin/social`

Halaman admin untuk trigger **Fetch YouTube & Instagram**. Tombol "Fetch"
panggil worker Node Playwright di background. Polling status setiap 3 detik.
Cache `social_post` ditampilkan di bawah. Tombol "☆/★ Feature" untuk kurasi.

| Desktop | Mobile |
|---|---|
| ![social desktop](screenshots/admin/social-desktop.png) | ![social mobile](screenshots/admin/social-mobile.png) |

---

## Halaman Editor (role: editor)

Folder: `screenshots/editor/`

### Dashboard Editor — `/editor`

Kartu A/B/C/D untuk jumlah tugas per tahap editing (cut-to-cut, finishing,
revisi, done+revisi_selesai). Modal pop-up muncul otomatis saat login (FCFS
berdasarkan `tanggal_selesai_editing`).

| Desktop | Mobile |
|---|---|
| ![dashboard desktop](screenshots/editor/dashboard-desktop.png) | ![dashboard mobile](screenshots/editor/dashboard-mobile.png) |

### Daftar Tugas — `/editor/tugas`

Semua tugas yang di-assign ke editor. Filter by status. Klik baris → detail
tugas dengan form update progres + upload file preview + field `link_hasil`
untuk Google Drive.

| Desktop | Mobile |
|---|---|
| ![tugas desktop](screenshots/editor/tugas-desktop.png) | ![tugas mobile](screenshots/editor/tugas-mobile.png) |

---

## Catatan tentang Screenshot

- Viewport: **desktop 1440×900** dan **mobile 390 (iPhone 13)**.
- Browser: Chromium headless, lewat Playwright.
- Akun login: pakai akun seeder (`admin@mellogang.test`, `editor1@mellogang.test`, `pengguna1@mellogang.test`, password `123123`).
- Data dinamis (nama, jumlah, total) bisa beda kalau seeder diubah.

Untuk re-generate:
```bash
php spark serve --port 8080 &
cd tests/e2e
E2E_BASE_URL=http://localhost:8080 node capture.js
```

Output ditulis ke `pages/screenshots/<area>/<nama>-<viewport>.png`.
