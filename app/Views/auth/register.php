<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="auth-wrap">
  <h1 class="auth-title"><?= esc(t('auth.register.title')) ?></h1>
  <p class="auth-sub"><?= t('auth.register.subtitle') ?></p>

  <div class="card">
    <div class="tabs">
      <a class="tab" href="<?= site_url('/login') ?>"><?= esc(t('auth.register.signinTab')) ?></a>
      <a class="tab active" href="<?= site_url('/register') ?>"><?= esc(t('auth.register.tab')) ?></a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if (! empty($googleOn)): ?>
      <a class="btn-google" href="<?= site_url('auth/google/redirect') ?>">
        <svg class="g-icon" viewBox="0 0 24 24" aria-hidden="true">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/>
          <path fill="#FBBC05" d="M5.84 14.1A6.6 6.6 0 0 1 5.5 12c0-.73.12-1.44.34-2.1V7.06H2.18A11 11 0 0 0 1 12c0 1.78.43 3.46 1.18 4.94l3.66-2.84z"/>
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 0 0 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/>
        </svg>
        <span><?= esc(t('auth.register.google')) ?></span>
      </a>

      <div class="auth-divider"><span><?= esc(t('auth.register.orEmail')) ?></span></div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= site_url('/register') ?>" novalidate>
      <?= csrf_field() ?>

      <div>
        <label class="label" for="nama_lengkap"><?= esc(t('auth.register.nameLabel')) ?></label>
        <input class="input" type="text" id="nama_lengkap" name="nama_lengkap" value="<?= old('nama_lengkap') ?>" placeholder="<?= esc(t('auth.register.namePh')) ?>" autocomplete="name" required>
        <?php if ($validation->hasError('nama_lengkap')): ?>
          <div class="field-error"><?= esc($validation->getError('nama_lengkap')) ?></div>
        <?php endif; ?>
      </div>

      <div class="row">
        <div>
          <label class="label" for="email"><?= esc(t('auth.register.emailLabel')) ?></label>
          <input class="input" type="email" id="email" name="email" value="<?= old('email') ?>" placeholder="<?= esc(t('auth.login.emailPh')) ?>" autocomplete="email" required>
          <?php if ($validation->hasError('email')): ?>
            <div class="field-error"><?= esc($validation->getError('email')) ?></div>
          <?php endif; ?>
        </div>
        <div>
          <label class="label" for="no_telepon"><?= esc(t('auth.register.phoneLabel')) ?></label>
          <input class="input" type="tel" id="no_telepon" name="no_telepon" value="<?= old('no_telepon') ?>" placeholder="<?= esc(t('auth.register.phonePh')) ?>" autocomplete="tel" required>
          <?php if ($validation->hasError('no_telepon')): ?>
            <div class="field-error"><?= esc($validation->getError('no_telepon')) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="row">
        <div>
          <label class="label" for="password"><?= esc(t('auth.register.passLabel')) ?></label>
          <input class="input" type="password" id="password" name="password" placeholder="<?= esc(t('auth.register.passPh')) ?>" autocomplete="new-password" required>
          <?php if ($validation->hasError('password')): ?>
            <div class="field-error"><?= esc($validation->getError('password')) ?></div>
          <?php endif; ?>
        </div>
        <div>
          <label class="label" for="password_confirm"><?= esc(t('auth.register.confirmLabel')) ?></label>
          <input class="input" type="password" id="password_confirm" name="password_confirm" placeholder="<?= esc(t('auth.register.confirmPh')) ?>" autocomplete="new-password" required>
          <?php if ($validation->hasError('password_confirm')): ?>
            <div class="field-error"><?= esc($validation->getError('password_confirm')) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <button class="btn btn-primary btn-lg" type="submit">
        <span><?= esc(t('auth.register.submit')) ?></span>
        <svg class="btn-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>

      <div class="auth-foot">
        <?= esc(t('auth.register.foot')) ?> <a class="link" href="<?= site_url('/login') ?>"><?= esc(t('auth.register.footCta')) ?></a>.
        <div class="muted" style="font-size:12px;margin-top:8px;">
          <?= esc(t('auth.register.spam')) ?>
        </div>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
