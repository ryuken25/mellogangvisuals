<?php
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
$avatarUrl  = site_url('profile/avatar'); // endpoint baru
?>
<header class="topbar">
  <div class="topbar__inner">
    <a class="brand" href="<?= site_url('/') ?>">
      <img class="brand__logoimg"
           src="<?= base_url('assets/images/logomlg.png') ?>"
           alt="MellogangVisuals">
    </a>

    <nav class="nav">
      <a class="<?= ($path === '' ? 'active' : '') ?>" href="<?= site_url('/') ?>">Beranda</a>
      <a class="<?= (str_starts_with($path, 'katalog') ? 'active' : '') ?>" href="<?= site_url('/katalog') ?>">Paket</a>
      <a class="<?= (str_starts_with($path, 'portofolio') ? 'active' : '') ?>" href="<?= site_url('/portofolio') ?>">Portofolio</a>
      <a class="<?= (str_starts_with($path, 'status-pesanan') ? 'active' : '') ?>" href="<?= site_url('/status-pesanan') ?>">Status Pesanan</a>
      <a class="<?= (str_starts_with($path, 'kontak') ? 'active' : '') ?>" href="<?= site_url('/kontak') ?>">Kontak</a>

      <?php if ($loggedIn): ?>

        <?php if ($role === 'admin' || $role === 'editor'): ?>
          <a class="<?= (str_starts_with($path, trim($dashboardUrl,'/')) ? 'active' : '') ?>"
             href="<?= site_url($dashboardUrl) ?>">Dashboard</a>
        <?php endif; ?>

        <!-- Avatar + dropdown -->
        <div class="profileMenu" id="profileMenu">
          <button type="button" class="profileBtn" id="profileBtn" aria-haspopup="true" aria-expanded="false">
            <img class="profileAvatar" src="<?= esc($avatarUrl) ?>" alt="Profil">
          </button>

          <div class="profileDropdown" id="profileDropdown">
            <a class="profileItem" style="color:#111827" href="<?= esc($profileUrl) ?>">Profil</a>
            <a class="profileItem" style="color:#111827" href="<?= site_url('/logout') ?>">Logout</a>
          </div>
        </div>

      <?php else: ?>
        <a class="<?= (str_starts_with($path, 'login') ? 'active' : '') ?>" href="<?= site_url('/login') ?>">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<style>
/* avatar fixed 1:1 + crop */
.profileMenu{ position:relative; display:inline-flex; align-items:center; }
.profileBtn{
  border:0; background:transparent; padding:0; margin-left:6px;
  cursor:pointer;
}
.profileAvatar{
  width:38px; height:38px;
  border-radius:999px;
  object-fit:cover;
  display:block;
  border:1px solid #e5e7eb;
}

.profileDropdown{
  position:absolute;
  right:0;
  top:48px;
  min-width:160px;
  background:#fff;
  border:1px solid #e5e7eb;
  border-radius:12px;
  box-shadow:0 10px 30px rgba(0,0,0,.08);
  padding:6px;
  display:none;
  z-index:9999;
}
.profileDropdown.show{ display:block; }

.profileItem{
  display:block;
  padding:10px 10px;
  border-radius:10px;
  text-decoration:none;
}
.profileItem:hover{
  background:#e5e7eb;     
}
</style>


<script>
(function(){
  const btn = document.getElementById('profileBtn');
  const dd  = document.getElementById('profileDropdown');
  const menu= document.getElementById('profileMenu');
  if(!btn || !dd || !menu) return;

  function close(){
    dd.classList.remove('show');
    btn.setAttribute('aria-expanded','false');
  }

  btn.addEventListener('click', function(e){
    e.stopPropagation();
    const isOpen = dd.classList.toggle('show');
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  document.addEventListener('click', function(e){
    if(!menu.contains(e.target)) close();
  });

  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') close();
  });
})();
</script>
