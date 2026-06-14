<?php
use App\Support\I18n;

$uri = service('uri');
$path = $uri->getPath();
$loggedIn = (bool) session()->get('logged_in');
$role = (string) session()->get('role');

$dashboardUrl = '/';
if ($loggedIn) {
  if ($role === 'admin') $dashboardUrl = '/admin';
  elseif ($role === 'editor') $dashboardUrl = '/editor';
}

$profileUrl = site_url('profile');
$avatarUrl  = site_url('profile/avatar');

$navItems = [];
if ($role !== 'editor') {
  $navItems[] = ['label' => t('nav.home'),        'url' => site_url('/'),                'match' => $path === ''];
  $navItems[] = ['label' => t('nav.packages'),    'url' => site_url('/katalog'),         'match' => str_starts_with($path, 'katalog')];
  $navItems[] = ['label' => t('nav.portfolio'),   'url' => site_url('/portofolio'),      'match' => str_starts_with($path, 'portofolio')];
  $navItems[] = ['label' => t('nav.status'),      'url' => site_url('/status-pesanan'),  'match' => str_starts_with($path, 'status-pesanan')];
  $navItems[] = ['label' => t('nav.contact'),     'url' => site_url('/kontak'),          'match' => str_starts_with($path, 'kontak')];
}
if ($loggedIn && ($role === 'admin' || $role === 'editor')) {
  $navItems[] = [
    'label' => t('nav.dashboard'),
    'url'   => site_url($dashboardUrl),
    'match' => str_starts_with($path, trim($dashboardUrl, '/')),
  ];
}

$langOther = I18n::isEn() ? 'id' : 'en';
$langOtherLabel = $langOther === 'en' ? 'EN' : 'ID';
?>
<header class="topbar">
  <div class="topbar__inner">
    <a class="brand" href="<?= site_url('/') ?>">
      <img class="brand__logoimg"
           src="<?= base_url('assets/images/logomlg.png') ?>"
           alt="MellogangVisuals">
      <span class="brand__name">Mellogang Visuals</span>
    </a>

    <!-- Desktop nav -->
    <nav class="nav nav--desktop" aria-label="Primary">
      <?php foreach ($navItems as $item): ?>
        <a class="<?= ! empty($item['match']) ? 'active' : '' ?>" href="<?= $item['url'] ?>"><?= esc($item['label']) ?></a>
      <?php endforeach; ?>

      <!-- Language switcher -->
      <a class="langSwitch" href="<?= site_url('lang/' . $langOther) ?>" rel="nofollow" aria-label="<?= esc(t('global.language')) ?>">
        <svg class="langSwitch__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>
        <span><?= $langOtherLabel ?></span>
      </a>

      <?php if ($loggedIn): ?>
        <div class="profileMenu" id="profileMenu">
          <button type="button" class="profileBtn" id="profileBtn" aria-haspopup="true" aria-expanded="false" aria-label="<?= esc(t('global.profile')) ?>">
            <img class="profileAvatar" src="<?= esc($avatarUrl) ?>" alt="Profil">
          </button>
          <div class="profileDropdown" id="profileDropdown" role="menu">
            <a class="profileItem" href="<?= esc($profileUrl) ?>" role="menuitem"><?= esc(t('global.profile')) ?></a>
            <a class="profileItem" href="<?= site_url('/logout') ?>" role="menuitem"><?= esc(t('global.logout')) ?></a>
          </div>
        </div>
      <?php else: ?>
        <a class="navLogin" href="<?= site_url('/login') ?>"><?= esc(t('global.login')) ?></a>
      <?php endif; ?>
    </nav>

    <!-- Mobile toggle -->
    <button type="button" class="navToggle" id="navToggle" aria-label="<?= esc(t('global.openMenu')) ?>" aria-controls="navMobile" aria-expanded="false">
      <span class="navToggle__bar"></span>
      <span class="navToggle__bar"></span>
      <span class="navToggle__bar"></span>
    </button>
  </div>

  <!-- Mobile drawer -->
  <div class="navBackdrop" id="navBackdrop" hidden></div>
  <aside class="navDrawer" id="navMobile" aria-label="<?= esc(t('global.menu')) ?>" aria-hidden="true">
    <div class="navDrawer__head">
      <span class="navDrawer__title"><?= esc(t('global.menu')) ?></span>
      <button type="button" class="navDrawer__close" id="navClose" aria-label="<?= esc(t('global.closeMenu')) ?>">&times;</button>
    </div>

    <nav class="navDrawer__list">
      <?php foreach ($navItems as $item): ?>
        <a class="navDrawer__link <?= ! empty($item['match']) ? 'active' : '' ?>" href="<?= $item['url'] ?>"><?= esc($item['label']) ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="navDrawer__foot">
      <a class="langSwitch langSwitch--block" href="<?= site_url('lang/' . $langOther) ?>" rel="nofollow">
        <svg class="langSwitch__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>
        <span>Switch to <?= $langOtherLabel ?></span>
      </a>
      <?php if ($loggedIn): ?>
        <a class="navDrawer__link" href="<?= esc($profileUrl) ?>"><?= esc(t('global.profile')) ?></a>
        <a class="navDrawer__link navDrawer__link--danger" href="<?= site_url('/logout') ?>"><?= esc(t('global.logout')) ?></a>
      <?php else: ?>
        <a class="navDrawer__link navDrawer__link--primary" href="<?= site_url('/login') ?>"><?= esc(t('global.login')) ?></a>
      <?php endif; ?>
    </div>
  </aside>
</header>

<script>
(function () {
  const toggle = document.getElementById('navToggle');
  const drawer = document.getElementById('navMobile');
  const close  = document.getElementById('navClose');
  const back   = document.getElementById('navBackdrop');
  if (!toggle || !drawer) return;

  function open() {
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    if (back) { back.hidden = false; back.classList.add('is-open'); }
    toggle.classList.add('is-active');
    toggle.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }
  function shut() {
    drawer.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    if (back) { back.classList.remove('is-open'); back.hidden = true; }
    toggle.classList.remove('is-active');
    toggle.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }
  toggle.addEventListener('click', function () {
    if (drawer.classList.contains('is-open')) shut(); else open();
  });
  if (close) close.addEventListener('click', shut);
  if (back)  back.addEventListener('click', shut);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && drawer.classList.contains('is-open')) shut();
  });
  drawer.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', shut);
  });

  const profileBtn = document.getElementById('profileBtn');
  const profileDd  = document.getElementById('profileDropdown');
  const profileMenu = document.getElementById('profileMenu');
  if (profileBtn && profileDd && profileMenu) {
    profileBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      const open = profileDd.classList.toggle('show');
      profileBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.addEventListener('click', function (e) {
      if (!profileMenu.contains(e.target)) {
        profileDd.classList.remove('show');
        profileBtn.setAttribute('aria-expanded', 'false');
      }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        profileDd.classList.remove('show');
        profileBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }
})();
</script>
