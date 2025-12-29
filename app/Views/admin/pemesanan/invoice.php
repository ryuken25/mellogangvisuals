<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Invoice') ?></title>
  <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
  <style>
    @media print {
      .noPrint { display:none !important; }
      body { background:#fff; }
      .panel { box-shadow:none !important; border:1px solid #e5e7eb; }
    }
  </style>
</head>
<body>

<div class="container" style="max-width:900px;margin:16px auto;">
  <div class="panel">
    <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;">
      <div>
        <h2 style="margin:0;">INVOICE</h2>
        <div class="muted">No: <b><?= esc($invoiceNo ?? '-') ?></b></div>
        <div class="muted">Tanggal: <?= esc(date('d/m/Y H:i')) ?></div>
      </div>

      <div style="text-align:right;">
        <div style="font-weight:700;">MellogangVisuals</div>
        <div class="muted">Photo & Video Service</div>
      </div>
    </div>

    <hr style="border:0;border-top:1px solid #e5e7eb;margin:14px 0;">

    <div class="adminTwoCol">
      <div>
        <h3 class="section__title" style="margin-bottom:8px;">Ditagihkan kepada</h3>
        <div><b>Nama:</b> <?= esc($order['nama_lengkap'] ?? '-') ?></div>
        <div><b>Email:</b> <?= esc($order['email'] ?? '-') ?></div>
        <div><b>Telepon:</b> <?= esc($order['no_telepon'] ?? '-') ?></div>
      </div>
      <div>
        <h3 class="section__title" style="margin-bottom:8px;">Info Pesanan</h3>
        <div><b>Kode:</b> <?= esc($order['kode_pemesanan'] ?? '-') ?></div>
        <div><b>Paket:</b> <?= esc($order['nama_paket'] ?? '-') ?> (<?= esc($order['kategori'] ?? '-') ?>)</div>
        <div><b>Tanggal Acara:</b> <?= esc($order['tanggal_acara'] ?? '-') ?></div>
        <div><b>Lokasi:</b> <?= esc($order['lokasi_acara'] ?? '-') ?></div>
      </div>
    </div>

    <hr style="border:0;border-top:1px solid #e5e7eb;margin:14px 0;">

    <h3 class="section__title" style="margin-bottom:8px;">Ringkasan</h3>
    <table class="table">
      <thead>
        <tr>
          <th>Deskripsi</th>
          <th style="width:220px;">Nominal</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Total Order</td>
          <td>Rp <?= number_format((int)($totalOrder ?? 0), 0, ',', '.') ?></td>
        </tr>
        <tr>
          <td>Total Pembayaran Valid</td>
          <td>Rp <?= number_format((int)($totalValid ?? 0), 0, ',', '.') ?></td>
        </tr>
        <tr>
          <td><b>Sisa Tagihan</b></td>
          <td><b>Rp <?= number_format((int)($sisa ?? 0), 0, ',', '.') ?></b></td>
        </tr>
      </tbody>
    </table>

    <div style="height:10px;"></div>

    <h3 class="section__title" style="margin-bottom:8px;">Pembayaran Valid</h3>
    <table class="table">
      <thead>
        <tr>
          <th style="width:160px;">Tanggal</th>
          <th style="width:120px;">Jenis</th>
          <th>Metode</th>
          <th style="width:200px;">Jumlah</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach (($paymentsValid ?? []) as $p): ?>
        <tr>
          <td><?= esc($p['tanggal_bayar'] ?? '-') ?></td>
          <td><?= esc($p['jenis_pembayaran'] ?? '-') ?></td>
          <td><?= esc($p['metode_pembayaran'] ?? '-') ?></td>
          <td>Rp <?= number_format((int)($p['jumlah_bayar'] ?? 0), 0, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <hr style="border:0;border-top:1px solid #e5e7eb;margin:14px 0;">

    <h3 class="section__title" style="margin-bottom:8px;">Info Pembayaran</h3>
    <div class="muted" style="margin-bottom:6px;">Transfer Pembayaran Ke Rekening Berikut A/N: <b>I KADEK DARMADI</b></div>
    <ul style="margin:0;padding-left:18px;">
      <li>BCA: 7680513222</li>
      <li>MANDIRI: 1450015365766</li>
      <li>BNI: 0695785189</li>
      <li>BRI: 477401009136504</li>
      <li>BPD BALI: 0100202547258</li>
      <li>SEABANK: 901081603711</li>
      <li>E-Wallet (OVO/DANA/SHOPEE): 082236004917</li>
    </ul>

    <div class="noPrint" style="margin-top:14px;display:flex;gap:10px;">
      <button class="btnPrimary" onclick="window.print()">Print / Save as PDF</button>
      <a class="btnGhost" href="<?= site_url('admin/pemesanan/'.$order['id_pemesanan']) ?>">Kembali</a>
    </div>
  </div>
</div>

</body>
</html>
