<?= $this->extend('layout/admin') ?>
<?= $this->section('adminContent') ?>

<h2 class="section__title">Laporan</h2>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="adminCards" style="margin-top:10px;">
  <div class="adminCard">
    <div class="adminCard__label">Total Pemasukan (Valid)</div>
    <div class="adminCard__value">Rp <?= number_format((int)$totalPemasukan,0,',','.') ?></div>
  </div>
  <div class="adminCard">
    <div class="adminCard__label">Total Pengeluaran</div>
    <div class="adminCard__value">Rp <?= number_format((int)$totalPengeluaran,0,',','.') ?></div>
  </div>
  <div class="adminCard">
    <div class="adminCard__label">Laba/Rugi</div>
    <div class="adminCard__value">Rp <?= number_format((int)$laba,0,',','.') ?></div>
  </div>
</div>

<div class="panel" style="margin-top:16px;">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
    <div>
      <div class="label">Pemasukan</div>
      <div class="muted">Default hanya yang valid. Pending tampil kalau filter diubah.</div>
    </div>

    <form method="get" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      <select class="input" name="pay" style="min-width:180px;">
        <option value="valid"   <?= ($pay ?? 'valid') === 'valid' ? 'selected' : '' ?>>Terbayar (Valid)</option>
        <option value="pending" <?= ($pay ?? '') === 'pending' ? 'selected' : '' ?>>Pending (Menunggu / Sisa)</option>
        <option value="all"     <?= ($pay ?? '') === 'all' ? 'selected' : '' ?>>All</option>
      </select>
      <button class="btnPrimary" type="submit">Terapkan</button>
      <a class="btnGhost" href="<?= site_url('admin/laporan') ?>">Reset</a>
    </form>
  </div>

  <div style="height:12px"></div>

  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a class="btnGhost" href="<?= site_url('admin/laporan/export/pembayaran?pay=valid') ?>">Export Terbayar (CSV)</a>
    <a class="btnGhost" href="<?= site_url('admin/laporan/export/pembayaran?pay=pending') ?>">Export Pending (CSV)</a>
    <a class="btnGhost" href="<?= site_url('admin/laporan/export/pembayaran?pay=all') ?>">Export All (CSV)</a>
    <a class="btnGhost" href="<?= site_url('admin/laporan/export/pengeluaran') ?>">Export Pengeluaran (CSV)</a>
  </div>
</div>

<?php
// helper group by date
$printPaymentsByDate = function(array $rows) {
  $lastDate = null;
  foreach ($rows as $r) {
    $d = '';
    if (!empty($r['tanggal_bayar'])) $d = date('Y-m-d', strtotime($r['tanggal_bayar']));
    if ($d !== $lastDate) {
      echo '<div class="panel" style="margin-top:12px;"><b>'.esc($d ?: '-').'</b></div>';
      echo '<div class="panel" style="margin-top:8px;">';
      echo '<table class="table"><thead><tr>
              <th>Kode</th><th>Pelanggan</th><th>Jenis</th><th>Metode</th><th>Jumlah</th><th>Status</th>
            </tr></thead><tbody>';
      $lastDate = $d;
    }

    echo '<tr>';
    echo '<td>'.esc($r['kode_pemesanan'] ?? '-').'</td>';
    echo '<td>'.esc($r['nama_lengkap'] ?? '-').'</td>';
    echo '<td>'.esc($r['jenis_pembayaran'] ?? '-').'</td>';
    echo '<td>'.esc($r['metode_pembayaran'] ?? '-').'</td>';
    echo '<td>Rp '.number_format((int)($r['jumlah_bayar'] ?? 0),0,',','.').'</td>';
    echo '<td><span class="pill">'.esc($r['status_verifikasi'] ?? '-').'</span></td>';
    echo '</tr>';

    // kalau next date beda atau end -> close
    // (kita cek via output buffering manual)
  }

  // close tables properly (cara aman: kalau rows ada, tutup di akhir)
  if (!empty($rows)) {
    echo '</tbody></table></div>';
  }
};
?>

<?php if (($pay ?? 'valid') !== 'pending'): ?>
  <div style="margin-top:16px;">
    <h3 class="section__title" style="margin-bottom:6px;">Terbayar (Valid)</h3>
    <?php if (empty($validPayments)): ?>
      <div class="alert ok">Belum ada pembayaran valid.</div>
    <?php else: ?>
      <?php
        // render manual group-by-date agar rapih
        $last = null;
        foreach ($validPayments as $r):
          $d = !empty($r['tanggal_bayar']) ? date('Y-m-d', strtotime($r['tanggal_bayar'])) : '-';
          if ($d !== $last):
            if ($last !== null) echo '</tbody></table></div>';
            echo '<div class="panel" style="margin-top:12px;"><b>'.esc($d).'</b></div>';
            echo '<div class="panel" style="margin-top:8px;">';
            echo '<table class="table"><thead><tr>
                    <th>Kode</th><th>Pelanggan</th><th>Jenis</th><th>Metode</th><th>Jumlah</th><th>Status</th>
                  </tr></thead><tbody>';
            $last = $d;
          endif;
        ?>
          <tr>
            <td><?= esc($r['kode_pemesanan'] ?? '-') ?></td>
            <td><?= esc($r['nama_lengkap'] ?? '-') ?></td>
            <td><?= esc($r['jenis_pembayaran'] ?? '-') ?></td>
            <td><?= esc($r['metode_pembayaran'] ?? '-') ?></td>
            <td>Rp <?= number_format((int)($r['jumlah_bayar'] ?? 0),0,',','.') ?></td>
            <td><span class="pill"><?= esc($r['status_verifikasi'] ?? '-') ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody></table></div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if (($pay ?? '') !== 'valid'): ?>
  <div style="margin-top:18px;">
    <h3 class="section__title" style="margin-bottom:6px;">Pending (Menunggu Verifikasi)</h3>
    <div class="muted">Yang tampil di sini hanya status <b>Menunggu</b> (bukan ditolak).</div>

    <?php if (empty($menungguPayments)): ?>
      <div class="alert ok" style="margin-top:10px;">Tidak ada pembayaran Menunggu.</div>
    <?php else: ?>
      <?php
        $last = null;
        foreach ($menungguPayments as $r):
          $d = !empty($r['tanggal_bayar']) ? date('Y-m-d', strtotime($r['tanggal_bayar'])) : '-';
          if ($d !== $last):
            if ($last !== null) echo '</tbody></table></div>';
            echo '<div class="panel" style="margin-top:12px;"><b>'.esc($d).'</b></div>';
            echo '<div class="panel" style="margin-top:8px;">';
            echo '<table class="table"><thead><tr>
                    <th>Kode</th><th>Pelanggan</th><th>Jenis</th><th>Metode</th><th>Jumlah</th><th>Status</th>
                  </tr></thead><tbody>';
            $last = $d;
          endif;
        ?>
          <tr>
            <td><?= esc($r['kode_pemesanan'] ?? '-') ?></td>
            <td><?= esc($r['nama_lengkap'] ?? '-') ?></td>
            <td><?= esc($r['jenis_pembayaran'] ?? '-') ?></td>
            <td><?= esc($r['metode_pembayaran'] ?? '-') ?></td>
            <td>Rp <?= number_format((int)($r['jumlah_bayar'] ?? 0),0,',','.') ?></td>
            <td><span class="pill"><?= esc($r['status_verifikasi'] ?? '-') ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody></table></div>
    <?php endif; ?>
  </div>

  <div style="margin-top:18px;">
    <h3 class="section__title" style="margin-bottom:6px;">Pending (Sisa Pelunasan / Belum Bayar)</h3>
    <div class="muted">DP valid tapi belum lunas juga muncul di sini (kolom Sisa).</div>

    <?php if (empty($rekapOrder)): ?>
      <div class="alert ok" style="margin-top:10px;">Tidak ada order pending.</div>
    <?php else: ?>
      <div class="panel" style="margin-top:10px;">
        <table class="table">
          <thead>
            <tr>
              <th>Kode</th><th>Pelanggan</th><th>Tanggal Pesan</th><th>Status</th>
              <th>Total</th><th>Total Valid</th><th>Sisa</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($rekapOrder as $o): ?>
            <tr>
              <td><?= esc($o['kode_pemesanan'] ?? '-') ?></td>
              <td><?= esc($o['nama_lengkap'] ?? '-') ?></td>
              <td><?= esc($o['tanggal_pemesanan'] ?? '-') ?></td>
              <td><span class="pill"><?= esc($o['status_pemesanan'] ?? '-') ?></span></td>
              <td>Rp <?= number_format((int)($o['total_biaya'] ?? 0),0,',','.') ?></td>
              <td>Rp <?= number_format((int)($o['total_valid'] ?? 0),0,',','.') ?></td>
              <td>Rp <?= number_format((int)($o['sisa'] ?? 0),0,',','.') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<hr style="border:0;border-top:1px solid #e5e7eb;margin:22px 0;">

<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
  <h3 class="section__title" style="margin:0;">Pengeluaran Operasional</h3>
  <a class="btnPrimary" href="<?= site_url('admin/laporan?edit_pengeluaran=0') ?>">+ Tambah Pengeluaran</a>
</div>

<?php
$isEdit = !empty($editPengeluaran);
$cols = $pengCols ?? [];
?>

<div class="panel" style="margin-top:12px;">
  <form method="post" action="<?= $isEdit ? site_url('admin/laporan/pengeluaran/update/'.$editPengeluaran['id_pengeluaran']) : site_url('admin/laporan/pengeluaran') ?>">
    <?= csrf_field() ?>

    <div class="row">
      <div>
        <div class="label">Tanggal</div>
        <input class="input" type="date" name="tanggal"
          value="<?= esc($isEdit ? ($editPengeluaran[$cols['tanggal']] ?? '') : '') ?>" required>
      </div>
      <div>
        <div class="label">Nama Pengeluaran</div>
        <input class="input" name="nama_pengeluaran"
          value="<?= esc($isEdit ? ($editPengeluaran[$cols['nama']] ?? '') : '') ?>" required>
      </div>
      <div>
        <div class="label">Nominal</div>
        <input class="input" type="number" name="jumlah"
          value="<?= esc($isEdit ? ($editPengeluaran[$cols['jumlah']] ?? '') : '') ?>" required>
      </div>
    </div>

    <div class="row">
      <div>
        <div class="label">ID Pemesanan (opsional)</div>
        <input class="input" type="number" name="id_pemesanan"
          value="<?= esc($isEdit && !empty($cols['idpes']) ? ($editPengeluaran[$cols['idpes']] ?? '') : '') ?>">
      </div>
    </div>

    <button class="btnPrimary" type="submit" style="margin-top:10px;">
      <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Pengeluaran' ?>
    </button>

    <?php if ($isEdit): ?>
      <a class="btnGhost" href="<?= site_url('admin/laporan') ?>" style="margin-top:10px;">Batal</a>
    <?php endif; ?>
  </form>
</div>

<div class="panel" style="margin-top:12px;">
  <?php if (empty($pengeluaran)): ?>
    <div class="alert ok">Belum ada pengeluaran.</div>
  <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Nama Pengeluaran</th>
          <th>Nominal</th>
          <th>ID Pemesanan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($pengeluaran as $p): ?>
        <tr>
          <td><?= esc($p[$cols['tanggal']] ?? '-') ?></td>
          <td><?= esc($p[$cols['nama']] ?? '-') ?></td>
          <td>Rp <?= number_format((int)($p[$cols['jumlah']] ?? 0),0,',','.') ?></td>
          <td><?= esc(!empty($cols['idpes']) ? ($p[$cols['idpes']] ?? '-') : '-') ?></td>
          <td style="display:flex;gap:8px;flex-wrap:wrap;">
            <a class="link" href="<?= site_url('admin/laporan?edit_pengeluaran='.$p['id_pengeluaran']) ?>">Edit</a>
            <form method="post" action="<?= site_url('admin/laporan/pengeluaran/delete/'.$p['id_pengeluaran']) ?>" onsubmit="return confirm('Hapus pengeluaran ini?')">
              <?= csrf_field() ?>
              <button class="link" type="submit" style="border:0;background:transparent;padding:0;color:#dc2626;cursor:pointer;">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
