const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const OUT = path.resolve(__dirname, '..', '..', 'pages', 'screenshots', 'audit');
fs.mkdirSync(OUT, { recursive: true });

const CREDS = {
  admin:    { email: 'admin@mellogang.test',   pass: '123123' },
  editor:   { email: 'editor1@mellogang.test', pass: '123123' },
  customer: { email: 'pengguna1@mellogang.test', pass: '123123' },
};

const ROUTES = [
  { url: '/',                 name: 'home',         role: null },
  { url: '/katalog',          name: 'katalog',      role: null },
  { url: '/portofolio',       name: 'portofolio',   role: null },
  { url: '/kontak',           name: 'kontak',       role: null },
  { url: '/status-pesanan',   name: 'status',       role: null },
  { url: '/login',            name: 'login',        role: null },
  { url: '/register',         name: 'register',     role: null },
  { url: '/admin',            name: 'admin-dash',   role: 'admin' },
  { url: '/admin/paket',      name: 'admin-paket',  role: 'admin' },
  { url: '/admin/pembayaran', name: 'admin-bayar',  role: 'admin' },
  { url: '/admin/users',      name: 'admin-users',  role: 'admin' },
  { url: '/pelanggan',        name: 'plg-dash',     role: 'customer' },
  { url: '/editor',           name: 'ed-dash',      role: 'editor' },
];

(async () => {
  const browser = await chromium.launch();
  const ctx = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
  const page = await ctx.newPage();
  let currentRole = null;

  for (const r of ROUTES) {
    if (r.role !== currentRole) {
      // Switch session
      await page.context().clearCookies();
      await page.evaluate(() => {}).catch(() => {});
      if (r.role) {
        await page.goto('http://localhost:8080/login');
        await page.fill('input[name="email"]', CREDS[r.role].email);
        await page.fill('input[name="password"]', CREDS[r.role].pass);
        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle', { timeout: 8000 }).catch(() => {});
      } else {
        // public, just logout via direct URL
        await page.goto('http://localhost:8080/logout').catch(() => {});
        await page.goto('http://localhost:8080/');
      }
      currentRole = r.role;
    }
    await page.goto('http://localhost:8080' + r.url, { waitUntil: 'networkidle' });
    await page.evaluate(async () => {
      const imgs = Array.from(document.images || []);
      await Promise.all(imgs.map(img => img.complete ? Promise.resolve() : new Promise(r => {
        img.addEventListener('load', r, { once: true });
        img.addEventListener('error', r, { once: true });
        setTimeout(r, 3000);
      })));
    }).catch(() => {});
    const h1 = await page.textContent('h1, h2').catch(() => 'NO HEADING');
    const finalUrl = page.url();
    const ok = !finalUrl.includes('/login') || r.role === null;
    const status = ok ? '✓' : '✗ (redirected to login)';
    console.log(`${status} ${r.name.padEnd(15)} ${r.url.padEnd(25)} → ${finalUrl.replace('http://localhost:8080', '')}  |  H: ${h1?.slice(0, 60)}`);
    await page.screenshot({ path: path.join(OUT, r.name + '.png'), fullPage: false });
  }
  await browser.close();
})();
