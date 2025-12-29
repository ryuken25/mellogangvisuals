<?= $this->extend('layout/editor') ?>
<?= $this->section('editorContent') ?>
<div class="container">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
    <h2 class="section__title">Tugas Editing</h2>
    <a class="btnGhost" href="<?= site_url('editor') ?>">Kembali</a>
  </div>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="panel" style="margin-top:12px;">
    <form method="get" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      <select class="input" name="status" style="max-width:260px;">
        <option value="">-- semua status --</option>
        <?php
          $opts = ['pra produksi','shooting','cut-to-cut','finishing','done','revisi','revisi selesai'];
          foreach ($opts as $o):
            $sel = ($status === $o) ? 'selected' : '';
        ?>
          <option value="<?= esc($o) ?>" <?= $sel ?>><?= esc($o) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btnPrimary" type="submit">Filter</button>
      <a class="btnGhost" href="<?= site_url('editor/tugas') ?>">Reset</a>
    </form>

    <div style="height:12px;"></div>

    <table class="table">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Pelanggan</th>
          <th>Paket</th>
          <th>Status Produksi</th>
          <th>Editing</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6" class="muted">Belum ada tugas.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= esc($r['kode_pemesanan'] ?? '-') ?></td>
              <td><?= esc($r['nama_pelanggan'] ?? '-') ?></td>
              <td><?= esc($r['nama_paket'] ?? '-') ?></td>
              <td><span class="pill"><?= esc($r['status_produksi'] ?? '-') ?></span></td>
              <td><?= esc($r['tanggal_mulai_editing'] ?? '-') ?> → <?= esc($r['tanggal_selesai_editing'] ?? '-') ?></td>
              <td><a class="link" href="<?= site_url('editor/tugas/'.$r['id_jadwal']) ?>">Detail</a></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
