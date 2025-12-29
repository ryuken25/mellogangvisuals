<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
$loggedIn = (bool) session()->get('logged_in');
$role     = session()->get('role');

// tombol kanan di hero (Login / Ke Dashboard / Pesanan Anda)
$secondaryUrl   = '/login';
$secondaryLabel = 'Login';

if ($loggedIn) {
  if ($role === 'admin') {
    $secondaryUrl   = '/admin';
    $secondaryLabel = 'Ke Dashboard';
  } elseif ($role === 'editor') {
    $secondaryUrl   = '/editor';
    $secondaryLabel = 'Ke Dashboard';
  } else { // pelanggan
    $secondaryUrl   = '/status-pesanan';
    $secondaryLabel = 'Pesanan Anda';
  }
}
?>

<div class="container">

  <section class="hero">
    <div class="hero__left">
      <h1 class="hero__title">MellogangVisuals</h1>
      <p class="hero__sub">
        Layanan photo & video untuk wedding, corporate, event, dan kebutuhan lainnya.
        Lihat paket, portofolio, dan lakukan pemesanan dengan mudah.
      </p>

      <div class="hero__cta">
        <a class="btnPrimary" href="<?= site_url('/katalog') ?>">Lihat Paket</a>
        <a class="btnGhost" href="<?= site_url($secondaryUrl) ?>">
          <?= esc($secondaryLabel) ?>
        </a>
      </div>

      <div class="hero__note">
        Tip: setelah login, kamu bisa buat pemesanan dan upload bukti pembayaran.
      </div>
    </div>

    <div class="hero__right">
      <div class="heroCard">
        <div class="heroCard__label">Cek Status Pesanan</div>
        <form class="statusForm" method="get" action="<?= site_url('/status-pesanan') ?>">
          <input class="input" name="kode" placeholder="Masukkan kode pemesanan (contoh: MLG001)">
          <button class="btnPrimary" type="submit">Cek</button>
        </form>
        <small class="muted">Kamu bisa cek status menggunakan kode pemesanan.</small>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="section__head">
      <h2 class="section__title">Paket Populer</h2>
      <a class="link" href="<?= site_url('/katalog') ?>">Lihat semua paket →</a>
    </div>

    <?php if (empty($paket)): ?>
      <div class="panel">Belum ada paket aktif. Admin bisa tambah paket dari dashboard.</div>
    <?php else: ?>
      <div class="grid3">
        <?php foreach ($paket as $p): ?>
          <div class="cardMini">
            <div class="cardMini__title"><?= esc($p['nama_paket']) ?></div>

            <div class="cardMini__meta">
              <span class="pill"><?= esc($p['kategori']) ?></span>
              <span class="muted"><?= esc($p['durasi_jam']) ?> jam</span>
            </div>

            <div class="cardMini__price">
              Rp <?= number_format((int)$p['harga'], 0, ',', '.') ?>
            </div>

            <div class="cardMini__desc">
              <?= esc($p['deskripsi'] ?? '') ?>
            </div>

            <div class="cardMini__actions">

              <?php
              $paketId = (int) ($p['id_paket'] ?? 0);
              $pesanTarget = 'pelanggan/pemesanan/buat/' . $paketId;

              if ($loggedIn && $role === 'pelanggan') {
                // pelanggan login -> langsung ke form paket terpilih
                $pesanUrl   = site_url($pesanTarget);
                $pesanLabel = 'Pesan';
              } elseif (!$loggedIn) {
                // belum login -> login dulu lalu balik ke form paket terpilih
                $pesanUrl   = site_url('login?redirect=' . urlencode(site_url($pesanTarget)));
                $pesanLabel = 'Login untuk Pesan';
              } else {
                // admin/editor -> ke dashboard masing-masing
                $pesanUrl   = site_url($secondaryUrl);
                $pesanLabel = 'Ke Dashboard';
              }
              ?>

              <a class="btnPrimary" href="<?= $pesanUrl ?>">
                <?= esc($pesanLabel) ?>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="section">
    <div class="section__head">
      <h2 class="section__title">Portofolio Terbaru</h2>
      <a class="link" href="<?= site_url('/portofolio') ?>">Lihat semua portofolio →</a>
    </div>

    <?php if (empty($portofolio)): ?>
      <div class="panel">Belum ada portofolio. Admin bisa tambah portofolio dari dashboard.</div>
    <?php else: ?>
      <div class="grid4">
        <?php foreach ($portofolio as $po): ?>
          <a class="portoCard" href="<?= esc($po['url_media'] ?? '#') ?>" target="_blank" rel="noopener">
            <div class="portoCard__title"><?= esc($po['judul']) ?></div>
            <div class="portoCard__meta">
              <span class="pill"><?= esc($po['kategori']) ?></span>
              <?php if (!empty($po['tanggal_publikasi'])): ?>
                <span class="muted"><?= esc($po['tanggal_publikasi']) ?></span>
              <?php endif; ?>
            </div>
            <div class="portoCard__desc"><?= esc($po['deskripsi'] ?? '') ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

</div>

<?= $this->endSection() ?>
