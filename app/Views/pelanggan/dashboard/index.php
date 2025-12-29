<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container">

  <h2 class="section__title">Dashboard Pelanggan</h2>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px;">
    <a class="btnPrimary" href="<?= site_url('pelanggan/pemesanan/buat') ?>">+ Buat Pemesanan</a>

    <!-- status pesanan = halaman tracking (public) -->
    <a class="btnGhost" href="<?= site_url('status-pesanan') ?>">Lihat Status Pesanan</a>

    <!-- book now = ke kontak -->
    <a class="btnGhost" href="<?= site_url('kontak') ?>">Kontak / Book now!</a>
  </div>

  <div class="panel" style="margin-top:12px;">
    <h3 class="section__title" style="margin:0 0 10px 0;">Pesanan Saya</h3>

    <?php if (empty($orders)): ?>
      <div class="alert ok">Kamu belum punya pesanan.</div>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>Kode</th>
            <th>Paket</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Total</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <?php
              $kode = (string) ($o['kode_pemesanan'] ?? '');
              $kodeUrl = site_url('status-pesanan?kode=' . urlencode($kode));
            ?>
            <tr>
              <td>
                <a class="link" href="<?= $kodeUrl ?>">
                  <?= esc($kode) ?>
                </a>
              </td>

              <td>
                <a class="link" href="<?= $kodeUrl ?>">
                  <?= esc($o['nama_paket'] ?? '-') ?>
                </a>
              </td>

              <td><?= esc($o['tanggal_acara'] ?? '-') ?></td>

              <td>
                <span class="pill"><?= esc($o['status_pemesanan'] ?? '-') ?></span>
              </td>

              <td>
                Rp <?= number_format((int)($o['total_biaya'] ?? 0), 0, ',', '.') ?>
              </td>

              <td>
                <a class="link" href="<?= site_url('pelanggan/pembayaran/upload/' . (int)$o['id_pemesanan']) ?>">
                  Upload Pembayaran
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

  </div>
</div>

<?= $this->endSection() ?>
