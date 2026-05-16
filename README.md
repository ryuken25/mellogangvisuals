# MellogangVisuals — Ordering & Production Tracking System

Web-based information system for managing photo/video service orders:
catalog & portfolio, ordering, payment proof upload, admin verification,
scheduling, and editing-progress tracking.

Built on **PHP 8.1+** and **CodeIgniter 4**, backed by **MySQL/MariaDB**.

---

## Roles

| Role          | Capabilities                                                                 |
| ------------- | ---------------------------------------------------------------------------- |
| **Admin**     | Manage packages & portfolio, verify payments, assign editor/schedule, report |
| **Editor**    | Update production progress on assigned orders                                |
| **Pelanggan** | Create orders, upload payment proofs, track status & progress                |

## Public pages (no login required)

- Home: `/`
- Katalog: `/katalog`
- Portofolio: `/portofolio`
- Kontak: `/kontak`
- Status Pesanan: `/status-pesanan` (`GET /status-pesanan?kode=KODE`)
- Invoice: `GET /invoice/{kode_pemesanan}`

## Core features

- Authentication: register / login / logout
- Role-based access control (admin / editor / pelanggan)
- Pemesanan: create order, pick package, fill event details
- Pembayaran: upload DP / pelunasan proof, admin verifies
- Penjadwalan: admin assigns editor + production dates
- Production progress: editor updates `cut-to-cut → finishing → done`
- Laporan (admin)

---

## Database

Migrations and seeders live in:

- `app/Database/Migrations`
- `app/Database/Seeds`

### Tables

`user`, `paket`, `pemesanan`, `pembayaran`, `jadwal_produksi`,
`portofolio`, `pengeluaran_operasional`.

Foreign keys and secondary indexes are added by
`2025-12-15-150000_AddIndexesAndForeignKeys.php` after every base table
is created. The constraint migration is a no-op on SQLite (used by the
PHPUnit suite) because SQLite cannot `ALTER TABLE ADD CONSTRAINT`.

## File storage

Runtime uploads:

- Payment proofs → `writable/uploads/pembayaran`
- User avatars   → `writable/uploads/avatars`
- Portfolio media → `public/uploads/portofolio`

Keep uploaded files out of git (already covered by `.gitignore`); track
empty directories with `.gitkeep`.

---

## Local setup (host PHP)

```bash
composer install
cp .env.example .env             # edit DB credentials to taste
php spark migrate --all          # create tables + add FKs/indexes
php spark db:seed DatabaseSeeder # optional sample data
php spark serve --port 8080      # http://localhost:8080
```

Or, with the included `Makefile`:

```bash
make install
make migrate
make seed
make serve
```

## Local setup (Docker)

A `Dockerfile` and `docker-compose.yml` are included. The stack runs
the app behind Apache, plus a MariaDB 11 instance; phpMyAdmin is
available behind the optional `tools` profile.

```bash
make docker-build      # build the app image
make docker-up         # start app + db
make docker-migrate    # run migrations
make docker-seed       # seed sample data
# App:        http://localhost:8080
# phpMyAdmin: docker compose --profile tools up -d phpmyadmin -> :8081
make docker-down
```

The app container reads database credentials from `docker-compose.yml`
environment variables — no `.env` is required for the containerised
flow.

---

## Tests

```bash
composer install
vendor/bin/phpunit
# or
make test
```

The default PHPUnit profile uses an **in-memory SQLite** database, so
no external services are required to run the suite.

## Continuous integration

`.github/workflows/ci.yml` runs on every push and pull request against
`main`, `master`, and `develop`. It:

1. Spins up MariaDB 11 as a service container.
2. Installs Composer dependencies for PHP 8.1 / 8.2 / 8.3 (matrix).
3. Runs `php spark migrate --all` against MariaDB to validate the
   migrations *and* the foreign-key/index migration end-to-end.
4. Runs PHPUnit (SQLite, in-memory).

## Demo accounts (seeder)

Password for every seeded account: `123123`

- Admin    — `admin@mellogang.test`
- Editor   — `editor1@mellogang.test`, `editor@mellogang.test`
- Pelanggan — `pengguna1@mellogang.test` (also `pengguna2`, `pengguna3`)

---

## License

Educational / portfolio project. Add a proper license (e.g. MIT) before
publishing publicly.
