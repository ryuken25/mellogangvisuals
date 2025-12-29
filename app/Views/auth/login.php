<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="auth-wrap">
  <h1 class="auth-title">Login</h1>
  <p class="auth-sub">Masuk untuk memesan layanan, melihat status pesanan, atau mengelola sistem sesuai hak akses.</p>

  <div class="card">
    <div class="tabs">
      <a class="tab active" href="<?= site_url('/login') ?>">Login</a>
      <a class="tab" href="<?= site_url('/register') ?>">Daftar Akun</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= site_url('/login') ?>">
      <?= csrf_field() ?>

      <div>
        <div class="label">Email</div>
        <input class="input" type="email" name="email" value="<?= old('email') ?>" placeholder="contoh@email.com">
        <?php if ($validation->hasError('email')): ?>
          <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('email')) ?></div>
        <?php endif; ?>
      </div>

      <div>
        <div class="label">Kata sandi</div>
        <input class="input" type="password" name="password" placeholder="••••••••">
        <?php if ($validation->hasError('password')): ?>
          <div class="label" style="color:#b91c1c;"><?= esc($validation->getError('password')) ?></div>
        <?php endif; ?>
      </div>

      <div class="actions">
        <a class="link" href="#">Lupa kata sandi?</a>
      </div>

      <button class="btn" type="submit">Masuk</button>

      <div class="note">
        Belum punya akun? Klik <a class="link" href="<?= site_url('/register') ?>">daftar akun</a>.
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
