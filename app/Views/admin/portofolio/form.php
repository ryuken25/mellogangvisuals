<?= $this->extend('layout/admin') ?>
<?= $this->section('adminContent') ?>

<?php
// FIX: biar gak warning "null used as array"
$data = (is_array($data ?? null)) ? $data : [];
$thumbName = (string)($data['thumbnail'] ?? '');
$thumbUrl = $thumbName !== '' ? base_url('uploads/portofolio/' . $thumbName) : '';
?>

<div class="container">
  <div class="panel">
    <h2 class="section__title"><?= esc($title) ?></h2>
    <p class="auth-sub">Isi data portofolio.</p>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <form class="form" method="post" enctype="multipart/form-data"
      action="<?= $mode === 'create'
        ? site_url('admin/portofolio')
        : site_url('admin/portofolio/update/' . (int)($data['id_portfolio'] ?? 0)) ?>">
      <?= csrf_field() ?>

      <div>
        <div class="label">Paket</div>
        <select class="input" name="id_paket">
          <option value="">-- pilih paket --</option>
          <?php foreach ($paket as $p): ?>
            <?php $selected = ((string)($data['id_paket'] ?? old('id_paket')) === (string)$p['id_paket']) ? 'selected' : ''; ?>
            <option value="<?= (int)$p['id_paket'] ?>" <?= $selected ?>><?= esc($p['nama_paket']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if ($validation->hasError('id_paket')): ?>
          <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('id_paket')) ?></div>
        <?php endif; ?>
      </div>

      <div>
        <div class="label">Judul</div>
        <input class="input" name="judul" value="<?= esc($data['judul'] ?? old('judul')) ?>">
        <?php if ($validation->hasError('judul')): ?>
          <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('judul')) ?></div>
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
          <div class="label">Tanggal publikasi</div>
          <input class="input" type="date" name="tanggal_publikasi" value="<?= esc($data['tanggal_publikasi'] ?? old('tanggal_publikasi')) ?>">
          <?php if ($validation->hasError('tanggal_publikasi')): ?>
            <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('tanggal_publikasi')) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div>
        <div class="label">URL Media (link video/foto)</div>
        <input class="input" name="url_media" value="<?= esc($data['url_media'] ?? old('url_media')) ?>" placeholder="https://...">
        <?php if ($validation->hasError('url_media')): ?>
          <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('url_media')) ?></div>
        <?php endif; ?>
      </div>

      <div>
        <div class="label">Thumbnail (JPG/PNG/WebP max 3MB)</div>

        <?php if ($thumbUrl !== ''): ?>
          <div style="margin:8px 0;">
            <div class="muted" style="margin-bottom:6px;">Thumbnail saat ini:</div>
            <img src="<?= esc($thumbUrl) ?>"
                 alt="thumb"
                 style="width:260px;aspect-ratio:16/9;object-fit:cover;border-radius:12px;border:1px solid #e5e7eb;">
          </div>
        <?php endif; ?>

        <input class="input" type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp">
        <small class="muted">
          <?= $mode === 'create' ? 'Wajib diisi untuk portofolio baru.' : 'Kosongkan jika tidak ingin mengganti thumbnail.' ?>
        </small>
        <?php if ($validation->hasError('thumbnail')): ?>
          <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('thumbnail')) ?></div>
        <?php endif; ?>
      </div>

      <div>
        <div class="label">Deskripsi</div>
        <textarea class="input" name="deskripsi" rows="3"><?= esc($data['deskripsi'] ?? old('deskripsi')) ?></textarea>
      </div>

      <button class="btnPrimary" type="submit">Simpan</button>
      <div class="note"><a class="link" href="<?= site_url('admin/portofolio') ?>">Kembali</a></div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
