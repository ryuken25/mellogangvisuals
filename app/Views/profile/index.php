<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container">
  <h2 class="section__title">Profile</h2>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert ok" style="margin-top:10px;"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error" style="margin-top:10px;"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="panel" style="margin-top:12px;">
    <h3 class="miniTitle">Foto Profile</h3>

    <div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;margin-top:10px;">
      <div class="avatarBox">
        <img id="avatarPreview" src="<?= site_url('profile/avatar') ?>" alt="Avatar">
      </div>

      <form method="post" enctype="multipart/form-data" action="<?= site_url('profile/photo') ?>">
        <?= csrf_field() ?>
        <div class="label">Upload Foto (auto crop 1:1 + kompres)</div>
        <input class="input" type="file" name="photo" id="photoInput" accept="image/*" required>
        <button class="btnPrimary" type="submit" style="margin-top:10px;">Simpan Foto</button>
      </form>
    </div>

    <hr style="border:0;border-top:1px solid #e5e7eb;margin:16px 0;">

    <h3 class="miniTitle">Data Profile</h3>
    <form method="post" action="<?= site_url('profile/update') ?>" style="margin-top:10px;">
      <?= csrf_field() ?>

      <div class="row">
        <div>
          <div class="label">Nama</div>
          <input class="input" name="nama_lengkap" value="<?= esc($user['nama_lengkap'] ?? '') ?>" required>
        </div>
        <div>
          <div class="label">Email (tidak bisa diganti)</div>
          <input class="input" value="<?= esc($user['email'] ?? '') ?>" disabled>
        </div>
      </div>

      <div class="row">
        <div>
          <div class="label">No. Telepon</div>
          <input class="input" name="no_telepon" value="<?= esc($user['no_telepon'] ?? '') ?>">
        </div>
      </div>

      <button class="btnPrimary" type="submit" style="margin-top:10px;">Simpan Perubahan</button>
      <a class="btnGhost" href="<?= site_url('/') ?>" style="margin-top:10px;">Kembali</a>
      <a class="btnGhost" href="<?= site_url('logout') ?>" style="margin-top:10px;">Logout</a>
    </form>

    <hr style="border:0;border-top:1px solid #e5e7eb;margin:16px 0;">

    <h3 class="miniTitle">Ganti Password</h3>
    <form method="post" action="<?= site_url('profile/password') ?>"
          onsubmit="return confirm('Yakin mau ganti password?')" style="margin-top:10px;">
      <?= csrf_field() ?>

      <div class="row">
        <div>
          <div class="label">Password Lama</div>
          <input class="input" type="password" name="old_password" required>
        </div>
        <div>
          <div class="label">Password Baru</div>
          <input class="input" type="password" name="new_password" minlength="6" required>
        </div>
      </div>

      <div class="row">
        <div>
          <div class="label">Konfirmasi Password Baru</div>
          <input class="input" type="password" name="confirm_password" minlength="6" required>
        </div>
      </div>

      <button class="btnPrimary" type="submit">Ganti Password</button>
    </form>
  </div>
</div>

<style>
  .avatarBox{
    width:96px;height:96px;border-radius:999px;overflow:hidden;
    border:1px solid #e5e7eb;background:#fff;
  }
  .avatarBox img{ width:100%;height:100%;object-fit:cover;display:block; }
</style>

<script>
(function(){
  const inp = document.getElementById('photoInput');
  const prev = document.getElementById('avatarPreview');
  if (!inp || !prev) return;

  inp.addEventListener('change', () => {
    const f = inp.files && inp.files[0];
    if (!f) return;
    const url = URL.createObjectURL(f);
    prev.src = url;
  });
})();
</script>

<?= $this->endSection() ?>
