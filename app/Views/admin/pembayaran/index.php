<?= $this->extend('layout/admin') ?>
<?= $this->section('adminContent') ?>

<div class="container">
  <div class="sectionHead">
    <div>
      <h2 class="section__title">Pembayaran</h2>
      <div class="muted">Daftar pembayaran DP/Pelunasan yang diupload pelanggan.</div>
    </div>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert ok" style="margin-top:12px;"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error" style="margin-top:12px;"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="panel" style="margin-top:12px;">
    <form method="get" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      <?php $st = strtolower((string)($status ?? 'all')); ?>
      <select class="input" name="status" style="max-width:240px;">
        <option value="all" <?= $st==='all'?'selected':'' ?>>Semua</option>
        <option value="menunggu" <?= $st==='menunggu'?'selected':'' ?>>Menunggu</option>
        <option value="valid" <?= $st==='valid'?'selected':'' ?>>Valid</option>
        <option value="ditolak" <?= $st==='ditolak'?'selected':'' ?>>Ditolak</option>
      </select>

      <button class="btnPrimary" type="submit">Filter</button>
      <a class="btnGhost" href="<?= site_url('admin/pembayaran') ?>">Reset</a>
    </form>

    <div style="height:12px"></div>

    <?php if (empty($rows)): ?>
      <div class="alert ok">Tidak ada data pembayaran.</div>
    <?php else: ?>
      <div class="tableWrap">
        <table class="table">
          <thead>
            <tr>
              <th style="width:150px;">Kode</th>
              <th>Pelanggan</th>
              <th style="width:110px;">Jenis</th>
              <th style="width:160px;">Tanggal</th>
              <th style="width:140px;">Metode</th>
              <th style="width:150px;">Jumlah</th>
              <th style="width:120px;">Status</th>
              <th style="width:140px;">Bukti</th>
              <th style="width:90px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= esc($r['kode_pemesanan'] ?? '-') ?></td>
                <td><?= esc($r['nama_lengkap'] ?? '-') ?></td>
                <td><?= esc($r['jenis_pembayaran'] ?? '-') ?></td>
                <td>
                  <?php
                    $tgl = $r['tanggal_bayar'] ?? null;
                    echo $tgl ? esc(date('d/m/Y H:i', strtotime($tgl))) : '-';
                  ?>
                </td>
                <td><?= esc($r['metode_pembayaran'] ?? '-') ?></td>
                <td>Rp <?= number_format((int)($r['jumlah_bayar'] ?? 0), 0, ',', '.') ?></td>
                <td><span class="pill"><?= esc($r['status_verifikasi'] ?? '-') ?></span></td>

                <td>
                  <?php if (!empty($r['bukti_bayar'])): ?>
                    <a class="link" target="_blank" rel="noopener" href="<?= site_url('admin/pembayaran/file/'.$r['id_pembayaran']) ?>">
                      Preview
                    </a>
                    <span class="muted">·</span>
                    <a class="link" href="<?= site_url('admin/pembayaran/file/'.$r['id_pembayaran'].'?download=1') ?>">
                      Download
                    </a>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>

                <td>
                  <a class="btnMini" href="<?= site_url('admin/pembayaran/verify/'.$r['id_pembayaran']) ?>">Verifikasi</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
