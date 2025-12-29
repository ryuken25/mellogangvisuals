<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container">
  <h2 class="section__title">Ganti Bukti Pembayaran</h2>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="panel" style="margin-top:12px;">
    <div>Kode: <b><?= esc($row['kode_pemesanan'] ?? '-') ?></b></div>
    <div>Jenis: <b><?= esc($row['jenis_pembayaran'] ?? '-') ?></b></div>
    <div>Jumlah: <b>Rp <?= number_format((int)($row['jumlah_bayar'] ?? 0),0,',','.') ?></b></div>
    <div>Status: <span class="pill"><?= esc($row['status_verifikasi'] ?? '-') ?></span></div>

    <?php if (!empty($row['bukti_bayar'])): ?>
      <div style="margin-top:12px;">
        <div class="muted" style="margin-bottom:6px;">Bukti saat ini:</div>
        <img src="<?= site_url('pelanggan/pembayaran/file/'.$row['id_pembayaran']) ?>"
             style="max-width:100%;border-radius:12px;border:1px solid #e5e7eb;" alt="Bukti saat ini">
      </div>
    <?php endif; ?>

    <hr style="border:0;border-top:1px solid #e5e7eb;margin:14px 0;">

    <form method="post" enctype="multipart/form-data" action="<?= site_url('pelanggan/pembayaran/ganti/'.$row['id_pembayaran']) ?>">
      <?= csrf_field() ?>

      <div class="row">
        <div>
          <div class="label">Upload Bukti Baru (JPG/PNG max 2MB)</div>
          <input class="input" type="file" name="bukti_bayar" accept="image/png,image/jpeg" required>
        </div>
      </div>

      <button class="btnPrimary" type="submit">Simpan Bukti Baru</button>
      <a class="btnGhost" href="<?= site_url('pelanggan/pembayaran/riwayat/'.$row['id_pemesanan']) ?>">Batal</a>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
