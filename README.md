# MellogangVisuals Ordering & Production Tracking System

Web-based information system for managing photo/video service orders: catalog & portfolio, ordering, payment proof upload, admin verification, scheduling, and editing progress tracking.

## Roles
- **Admin**: manage packages & portfolio, verify payments, assign editor & schedule production, reporting
- **Editor**: update production progress and handle assigned tasks
- **Pelanggan**: create orders, upload payment proofs, track status & progress

## Public Pages (No Login)
- Home: `/`
- Katalog: `/katalog`
- Portofolio: `/portofolio`
- Kontak: `/kontak`
- Status Pesanan: `/status-pesanan`
  - `GET /status-pesanan?kode=KODE` → order detail + progress tracking
- Invoice: `GET /invoice/{kode_pemesanan}`

## Core Features
- Authentication: register / login / logout
- Role-based access control (admin/editor/pelanggan)
- Pemesanan: create order + select package + event details
- Pembayaran: upload payment proof (DP / pelunasan), admin verification
- Penjadwalan: admin assigns editor + production schedule
- Progres editing: editor updates status produksi (`cut-to-cut → finishing → done`)
- Laporan (admin)

## Tech Stack
- PHP 8.1+
- CodeIgniter 4
- MySQL/MariaDB
- Composer

## Database
This project includes **migrations** and **seeders** under:
- `app/Database/Migrations`
- `app/Database/Seeds`

### Common tables
- `user`
- `paket`
- `pemesanan`
- `pembayaran`
- `jadwal_produksi`
- `portofolio`
- (optional) `pengeluaran_operasional`

## File Storage
Runtime uploads are stored under:
- Payment proofs: `writable/uploads/pembayaran`
- User avatars: `writable/uploads/avatars`
- Portfolio images: `public/uploads/portofolio`

> Recommendation: keep uploaded files out of git (use `.gitignore`) and keep empty directories tracked using `.gitkeep`.

---

## Local Setup

### 1) Install dependencies
```bash
composer install
```

### 2) Configure environment
Create `.env` (recommended from `.env.example`) and set:
- `app.baseURL`
- `database.default.hostname`
- `database.default.database`
- `database.default.username`
- `database.default.password`

### 3) Run migrations
```bash
php spark migrate
```

### 4) Seed sample data (optional)
```bash
php spark db:seed DatabaseSeeder
```

### 5) Run the app
Option A (built-in server):
```bash
php spark serve -- port 8080
```
Open: `http://localhost:8080`


## Demo Accounts (Seeder)
Password: `123123`

- Admin: `admin@mellogang.test`
- Editor: `editor1@mellogang.test` / `editor@mellogang.test`
- Pelanggan: `pengguna1@mellogang.test` (also `pengguna2`, `pengguna3`)

---

## License
Educational / portfolio project. Add a proper license (e.g., MIT) if you plan to open-source it publicly.