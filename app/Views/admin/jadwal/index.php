<?= $this->extend('layout/admin') ?>
<?= $this->section('adminContent') ?>

<div class="container">


  <h2 class="section__title">Jadwal Produksi</h2>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <div class="panel" style="margin-top:12px;">
    <a class="btnPrimary" href="<?= site_url('admin/jadwal/create') ?>">+ Buat Jadwal</a>

    <div style="height:12px"></div>

    <table class="table">
      <thead><tr><th>Kode</th><th>Editor</th><th>Shooting</th><th>Status Produksi</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= esc($r['kode_pemesanan'] ?? '-') ?></td>
          <td><?= esc($r['nama_editor'] ?? '-') ?></td>
          <td><?= esc($r['tanggal_shooting']) ?> (<?= esc($r['jam_mulai_shooting']) ?>-<?= esc($r['jam_selesai_shooting']) ?>)</td>
          <td><span class="pill"><?= esc($r['status_produksi']) ?></span></td>
          <td><a class="link" href="<?= site_url('admin/jadwal/edit/'.$r['id_jadwal']) ?>">Edit</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
