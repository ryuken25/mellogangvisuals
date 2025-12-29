<?= $this->extend('layout/admin') ?>
<?= $this->section('adminContent') ?>

<div class="auth-wrap">
  <h1 class="auth-title"><?= esc($title) ?></h1>
  <p class="auth-sub">Isi data paket layanan.</p>

  <div class="card">
    <form class="form" method="post"
          action="<?= $mode === 'create' ? site_url('/admin/paket') : site_url('/admin/paket/update/'.$data['id_paket']) ?>">
      <?= csrf_field() ?>

      <div>
        <div class="label">Nama paket</div>
        <input class="input" name="nama_paket" value="<?= esc($data['nama_paket'] ?? old('nama_paket')) ?>">
        <?php if ($validation->hasError('nama_paket')): ?>
          <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('nama_paket')) ?></div>
        <?php endif; ?>
      </div>

      <div class="row">
        <div>
          <div class="label">Kategori</div>
          <input class="input" name="kategori" value="<?= esc($data['kategori'] ?? old('kategori')) ?>">
          <?php if ($validation->hasError('kategori')): ?>
            <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('kategori')) ?></div>
          <?php endif; ?>
        </div>
        <div>
          <div class="label">Durasi (jam)</div>
          <input class="input" name="durasi_jam" value="<?= esc($data['durasi_jam'] ?? old('durasi_jam')) ?>">
          <?php if ($validation->hasError('durasi_jam')): ?>
            <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('durasi_jam')) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="row">
        <div>
          <div class="label">Harga</div>
          <input class="input" name="harga" value="<?= esc($data['harga'] ?? old('harga')) ?>">
          <?php if ($validation->hasError('harga')): ?>
            <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('harga')) ?></div>
          <?php endif; ?>
        </div>
        <div>
          <div class="label">Aktif? (1/0)</div>
          <input class="input" name="is_active" value="<?= esc($data['is_active'] ?? old('is_active') ?? 1) ?>">
        </div>
      </div>

      <div>
        <div class="label">Deskripsi</div>
        <textarea class="input" name="deskripsi" rows="3"><?= esc($data['deskripsi'] ?? old('deskripsi')) ?></textarea>
      </div>

      <button class="btn" type="submit">Simpan</button>
      <div class="note"><a class="link" href="<?= site_url('/admin/paket') ?>">Kembali</a></div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
