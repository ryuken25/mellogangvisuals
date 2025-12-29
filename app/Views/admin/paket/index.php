<?= $this->extend('layout/admin') ?>
<?= $this->section('adminContent') ?>

<div class="auth-wrap">
  <h1 class="auth-title">Admin - Paket</h1>
  <p class="auth-sub">Kelola paket layanan (CRUD).</p>

  <div class="card" style="max-width:900px;">
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
      <a class="tab active" href="<?= site_url('/admin/paket/create') ?>" style="margin:0;">+ Tambah Paket</a>
    </div>

    <table style="width:100%; border-collapse:collapse;">
      <thead>
        <tr style="text-align:left;">
          <th style="padding:10px;border-bottom:1px solid #e5e7eb;">Nama</th>
          <th style="padding:10px;border-bottom:1px solid #e5e7eb;">Kategori</th>
          <th style="padding:10px;border-bottom:1px solid #e5e7eb;">Harga</th>
          <th style="padding:10px;border-bottom:1px solid #e5e7eb;">Aktif</th>
          <th style="padding:10px;border-bottom:1px solid #e5e7eb;">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($paket as $p): ?>
        <tr>
          <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?= esc($p['nama_paket']) ?></td>
          <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?= esc($p['kategori']) ?></td>
          <td style="padding:10px;border-bottom:1px solid #f1f5f9;">Rp <?= number_format((int)$p['harga'], 0, ',', '.') ?></td>
          <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?= (int)$p['is_active'] === 1 ? 'Ya' : 'Tidak' ?></td>
          <td style="padding:10px;border-bottom:1px solid #f1f5f9;">
            <a class="link" href="<?= site_url('/admin/paket/edit/'.$p['id_paket']) ?>">Edit</a>
            <form method="post" action="<?= site_url('/admin/paket/delete/'.$p['id_paket']) ?>" style="display:inline;">
              <?= csrf_field() ?>
              <button type="submit" class="link" style="border:0;background:none;cursor:pointer;color:#b91c1c;">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
