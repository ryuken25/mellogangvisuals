<?= $this->extend('layout/admin') ?>
<?= $this->section('adminContent') ?>

<h2 class="section__title"><?= esc($title) ?></h2>

<?php if (session()->getFlashdata('error')): ?>
  <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="panel" style="margin-top:12px;">
  <form method="post"
    action="<?= $mode === 'create' ? site_url('admin/pengeluaran') : site_url('admin/pengeluaran/update/'.$data['id_pengeluaran']) ?>">
    <?= csrf_field() ?>

    <div class="row">
      <div>
        <div class="label">Terkait Pesanan (opsional)</div>
        <select class="input" name="id_pemesanan">
          <option value="">- Tidak terkait pesanan -</option>
          <?php foreach ($orders as $o): ?>
            <?php $sel = ((string)($data['id_pemesanan'] ?? '') === (string)$o['id_pemesanan']) ? 'selected' : ''; ?>
            <option value="<?= $o['id_pemesanan'] ?>" <?= $sel ?>><?= esc($o['kode_pemesanan']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <div class="label">Tanggal</div>
        <input class="input" type="date" name="tanggal_pengeluaran" value="<?= esc($data['tanggal_pengeluaran'] ?? date('Y-m-d')) ?>">
      </div>
    </div>

    <div class="row">
      <div>
        <div class="label">Nama Pengeluaran</div>
        <input class="input" name="nama_pengeluaran" value="<?= esc($data['nama_pengeluaran'] ?? '') ?>" placeholder="Contoh: Bensin, Konsumsi kru, Gaji editor">
      </div>
      <div>
        <div class="label">Nominal</div>
        <input class="input" type="number" name="nominal" value="<?= esc($data['nominal'] ?? 0) ?>">
      </div>
    </div>

    <button class="btnPrimary" type="submit">Simpan</button>
    <a class="btnGhost" href="<?= site_url('admin/pengeluaran') ?>">Kembali</a>
  </form>
</div>

<?= $this->endSection() ?>
