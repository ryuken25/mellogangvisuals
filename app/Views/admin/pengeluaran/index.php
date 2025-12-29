<?= $this->extend('layout/admin') ?>
<?= $this->section('adminContent') ?>

<h2 class="section__title">Pengeluaran Operasional</h2>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="panel" style="margin-top:12px;">
  <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;">
    <div>
      <div class="label">Dari</div>
      <input class="input" type="date" name="start" value="<?= esc($start) ?>">
    </div>
    <div>
      <div class="label">Sampai</div>
      <input class="input" type="date" name="end" value="<?= esc($end) ?>">
    </div>
    <button class="btnPrimary" type="submit">Tampilkan</button>
    <a class="btnGhost" href="<?= site_url('admin/pengeluaran') ?>">Reset</a>
    <a class="btnPrimary" href="<?= site_url('admin/pengeluaran/create') ?>">+ Tambah</a>
  </form>

  <div style="height:12px"></div>

  <div class="alert ok">
    Total Pengeluaran Periode: <b>Rp <?= number_format((int)$total, 0, ',', '.') ?></b>
  </div>

  <table class="table" style="margin-top:10px;">
    <thead>
      <tr>
        <th>Tanggal</th>
        <th>Nama Pengeluaran</th>
        <th>Kode Pesanan</th>
        <th>Nominal</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= esc($r['tanggal_pengeluaran']) ?></td>
          <td><?= esc($r['nama_pengeluaran']) ?></td>
          <td><?= esc($r['kode_pemesanan'] ?? '-') ?></td>
          <td>Rp <?= number_format((int)$r['nominal'], 0, ',', '.') ?></td>
          <td style="display:flex;gap:8px;">
            <a class="link" href="<?= site_url('admin/pengeluaran/edit/'.$r['id_pengeluaran']) ?>">Edit</a>
            <form method="post" action="<?= site_url('admin/pengeluaran/delete/'.$r['id_pengeluaran']) ?>" onsubmit="return confirm('Hapus pengeluaran ini?')">
              <?= csrf_field() ?>
              <button class="link" type="submit">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
