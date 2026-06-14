# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**MellogangVisuals Ordering & Production Tracking System** — A CodeIgniter 4 web app for managing photo/video service orders, payment verification, and editor production tracking with role-based access (admin, editor, pelanggan/customer). v2 added full i18n (EN+ID cookie), Google OAuth, OTP register, dark-cinematic UI, a Google Drive deliverable flow, and a Playwright social-media fetcher (YouTube + Instagram).

## Commands

```bash
# Install dependencies
composer install

# Start dev server (http://localhost:8080)
php spark serve --port 8080

# Database
php spark migrate                # run all migrations
php spark migrate:rollback       # rollback
php spark db:seed DatabaseSeeder  # re-seed (idempotent truncate + insert)

# Tests
composer test                    # PHPUnit
./vendor/bin/phpunit --filter EmailNormalizerTest   # single test

# Re-screenshot all pages (requires server running + Playwright browsers)
cd tests/e2e && E2E_BASE_URL=http://localhost:8080 node capture.js

# Fetch real photos for portofolio items (Picsum, ~30s)
php tools/fetch_portofolio_images.php

# Build pages PDF from screenshots
php tools/screenshots/build_pdf.php

# Remotion video (1080p showcase reel)
cd video && npm install
cd video && npx remotion render ShowcaseReel out/mellogangvisuals-reel.mp4
```

## Architecture

**Framework**: CodeIgniter 4.7.x (PHP 8.2/8.3), MySQL/MariaDB (InnoDB, utf8mb4_unicode_ci), Bootstrap 5 + jQuery. Controllers use CodeIgniter's Query Builder directly — no Eloquent/ORM. **All status values are stored as canonical snake_case** (e.g. `pra_produksi`, `menunggu_pembayaran`) — no more `LOWER(col) = '...'` queries (would defeat indexes).

### Role-Based Controllers

| Role | Namespace | Routes prefix |
|------|-----------|---------------|
| Admin | `App\Controllers\Admin\` | `/admin` |
| Editor | `App\Controllers\Editor\` | `/editor` |
| Pelanggan | `App\Controllers\Pelanggan\` | `/pelanggan` |
| Public | `App\Controllers\Public\` | `/` |

Auth is centralized in `App\Controllers\AuthController` (login, register, verify-OTP, Google OAuth, lockout/unlock, throttled). `App\Filters\RoleFilter` enforces role per route (note: the filter reads `role` from the session, not `user_role`).

### Layouts

- `app/Views/layout/main.php` — public + pelanggan pages (sticky topbar, language switcher, hero sections).
- `app/Views/layout/admin.php` — admin shell (navbar + `adminShell` grid with sidebar + content). All admin pages must `extend('layout/admin')` and put content in a `adminContent` section.
- `app/Views/layout/editor.php` — editor pages.
- All layouts share the same dark-cinematic theme via `public/assets/css/app.css`.

### Key Libraries / Support Classes

- `App\Support\Status` — canonical status constants (`Status::ORDER_MENUNGGU_PEMBAYARAN`, `Status::PROD_PRA_PRODUKSI`, etc.), label/color helpers, and the production state machine (`Status::canProdTransition($from, $to)`).
- `App\Support\I18n` — cookie-based locale (`mllang`, 1 year), default `en`. Dictionary inline (see `loadDictionaries()`). Helper `t('key', $params)` is autoloaded via `app/Helpers/i18n_helper.php` (registered in composer `files` autoload). Route `GET /lang/{en,id}` switches the cookie.
- `App\Libraries\EmailNormalizer` — Gmail/Googlemail dot/plus-alias handling. Result is stored in `user.email_canonical` (UNIQUE). Login + register + Google callback all dedup on canonical.
- `App\Libraries\Mailer` — wraps CI4 Email. Renders branded HTML emails from `app/Views/emails/*.php`. Never throws to user flow (try/catch + flash warning).
- `App\Libraries\GoogleAuth` — wrapper for `league/oauth2-google`. CSRF state is stored in session under `oauth_state`.
- `App\Models\AuthTokenModel` — issues / verifies / marks-used for OTP, unlock, and email-verify tokens (column `token_hash` is sha256 of the random token).
- `App\Models\SocialFetchJobModel` and `social_post` / `social_fetch_job` tables — cache results from the Playwright fetcher.

### Migrations (newest last)

`app/Database/Migrations/2026-06-14-10000*` — the v2 set. Important ones:
- `100000_CreateDetailPemesananTable` — repairs the missing `detail_pemesanan` table referenced by `DetailPemesananModel`.
- `100001_AddPortofolioThumbnail` — adds `portofolio.thumbnail` (local filename column).
- `100002_V2AddAuthAndDeliverableColumns` — user auth columns (`email_canonical`, `email_verified_at`, `google_id`, `auth_provider`, `avatar_url`, `failed_login_attempts`, `locked_until`, `last_login_at`) + `jadwal_produksi.link_hasil*`.
- `100003_V2CreateAuthTokenAndSocialTables` — `auth_token`, `social_post`, `social_fetch_job`.
- `100004_V2AddCanonicalAndHelperIndexes` — `UNIQUE(email_canonical)`, `UNIQUE(google_id)`, plus query-helping indexes on `pemesanan`, `pembayaran`, `jadwal_produksi`, `paket`.
- `100005_NormalizeStatusValues` — backfills all old status strings to canonical snake_case.
- `100006_WidenMoneyColumnsToBigint` — `paket.harga`, `pemesanan.total_biaya`, `pembayaran.jumlah_bayar`, `pengeluaran_operasional.nominal` → `BIGINT(20) UNSIGNED`.

### Critical Tables & Key Columns

- `user` — has `email_canonical` (UNIQUE) used for dedup; `google_id` (UNIQUE); `auth_provider` (`password`|`google`); `email_verified_at` (gate); `failed_login_attempts` + `locked_until`.
- `pemesanan` — `kode_pemesanan` (MLG+YYMMDD+4rand), `tanggal_acara`, `status_pemesanan` (canonical), `total_biaya` (BIGINT).
- `jadwal_produksi` — `status_produksi` (canonical), `tanggal_shooting`, `link_hasil`, `link_hasil_hash` (sha256 of last emailed link), `link_hasil_terkirim_at`.
- `pembayaran` — `jenis_pembayaran` (DP/pelunasan), `status_verifikasi` (`menunggu`|`valid`|`ditolak`).
- `auth_token` — `token_hash` (sha256), `otp_code`, `expires_at`, `used_at`. **Never** store raw tokens.
- `social_post` — cache: `platform` + `external_id` UNIQUE. Public portfolio reads from here.
- `social_fetch_job` — lifecycle: `queued` → `running` → `done`|`failed`.

### Order lifecycle

```
pelanggan pesan → upload bukti bayar → admin verifikasi →
admin assign editor + jadwal → editor update progress →
revisi (optional) → serah terima → selesai
```

Editor status flow (use `Status::canProdTransition`):
```
pra_produksi → shooting → cut_to_cut → finishing → done
                                                    ↓
                                          revisi → revisi_selesai
```

### Availability check (2-fotografer/hari)

Both `Pelanggan/PemesananController::availability()` and `Admin/JadwalProduksiController::availability()` run a lazy 2-hour auto-cancel before counting. The 2-hour rule:
```sql
UPDATE pemesanan SET status_pemesanan='batal'
WHERE status_pemesanan='menunggu_pembayaran'
  AND tanggal_pemesanan <= DATE_SUB(NOW(), INTERVAL 2 HOUR)
```
Count bookings from `pemesanan` (not `jadwal_produksi`) excluding `batal`/`ditolak`. Capacity = 2 per date.

### Editor dashboard "Done" sync

`Editor/DashboardController::countD` sums both `done` and `revisi_selesai` (not just `done`). FCFS sort: `tanggal_selesai_editing ASC`. Pop-up modal auto-shows on dashboard load when `session()->getFlashdata('show_tugas_popup')` is true (set by `AuthController::loginSuccess` for editor role).

### Google Drive deliverable flow

- Admin/editor sets `jadwal_produksi.link_hasil` (validated as Drive URL).
- App does NOT proxy the file — only stores and forwards the link. **No file storage cost.**
- Email "Hasil siap" only sends when stage hits `finishing` / `done` / `revisi_selesai` **and** `link_hasil` is non-empty. **Idempotent** via `sha256(link)` stored in `link_hasil_hash` — same hash means skip.
- Customer-facing "Unduh Hasil" button shows when order is `serah_terima_hasil` or `selesai`.

## Demo Accounts

All passwords: `123123`
- Admin: `admin@mellogang.test`
- Editor: `editor1@mellogang.test`, `editor@mellogang.test`
- Customer: `pengguna1@mellogang.test`, `pengguna2@mellogang.test`, `pengguna3@mellogang.test`

## File Upload Paths

- Payment proofs: `writable/uploads/pembayaran/`
- Avatars: `writable/uploads/avatars/`
- Portfolio: `public/uploads/portofolio/` (real photos at 16:9/4:3/1:1, downloaded by `tools/fetch_portofolio_images.php`)
- IG session cookies: `writable/secure/ig_state.json` (gitignored)

## Theme & Brand

- Primary: `#00F5B8` (teal/mint from logo). Background `#0A0E0D`.
- Display: Space Grotesk. Body: Inter. All tokens in `public/assets/css/app.css`.
- No CDN fonts — woff2 self-hosted in `public/assets/fonts/`.
- No random emoji in user-facing copy.

## More context

- Long-form architecture decisions: `DECISIONS.md` (technical decisions + hosting notes).
- Setup, hosting, and VPS/Playwright notes: `README.md`.
- Earlier pending revisions: `implementation_plan.md`, `popupplan.md` (historical).
- Video showcase built with Remotion in `video/` (separate `package.json`).
- Per-page screenshots in `pages/screenshots/<area>/`; combined PDF in `pages/MellogangVisuals-Pages.pdf`.
- Playwright e2e specs in `tests/e2e/`.
