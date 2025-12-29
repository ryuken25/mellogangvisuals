<?= $this->extend('layout/admin') ?>
<?= $this->section('adminContent') ?>

<h2 class="section__title">Edit User</h2>
<p class="auth-sub">Ubah data user & role (pelanggan/editor). Admin juga bisa reset password user jika lupa.</p>

<?php if (session()->getFlashdata('error')): ?>
  <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('success')): ?>
  <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="panel" style="margin-top:12px;">
  <form method="post" action="<?= site_url('admin/users/update/'.$row['id_user']) ?>">
    <?= csrf_field() ?>

    <div class="row">
      <div>
        <div class="label">Nama Lengkap</div>
        <input class="input" name="nama_lengkap" value="<?= esc(old('nama_lengkap', $row['nama_lengkap'] ?? '')) ?>" required>
      </div>

      <div>
        <div class="label">Telepon</div>
        <input class="input" name="no_telepon" value="<?= esc(old('no_telepon', $row['no_telepon'] ?? '')) ?>">
      </div>
    </div>

    <div class="row">
      <div>
        <div class="label">Email (readonly)</div>
        <input class="input" value="<?= esc($row['email'] ?? '') ?>" readonly>
      </div>

      <div>
        <div class="label">Role</div>
        <?php $curRole = old('role', $row['role'] ?? 'pelanggan'); ?>
        <select class="input" name="role" required>
          <option value="pelanggan" <?= ($curRole === 'pelanggan') ? 'selected' : '' ?>>pelanggan</option>
          <option value="editor" <?= ($curRole === 'editor') ? 'selected' : '' ?>>editor</option>
        </select>
        <small class="muted">Role admin tidak bisa diubah dari halaman ini.</small>
      </div>
    </div>

    <hr style="border:0;border-top:1px solid #e5e7eb;margin:14px 0;">

    <h3 class="section__title" style="margin-bottom:8px;">Reset Password</h3>
    <div class="row">
      <div>
        <div class="label">Password Baru (opsional)</div>
        <input class="input" type="password" name="new_password" placeholder="Kosongkan jika tidak ingin mengubah">
        <small class="muted">Minimal 6 karakter.</small>
      </div>
      <div>
        <div class="label">Konfirmasi Password Baru</div>
        <input class="input" type="password" name="new_password_confirm" placeholder="Ulangi password baru">
      </div>
    </div>

    <div style="margin-top:10px;display:flex;gap:10px;">
      <button class="btnPrimary" type="submit">Simpan</button>
      <a class="btnGhost" href="<?= site_url('admin/users') ?>">Kembali</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
