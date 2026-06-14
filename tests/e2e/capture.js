/**
 * Screenshot setiap halaman di tiap state role.
 *
 * Output:
 *   ./pages/screenshots/<area>/<nama>-desktop.png
 *   ./pages/screenshots/<area>/<nama>-mobile.png
 *
 * Usage:
 *   E2E_BASE_URL=http://localhost:8080 node tools/screenshots/capture.js
 *
 * Akun default (lihat README.md):
 *   admin:    admin@mellogang.test   / 123123
 *   editor:   editor1@mellogang.test / 123123
 *   customer: pengguna1@mellogang.test / 123123
 */

const { chromium, devices } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE = process.env.E2E_BASE_URL || 'http://localhost:8080';
// __dirname will be tests/e2e/ when run from there. Output: ./pages/screenshots/
const OUT  = path.resolve(__dirname, '..', '..', 'pages', 'screenshots');

const PAGES = {
  publik: [
    { name: 'home',         url: '/',                  auth: null },
    { name: 'katalog',      url: '/katalog',           auth: null },
    { name: 'portofolio',   url: '/portofolio',        auth: null },
    { name: 'kontak',       url: '/kontak',            auth: null },
    { name: 'status',       url: '/status-pesanan',    auth: null },
    { name: 'status-kode',  url: '/status-pesanan?kode=MLG', auth: null },
    { name: 'login',        url: '/login',             auth: null },
    { name: 'register',     url: '/register',          auth: null },
  ],
  akun: [
    { name: 'verify-otp',   url: '/auth/verify?email=demo@mellogang.test', auth: null },
    { name: 'unlock',       url: '/auth/unlock',       auth: null },
  ],
  pelanggan: [
    { name: 'dashboard',    url: '/pelanggan',         auth: 'customer' },
    { name: 'pemesanan-buat', url: '/pelanggan/pemesanan/buat', auth: 'customer' },
  ],
  admin: [
    { name: 'dashboard',    url: '/admin',             auth: 'admin' },
    { name: 'pemesanan',    url: '/admin/pemesanan',   auth: 'admin' },
    { name: 'paket',        url: '/admin/paket',       auth: 'admin' },
    { name: 'portofolio',   url: '/admin/portofolio',  auth: 'admin' },
    { name: 'pembayaran',   url: '/admin/pembayaran',  auth: 'admin' },
    { name: 'jadwal',       url: '/admin/jadwal',      auth: 'admin' },
    { name: 'laporan',      url: '/admin/laporan',     auth: 'admin' },
    { name: 'users',        url: '/admin/users',       auth: 'admin' },
    { name: 'social',       url: '/admin/social',      auth: 'admin' },
  ],
  editor: [
    { name: 'dashboard',    url: '/editor',            auth: 'editor' },
    { name: 'tugas',        url: '/editor/tugas',      auth: 'editor' },
  ],
};

const CREDS = {
  admin:    { email: 'admin@mellogang.test',   pass: '123123' },
  editor:   { email: 'editor1@mellogang.test', pass: '123123' },
  customer: { email: 'pengguna1@mellogang.test', pass: '123123' },
};

async function ensureDir(dir) {
  fs.mkdirSync(dir, { recursive: true });
}

async function login(page, who) {
  const c = CREDS[who];
  await page.goto(BASE + '/login');
  await page.fill('input[name="email"]', c.email);
  await page.fill('input[name="password"]', c.pass);
  await page.click('button[type="submit"]');
  // Tunggu redirect ke area masing-masing
  await page.waitForLoadState('networkidle', { timeout: 10_000 }).catch(() => {});
}

async function logout(context) {
  await context.clearCookies();
}

async function shoot(page, area, name, viewportLabel) {
  const file = path.join(OUT, area, `${name}-${viewportLabel}.png`);
  await ensureDir(path.dirname(file));
  await page.screenshot({ path: file, fullPage: true });
  console.log('  →', file.replace(process.cwd() + path.sep, ''));
}

(async () => {
  console.log('Capturing screenshots →', OUT);
  ensureDir(OUT);

  const browser = await chromium.launch({ headless: true });

  // Desktop: 1920x1080 (16:9 — "kalo di admin tuh 16:9")
  const desktopCtx = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
  const desktop = await desktopCtx.newPage();

  // Mobile: 360x640 (9:16 strict)
  const mobileCtx = await browser.newContext({ viewport: { width: 360, height: 640 }, deviceScaleFactor: 3, isMobile: true, hasTouch: true });
  const mobile = await mobileCtx.newPage();

  for (const area of Object.keys(PAGES)) {
    console.log('\n==', area, '==');
    // Reset login state antar area
    await logout(desktopCtx);
    await logout(mobileCtx);

    for (const p of PAGES[area]) {
      // Login kalau perlu (desktop)
      if (p.auth) {
        await login(desktop, p.auth);
        await login(mobile, p.auth);
      }
      try {
        await desktop.goto(BASE + p.url, { waitUntil: 'domcontentloaded', timeout: 15_000 });
        // Tunggu semua image load (max 8 detik)
        await desktop.waitForLoadState('networkidle', { timeout: 8_000 }).catch(() => {});
        await desktop.evaluate(async () => {
          const imgs = Array.from(document.images || []);
          await Promise.all(imgs.map(img =>
            img.complete ? Promise.resolve() : new Promise(r => {
              img.addEventListener('load', r, { once: true });
              img.addEventListener('error', r, { once: true });
              setTimeout(r, 5000);
            })
          ));
        }).catch(() => {});
        await shoot(desktop, area, p.name, 'desktop');

        await mobile.goto(BASE + p.url, { waitUntil: 'domcontentloaded', timeout: 15_000 });
        await mobile.waitForLoadState('networkidle', { timeout: 8_000 }).catch(() => {});
        await mobile.evaluate(async () => {
          const imgs = Array.from(document.images || []);
          await Promise.all(imgs.map(img =>
            img.complete ? Promise.resolve() : new Promise(r => {
              img.addEventListener('load', r, { once: true });
              img.addEventListener('error', r, { once: true });
              setTimeout(r, 5000);
            })
          ));
        }).catch(() => {});
        await shoot(mobile, area, p.name, 'mobile');
      } catch (e) {
        console.error('  !', p.url, e.message);
      }
    }
  }

  await browser.close();
  console.log('\nDone. Output:', OUT);
})();
