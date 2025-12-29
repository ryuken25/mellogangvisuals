<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="auth-wrap">
  <h1 class="auth-title">Daftar Akun</h1>
  <p class="auth-sub">Buat akun untuk melakukan pemesanan dan pelacakan status pesanan.</p>

  <div class="card">
    <div class="tabs">
      <a class="tab" href="<?= site_url('/login') ?>">Login</a>
      <a class="tab active" href="<?= site_url('/register') ?>">Daftar Akun</a>
    </div>

    <form class="form" method="post" action="<?= site_url('/register') ?>">
      <?= csrf_field() ?>

      <div>
        <div class="label">Nama lengkap</div>
        <input class="input" type="text" name="nama_lengkap" value="<?= old('nama_lengkap') ?>" placeholder="Nama kamu">
        <?php if ($validation->hasError('nama_lengkap')): ?>
          <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('nama_lengkap')) ?></div>
        <?php endif; ?>
      </div>

      <div class="row">
        <div>
          <div class="label">Email</div>
          <input class="input" type="email" name="email" value="<?= old('email') ?>" placeholder="contoh@email.com">
          <?php if ($validation->hasError('email')): ?>
            <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('email')) ?></div>
          <?php endif; ?>
        </div>
        <div>
          <div class="label">No telepon</div>
          <input class="input" type="text" name="no_telepon" value="<?= old('no_telepon') ?>" placeholder="08xxxxxxxxxx">
          <?php if ($validation->hasError('no_telepon')): ?>
            <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('no_telepon')) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="row">
        <div>
          <div class="label">Kata sandi</div>
          <input class="input" type="password" name="password" placeholder="minimal 6 karakter">
          <?php if ($validation->hasError('password')): ?>
            <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('password')) ?></div>
          <?php endif; ?>
        </div>
        <div>
          <div class="label">Ulangi kata sandi</div>
          <input class="input" type="password" name="password_confirm" placeholder="ulangi kata sandi">
          <?php if ($validation->hasError('password_confirm')): ?>
            <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('password_confirm')) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <button class="btn" type="submit">Daftar</button>

      <div class="note">
        Sudah punya akun? <a class="link" href="<?= site_url('/login') ?>">Login</a>.
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
