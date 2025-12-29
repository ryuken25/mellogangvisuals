<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container">
  <h2 class="section__title">Riwayat Pembayaran</h2>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <div class="panel" style="margin-top:12px;">
    <div>Kode: <b><?= esc($order['kode_pemesanan']) ?></b></div>
    <div>Total: <b>Rp <?= number_format((int)$total,0,',','.') ?></b></div>
    <div>Valid: <b>Rp <?= number_format((int)$valid,0,',','.') ?></b></div>
    <div>Sisa: <b>Rp <?= number_format((int)$sisa,0,',','.') ?></b></div>
  </div>

  <?php if ($pendingCount > 0): ?>
    <div class="alert error" style="margin-top:12px;">
      Ada pembayaran yang masih <b>Menunggu</b>. Kamu bisa <b>Ganti Bukti</b> kalau salah upload.
    </div>
  <?php endif; ?>

  <div style="height:12px"></div>

  <?php if (empty($rows)): ?>
    <div class="panel">Belum ada pembayaran.</div>
  <?php else: ?>
    <?php foreach ($rows as $r): ?>
      <?php $st = strtolower(trim((string)($r['status_verifikasi'] ?? ''))); ?>

      <div class="panel" style="margin-bottom:12px;">
        <div><b>Tanggal:</b> <?= esc($r['tanggal_bayar'] ?? '-') ?></div>
        <div><b>Jenis:</b> <?= esc($r['jenis_pembayaran']) ?></div>
        <div><b>Metode:</b> <?= esc($r['metode_pembayaran']) ?></div>
        <div><b>Jumlah:</b> Rp <?= number_format((int)$r['jumlah_bayar'],0,',','.') ?></div>
        <div><b>Status:</b> <span class="pill"><?= esc($r['status_verifikasi']) ?></span></div>

        <?php if (!empty($r['catatan_verifikasi'])): ?>
          <div><b>Catatan:</b> <?= esc($r['catatan_verifikasi']) ?></div>
        <?php endif; ?>

        <?php if (!empty($r['bukti_bayar'])): ?>
          <div style="margin-top:10px;">
            <div class="muted" style="margin-bottom:6px;">Bukti:</div>
            <img src="<?= site_url('pelanggan/pembayaran/file/'.$r['id_pembayaran']) ?>"
                 alt="Bukti bayar" style="max-width:100%;border-radius:12px;border:1px solid #e5e7eb;">
          </div>
        <?php endif; ?>

        <?php if (in_array($st, ['menunggu','ditolak'], true)): ?>
          <div style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;">
            <a class="btnPrimary" href="<?= site_url('pelanggan/pembayaran/ganti/'.$r['id_pembayaran']) ?>">
              Ganti Bukti
            </a>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <?php if ($sisa <= 0): ?>
      <span class="pill">Lunas</span>
    <?php elseif ($pendingCount > 0): ?>
      <a class="btnGhost" href="<?= site_url('status-pesanan?kode='.$order['kode_pemesanan']) ?>">Kembali</a>
    <?php else: ?>
      <?php $label = ($dpValid <= 0) ? 'Upload DP (50%)' : 'Bayar Pelunasan'; ?>
      <a class="btnPrimary" href="<?= site_url('pelanggan/pembayaran/upload/'.$order['id_pemesanan']) ?>"><?= esc($label) ?></a>
      <a class="btnGhost" href="<?= site_url('status-pesanan?kode='.$order['kode_pemesanan']) ?>">Kembali</a>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
