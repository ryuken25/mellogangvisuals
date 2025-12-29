<?= $this->extend('layout/admin') ?>

<?= $this->section('adminContent') ?>

<h2 class="section__title">Dashboard Admin</h2>
<p class="auth-sub">Ringkasan data & aktivitas terbaru.</p>

<div class="adminCards">
  <div class="adminCard"><div class="adminCard__label">Total Paket</div><div class="adminCard__value"><?= (int)($countPaket ?? 0) ?></div></div>
  <div class="adminCard"><div class="adminCard__label">Total Portofolio</div><div class="adminCard__value"><?= (int)($countPorto ?? 0) ?></div></div>
  <div class="adminCard"><div class="adminCard__label">Total Pemesanan</div><div class="adminCard__value"><?= (int)($countOrder ?? 0) ?></div></div>
  <div class="adminCard"><div class="adminCard__label">Pembayaran Menunggu</div><div class="adminCard__value"><?= (int)($pendingPay ?? 0) ?></div></div>
</div>

<div class="panel" style="margin-top:16px;">
  <h3 class="section__title" style="margin-bottom:10px;">Pemesanan Terbaru</h3>

  <?php if (empty($orders)): ?>
    <div class="alert ok">Belum ada pemesanan.</div>
  <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th>Kode</th><th>Pelanggan</th><th>Paket</th><th>Status</th><th>Tanggal</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><?= esc($o['kode_pemesanan']) ?></td>
          <td><?= esc($o['nama_lengkap'] ?? '-') ?></td>
          <td><?= esc($o['nama_paket'] ?? '-') ?></td>
          <td><span class="pill"><?= esc($o['status_pemesanan'] ?? '-') ?></span></td>
          <td><?= esc($o['tanggal_pemesanan'] ?? '-') ?></td>
          <td><a class="link" href="<?= site_url('admin/pemesanan/'.$o['id_pemesanan']) ?>">Detail</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
