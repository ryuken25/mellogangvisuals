<?= $this->extend('layout/admin') ?>
<?= $this->section('adminContent') ?>

<div class="container">
  <div class="panel">
    <h2 class="section__title">Admin - Portofolio</h2>
    <p class="auth-sub">Kelola portofolio (CRUD).</p>

    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
      <a class="tab active" href="<?= site_url('admin/portofolio/create') ?>" style="margin:0;">+ Tambah Portofolio</a>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th style="width:90px;">Thumb</th>
          <th>Judul</th>
          <th style="width:160px;">Kategori</th>
          <th style="width:120px;">URL</th>
          <th style="width:140px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($items as $po): ?>
        <?php
          $thumb = base_url('assets/images/porto_placeholder.png');

          $thumbName = (string)($po['thumbnail'] ?? '');
          if ($thumbName !== '') {
            $thumb = base_url('uploads/portofolio/' . $thumbName);
          } else {
            // fallback kalau belum upload thumbnail: coba derive dari url_media
            $url = (string)($po['url_media'] ?? '');
            if (preg_match('~\.(jpg|jpeg|png|webp|gif)(\?.*)?$~i', $url)) $thumb = $url;
            if (preg_match('~youtu\.be/([^/?]+)~', $url, $m)) $thumb = 'https://img.youtube.com/vi/'.$m[1].'/hqdefault.jpg';
            if (preg_match('~v=([^&]+)~', $url, $m)) $thumb = 'https://img.youtube.com/vi/'.$m[1].'/hqdefault.jpg';
          }
        ?>
        <tr>
          <td>
            <div style="width:70px;height:44px;border-radius:10px;background-image:url('<?= esc($thumb) ?>');background-size:cover;background-position:center;border:1px solid #e5e7eb;background-color:#f3f4f6;"></div>
          </td>
          <td><?= esc($po['judul']) ?></td>
          <td><?= esc($po['kategori']) ?></td>
          <td>
            <a class="btnMini" href="<?= esc($po['url_media'] ?? '#') ?>" target="_blank" rel="noopener">Buka</a>
          </td>
          <td style="display:flex;gap:10px;align-items:center;">
            <a class="btnMini" href="<?= site_url('admin/portofolio/edit/'.$po['id_portfolio']) ?>">Edit</a>

            <form method="post" action="<?= site_url('admin/portofolio/delete/'.$po['id_portfolio']) ?>" onsubmit="return confirm('Hapus portofolio ini?');">
              <?= csrf_field() ?>
              <button type="submit" class="btnMini" style="background:#fee2e2;border-color:#fecaca;">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
