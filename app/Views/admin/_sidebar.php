<?php
$path = service('uri')->getPath();

function isActive($path, $needle) {
  // active kalau path sama persis atau diawali (admin/paket/...)
  return $path === $needle || str_starts_with($path, $needle . '/');
}
?>
<div class="adminSide">
  <div class="adminSide__title">Dashboard</div>

  <nav class="adminNav">
    <a class="<?= isActive($path, 'admin') ? 'active' : '' ?>" href="<?= site_url('admin') ?>">Overview</a>
    <a class="<?= isActive($path, 'admin/pemesanan') ? 'active' : '' ?>" href="<?= site_url('admin/pemesanan') ?>">Pemesanan</a>
    <a class="<?= isActive($path, 'admin/jadwal') ? 'active' : '' ?>" href="<?= site_url('admin/jadwal') ?>">Jadwal</a>
    <a class="<?= isActive($path, 'admin/paket') ? 'active' : '' ?>" href="<?= site_url('admin/paket') ?>">Paket</a>
    <a class="<?= isActive($path, 'admin/portofolio') ? 'active' : '' ?>" href="<?= site_url('admin/portofolio') ?>">Portofolio</a>
    <a class="<?= isActive($path, 'admin/pembayaran') ? 'active' : '' ?>" href="<?= site_url('admin/pembayaran') ?>">Pembayaran</a>
    <a class="<?= url_is('admin/users*') ? 'active' : '' ?>" href="<?= site_url('admin/users') ?>">Users</a>
    <a class="<?= isActive($path, 'admin/laporan') ? 'active' : '' ?>" href="<?= site_url('admin/laporan') ?>">Laporan</a>

    <div class="adminNav__divider"></div>
    <a href="<?= site_url('logout') ?>">Logout</a>
  </nav>
</div>
