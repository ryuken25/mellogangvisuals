<?= $this->extend('layout/admin') ?>
<?= $this->section('adminContent') ?>

<h2 class="section__title">Kelola Users</h2>
<p class="auth-sub">Edit data user & ubah role menjadi <b>pelanggan</b> atau <b>editor</b>.</p>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="panel" style="margin-top:12px;">
  <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <input class="input" name="q" value="<?= esc($q ?? '') ?>" placeholder="Cari nama/email/telepon" style="max-width:260px;">
    <select class="input" name="role" style="max-width:200px;">
      <option value="">Semua Role</option>
      <?php foreach (['admin','editor','pelanggan'] as $r): ?>
        <option value="<?= esc($r) ?>" <?= (($role ?? '') === $r) ? 'selected' : '' ?>><?= esc($r) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btnPrimary" type="submit">Filter</button>
    <a class="btnGhost" href="<?= site_url('admin/users') ?>">Reset</a>
  </form>

  <div style="height:12px"></div>

  <?php if (empty($rows)): ?>
    <div class="alert ok">Belum ada user.</div>
  <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th style="width:80px;">ID</th>
          <th>Nama</th>
          <th>Email</th>
          <th style="width:160px;">Telepon</th>
          <th style="width:120px;">Role</th>
          <th style="width:160px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $u): ?>
        <tr>
          <td><?= (int)$u['id_user'] ?></td>
          <td><?= esc($u['nama_lengkap'] ?? '-') ?></td>
          <td><?= esc($u['email'] ?? '-') ?></td>
          <td><?= esc($u['no_telepon'] ?? '-') ?></td>
          <td><span class="pill"><?= esc($u['role'] ?? '-') ?></span></td>
          <td style="display:flex;gap:8px;align-items:center;">
            <a class="btnMini" href="<?= site_url('admin/users/edit/'.$u['id_user']) ?>">Edit</a>

            <?php if (($u['role'] ?? '') !== 'admin'): ?>
              <form method="post" action="<?= site_url('admin/users/delete/'.$u['id_user']) ?>" onsubmit="return confirm('Hapus user ini?');">
                <?= csrf_field() ?>
                <button class="btnMini" type="submit" style="background:#fee2e2;border-color:#fecaca;">Delete</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
