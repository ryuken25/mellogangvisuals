<?php

namespace Config;

use CodeIgniter\Config\Services;

$routes = Services::routes();

if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Public Routes
 * --------------------------------------------------------------------
 */
$routes->get('/', 'Public\\HomeController::index');
// Profile (semua role yang login)
$routes->get('profile', 'ProfileController::index');
$routes->post('profile/update', 'ProfileController::update');
$routes->post('profile/password', 'ProfileController::password');
$routes->post('profile/photo', 'ProfileController::photo');

// Avatar image (dipakai topbar)
$routes->get('profile/avatar', 'ProfileController::avatar');

// Auth
$routes->get('login', 'AuthController::loginForm');
$routes->post('login', 'AuthController::login');
$routes->get('register', 'AuthController::registerForm');
$routes->post('register', 'AuthController::register');
$routes->get('logout', 'AuthController::logout');

// Language switcher (set cookie + redirect back)
$routes->get('lang/(:segment)', 'BaseController::setLanguage/$1');

// Verifikasi OTP (post-register)
$routes->get('auth/verify', 'AuthController::verifyForm');
$routes->post('auth/verify', 'AuthController::verify');
$routes->get('auth/verify-link', 'AuthController::verifyLink');
$routes->post('auth/resend-otp', 'AuthController::resendOtp');

// Unlock akun (link dari email)
$routes->get('auth/unlock', 'AuthController::unlock');
$routes->get('auth/unlock/(:any)', 'AuthController::unlock/$1');

// Google OAuth
$routes->get('auth/google/redirect', 'AuthController::googleRedirect');
$routes->get('auth/google/callback', 'AuthController::googleCallback');

// Public pages
$routes->get('katalog', 'Public\\KatalogController::index');
$routes->get('portofolio', 'Public\\PortofolioController::index');
$routes->get('kontak', 'Public\\KontakController::index');

// Status Pesanan (public)
$routes->get('status-pesanan', 'Public\\StatusController::index');

// Revisi request + konfirmasi selesai (POST)
$routes->post('status-pesanan/revisi/(:num)', 'Public\\StatusController::revisi/$1');
$routes->post('status-pesanan/selesai/(:num)', 'Public\\StatusController::selesai/$1');

// Public file preview download
$routes->get('status-pesanan/file/(:num)/(:segment)', 'Public\\StatusController::file/$1/$2');

// Invoice
$routes->get('invoice/(:segment)', 'Public\\InvoiceController::show/$1');

/*
 * --------------------------------------------------------------------
 * PELANGGAN (after login)
 * --------------------------------------------------------------------
 */
$routes->group('pelanggan', ['filter' => 'role:pelanggan'], function ($routes) {
    $routes->get('/', 'Pelanggan\\DashboardController::index');
    $routes->get('dashboard', 'Pelanggan\\DashboardController::index');

    $routes->get('pemesanan/buat', 'Pelanggan\\PemesananController::create');
    $routes->get('pemesanan/buat/(:num)', 'Pelanggan\\PemesananController::create/$1', ['as' => 'pelanggan-pesan']);
    $routes->post('pemesanan/simpan', 'Pelanggan\\PemesananController::store');

    // availability tanggal (2 fotografer) buat pelanggan
    $routes->get('pemesanan/availability', 'Pelanggan\\PemesananController::availability');

    $routes->get('pembayaran/upload/(:num)', 'Pelanggan\\PembayaranController::create/$1');
    $routes->post('pembayaran/upload/(:num)', 'Pelanggan\\PembayaranController::store/$1');

    $routes->get('pembayaran/ganti/(:num)', 'Pelanggan\\PembayaranController::edit/$1');
    $routes->post('pembayaran/ganti/(:num)', 'Pelanggan\\PembayaranController::update/$1');

    $routes->get('pembayaran/riwayat/(:num)', 'Pelanggan\\PembayaranController::riwayat/$1');
    $routes->get('pembayaran/file/(:num)', 'Pelanggan\\PembayaranController::file/$1');
});

/*
 * --------------------------------------------------------------------
 * ADMIN
 * --------------------------------------------------------------------
 */
$routes->group('admin', ['filter' => 'role:admin'], static function ($routes) {
    $routes->get('/', 'Admin\\DashboardController::index');

    // Paket CRUD
    $routes->get('paket', 'Admin\\PaketController::index');
    $routes->get('paket/create', 'Admin\\PaketController::create');
    $routes->post('paket', 'Admin\\PaketController::store');
    $routes->get('paket/edit/(:num)', 'Admin\\PaketController::edit/$1');
    $routes->post('paket/update/(:num)', 'Admin\\PaketController::update/$1');
    $routes->post('paket/delete/(:num)', 'Admin\\PaketController::delete/$1');

    // Portofolio CRUD
    $routes->get('portofolio', 'Admin\\PortofolioController::index');
    $routes->get('portofolio/create', 'Admin\\PortofolioController::create');
    $routes->post('portofolio', 'Admin\\PortofolioController::store');
    $routes->get('portofolio/edit/(:num)', 'Admin\\PortofolioController::edit/$1');
    $routes->post('portofolio/update/(:num)', 'Admin\\PortofolioController::update/$1');
    $routes->post('portofolio/delete/(:num)', 'Admin\\PortofolioController::delete/$1');

    // Pemesanan
    $routes->get('pemesanan', 'Admin\\PemesananController::index');
    $routes->get('pemesanan/(:num)', 'Admin\\PemesananController::show/$1');

    // delete pemesanan (confirm di UI)
    $routes->post('pemesanan/delete/(:num)', 'Admin\\PemesananController::delete/$1');

    // invoice admin
    $routes->get('pemesanan/invoice/(:num)', 'Admin\\PemesananController::invoice/$1');

    // Pembayaran
    $routes->get('pembayaran', 'Admin\\PembayaranController::index');
    $routes->get('pembayaran/verify/(:num)', 'Admin\\PembayaranController::verifyForm/$1');
    $routes->post('pembayaran/verify/(:num)', 'Admin\\PembayaranController::verify/$1');
    $routes->get('pembayaran/file/(:num)', 'Admin\\PembayaranController::file/$1');

    // Jadwal
    $routes->get('jadwal', 'Admin\\JadwalProduksiController::index');
    $routes->get('jadwal/create', 'Admin\\JadwalProduksiController::create');
    $routes->post('jadwal', 'Admin\\JadwalProduksiController::store');
    $routes->get('jadwal/edit/(:num)', 'Admin\\JadwalProduksiController::edit/$1');
    $routes->post('jadwal/update/(:num)', 'Admin\\JadwalProduksiController::update/$1');

    // availability untuk admin form jadwal
    $routes->get('jadwal/availability', 'Admin\\JadwalProduksiController::availability');

    // Laporan + Export CSV
    $routes->get('laporan', 'Admin\\LaporanController::index');
    $routes->get('laporan/export/pembayaran', 'Admin\\LaporanController::exportPembayaran');
    $routes->get('laporan/export/pengeluaran', 'Admin\\LaporanController::exportPengeluaran');
    $routes->get('laporan/export/pembayaran-all', 'Admin\\LaporanController::exportPembayaranAll');
    $routes->get('laporan/export/pembayaran-pending', 'Admin\\LaporanController::exportPembayaranPending');
    $routes->get('laporan/export/pembayaran-valid', 'Admin\\LaporanController::exportPembayaranValid');

    $routes->post('laporan/pengeluaran', 'Admin\\LaporanController::storePengeluaran');
    $routes->post('laporan/pengeluaran/update/(:num)', 'Admin\\LaporanController::updatePengeluaran/$1');
    $routes->post('laporan/pengeluaran/delete/(:num)', 'Admin\\LaporanController::deletePengeluaran/$1');

    // Social fetcher (admin only)
    $routes->get('social', 'Admin\\SocialController::index');
    $routes->post('social/fetch', 'Admin\\SocialController::fetch');
    $routes->post('social/upsert', 'Admin\\SocialController::upsert');
    $routes->get('social/status/(:num)', 'Admin\\SocialController::status/$1');
    $routes->post('social/feature/(:num)', 'Admin\\SocialController::feature/$1');
    $routes->get('social/cache', 'Admin\\SocialController::cache');

    // Users
    $routes->get('users', 'Admin\\UsersController::index');
    $routes->get('users/edit/(:num)', 'Admin\\UsersController::edit/$1');
    $routes->post('users/update/(:num)', 'Admin\\UsersController::update/$1');
    $routes->post('users/delete/(:num)', 'Admin\\UsersController::delete/$1');
});

/*
 * --------------------------------------------------------------------
 * EDITOR
 * --------------------------------------------------------------------
 */
$routes->group('editor', ['filter' => 'role:editor'], static function ($routes) {
    $routes->get('/', 'Editor\\DashboardController::index');

    $routes->get('tugas', 'Editor\\TugasController::index');
    $routes->get('tugas/(:num)', 'Editor\\TugasController::show/$1');
    $routes->post('tugas/update/(:num)', 'Editor\\TugasController::update/$1');
    $routes->get('tugas/file/(:num)/(:segment)', 'Editor\\TugasController::file/$1/$2');

    // Terima / Tolak revisi (id_jadwal)
    $routes->post('tugas/revisi/accept/(:num)', 'Editor\\TugasController::acceptRevisi/$1');
    $routes->post('tugas/revisi/reject/(:num)', 'Editor\\TugasController::rejectRevisi/$1');
});

if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
