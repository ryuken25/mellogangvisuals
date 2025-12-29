<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container">
  <div class="panel">
    <h2 class="section__title">Paket</h2>
    <p class="auth-sub">Pilih paket layanan yang sesuai kebutuhan kamu.</p>

    <?php if (empty($paket)): ?>
      <div class="alert ok">Belum ada paket aktif.</div>
    <?php else: ?>
      <div class="listGrid3">
        <?php foreach ($paket as $p): ?>
          <div class="itemCard">
            <div class="itemCard__title"><?= esc($p['nama_paket']) ?></div>
            <div class="itemCard__meta">
              <span class="pill"><?= esc($p['kategori']) ?></span>
              <span class="muted"><?= esc($p['durasi_jam']) ?> jam</span>
            </div>
            <div class="itemCard__price">Rp <?= number_format((int)$p['harga'], 0, ',', '.') ?></div>
            <div class="itemCard__desc"><?= esc($p['deskripsi'] ?? '') ?></div>
            <div class="itemCard__actions">
              <?php
                $paketId   = $p['id'] ?? $p['id_paket']; // sesuaikan field id di tabel paket kamu
                $targetUrl = route_to('pelanggan-pesan', $paketId);

                // sesuaikan key session kamu kalau beda:
                $isLogin = (bool) session()->get('logged_in');
                $role    = session()->get('role');
                ?>

                <div class="itemCard__actions">
                  <?php
                    $paketId = $p['id_paket']; // pastikan ini sesuai field kamu
                    $target  = route_to('pelanggan-pesan', $paketId);

                    $isLogin = (bool) session()->get('logged_in'); // sesuaikan kalau key-mu beda
                    $role    = session()->get('role');
                  ?>

                  <?php if ($isLogin && $role === 'pelanggan'): ?>
                    <a class="btnPrimary" href="<?= $target ?>">Pesan</a>
                  <?php else: ?>
                    <a class="btnPrimary" href="<?= site_url('login?redirect=' . urlencode($target)) ?>">
                      Login untuk Pesan
                    </a>
                  <?php endif; ?>
                </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
