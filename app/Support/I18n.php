<?php

namespace App\Support;

/**
 * Ringan i18n untuk MellogangVisuals.
 *
 * - Locale disimpan di cookie `mllang` (1 tahun).
 * - Default `en` karena UI login/teknis pakai bahasa Inggris.
 * - Dictionary inline (cukup untuk skala app ini) — kalau mau extend
 *   tinggal tambah key di $dictionaries.
 * - Helper `t('key', $params)` mengembalikan string.
 */
final class I18n
{
    public const LANG_EN = 'en';
    public const LANG_ID = 'id';
    public const COOKIE  = 'mllang';
    public const DEFAULT_LANG = self::LANG_EN;

    /** @var array<string, array<string,string>> */
    private static array $dictionaries = [];

    private static ?string $current = null;

    public static function init(): void
    {
        $cookie = (string) ($_COOKIE[self::COOKIE] ?? '');
        $lang = in_array($cookie, [self::LANG_EN, self::LANG_ID], true) ? $cookie : self::DEFAULT_LANG;
        self::set($lang);
    }

    public static function set(string $lang): void
    {
        if (! in_array($lang, [self::LANG_EN, self::LANG_ID], true)) {
            $lang = self::DEFAULT_LANG;
        }
        self::$current = $lang;
        self::loadDictionaries();
    }

    public static function get(): string
    {
        return self::$current ?? self::DEFAULT_LANG;
    }

    public static function isEn(): bool
    {
        return self::get() === self::LANG_EN;
    }

    public static function switch(): string
    {
        $next = self::isEn() ? self::LANG_ID : self::LANG_EN;
        return base_url('lang/' . $next);
    }

    public static function setCookie(string $lang): void
    {
        if (! in_array($lang, [self::LANG_EN, self::LANG_ID], true)) {
            return;
        }
        setcookie(self::COOKIE, $lang, [
            'expires'  => time() + 60 * 60 * 24 * 365,
            'path'     => '/',
            'samesite' => 'Lax',
            'httponly' => true,
        ]);
        $_COOKIE[self::COOKIE] = $lang;
    }

    /**
     * Translate. Return $key kalau tidak ada di dictionary.
     */
    public static function t(string $key, array $params = []): string
    {
        $dict = self::$dictionaries[self::get()] ?? [];
        $val  = $dict[$key] ?? $key;
        if (! empty($params)) {
            $val = strtr($val, $params);
        }
        return $val;
    }

    /**
     * Output a key as the appropriate HTML lang attribute.
     */
    public static function htmlLang(): string
    {
        return self::get();
    }

    /**
     * Inline dictionaries.
     * Key naming: <area>.<context>.<purpose> contoh "auth.login.title".
     */
    private static function loadDictionaries(): void
    {
        if (! empty(self::$dictionaries)) {
            return;
        }
        self::$dictionaries = [
            self::LANG_EN => [
                // ============== GLOBAL ==============
                'global.appName'        => 'Mellogang Visuals',
                'global.appTagline'     => 'Photo & Video Maker',
                'global.menu'           => 'Menu',
                'global.close'          => 'Close',
                'global.closeMenu'      => 'Close menu',
                'global.openMenu'       => 'Open menu',
                'global.language'       => 'Language',
                'global.login'          => 'Sign in',
                'global.logout'         => 'Sign out',
                'global.profile'        => 'Profile',
                'global.save'           => 'Save',
                'global.cancel'         => 'Cancel',
                'global.back'           => 'Back',
                'global.delete'         => 'Delete',
                'global.edit'           => 'Edit',
                'global.view'           => 'View',
                'global.open'           => 'Open',
                'global.loading'        => 'Loading…',
                'global.search'         => 'Search',
                'global.filter'         => 'Filter',
                'global.reset'          => 'Reset',
                'global.all'            => 'All',
                'global.copyright'      => '© :year MellogangVisuals. All rights reserved.',
                'global.bookNow'        => 'Book now',

                // ============== PUBLIC NAV ==============
                'nav.home'        => 'Home',
                'nav.packages'    => 'Packages',
                'nav.portfolio'   => 'Portfolio',
                'nav.status'      => 'Order status',
                'nav.contact'     => 'Contact',
                'nav.dashboard'   => 'Dashboard',

                // ============== HOME ==============
                'home.heroTitle'    => 'Photography &amp; video, made effortless.',
                'home.heroSubtitle' => 'Premium photo and video for weddings, corporate, events, and everything in between. Browse packages, explore our work, and book your date in minutes.',
                'home.cta.viewPackages' => 'View packages',
                'home.cta.yourOrders'  => 'Your orders',
                'home.cta.checkStatus'  => 'Check order status',
                'home.checkStatusHelp' => 'Track your booking with your order code.',
                'home.checkCta'        => 'Track',
                'home.popularPackages' => 'Popular packages',
                'home.viewAll'         => 'View all packages →',
                'home.recentWork'      => 'Recent work',
                'home.viewAllWork'     => 'View all portfolio →',
                'home.book'            => 'Book',

                // ============== AUTH ==============
                'auth.login.title'       => 'Sign in',
                'auth.login.subtitle'    => 'Access your bookings, track production, and download finished media.',
                'auth.login.tab'         => 'Sign in',
                'auth.login.registerTab' => 'Create account',
                'auth.login.emailLabel'  => 'Email address',
                'auth.login.emailPh'     => 'you@example.com',
                'auth.login.passLabel'   => 'Password',
                'auth.login.passPh'      => 'Enter your password',
                'auth.login.submit'      => 'Sign in',
                'auth.login.google'      => 'Continue with Google',
                'auth.login.orEmail'     => 'or sign in with email',
                'auth.login.foot'        => 'New here?',
                'auth.login.footCta'     => 'Create an account',

                'auth.register.title'    => 'Create account',
                'auth.register.subtitle' => 'Book photo &amp; video sessions, track production, and download finished media.',
                'auth.register.tab'      => 'Create account',
                'auth.register.signinTab'=> 'Sign in',
                'auth.register.nameLabel'=> 'Full name',
                'auth.register.namePh'   => 'Your full name',
                'auth.register.emailLabel'=> 'Email',
                'auth.register.phoneLabel'=> 'Phone',
                'auth.register.phonePh'  => '08xxxxxxxxxx',
                'auth.register.passLabel'=> 'Password',
                'auth.register.passPh'   => 'At least 8 characters, letters + numbers',
                'auth.register.confirmLabel'=> 'Confirm password',
                'auth.register.confirmPh'=> 'Repeat your password',
                'auth.register.submit'   => 'Create account',
                'auth.register.google'   => 'Continue with Google',
                'auth.register.orEmail'  => 'or sign up with email',
                'auth.register.foot'     => 'Already have an account?',
                'auth.register.footCta'  => 'Sign in',
                'auth.register.spam'     => "We'll send a 6-digit code to your email. Check Spam / Promotions if it doesn't arrive.",

                // ============== DASHBOARDS ==============
                'dashboard.welcome'         => 'Welcome back, :name.',
                'dashboard.admin.title'     => 'Operations overview',
                'dashboard.admin.subtitle'  => 'Latest bookings, pending payments, and production health.',
                'dashboard.admin.kpi.packages' => 'Active packages',
                'dashboard.admin.kpi.portfolio'=> 'Portfolio items',
                'dashboard.admin.kpi.orders' => 'Total bookings',
                'dashboard.admin.kpi.pending' => 'Payments pending',
                'dashboard.admin.recentOrders' => 'Recent orders',
                'dashboard.admin.viewAll'    => 'View all orders →',
                'dashboard.admin.noOrders'   => 'No orders yet.',

                'dashboard.customer.title'  => 'Your bookings',
                'dashboard.customer.subtitle'=> 'Track every order in one place.',
                'dashboard.customer.noOrders'=> "You haven't placed any order yet.",
                'dashboard.customer.browse' => 'Browse packages',
                'dashboard.customer.col.code'=> 'Code',
                'dashboard.customer.col.package'=> 'Package',
                'dashboard.customer.col.event'=> 'Event date',
                'dashboard.customer.col.status'=> 'Status',
                'dashboard.customer.col.total'=> 'Total',
                'dashboard.customer.col.action'=> 'Action',
                'dashboard.customer.pay'    => 'Pay',
                'dashboard.customer.detail' => 'Detail',
                'dashboard.customer.recommendedWork' => 'Inspiration for your next shoot',
                'dashboard.customer.viewAll' => 'View all →',

                // ============== STATUS PILL ==============
                'status.menunggu_pembayaran' => 'Awaiting payment',
                'status.menunggu_pelunasan'  => 'Awaiting balance',
                'status.menunggu_verifikasi' => 'Awaiting verification',
                'status.lunas'                => 'Paid in full',
                'status.revisi_pelanggan'     => 'Revision requested',
                'status.revisi_diproses'      => 'Revision in progress',
                'status.serah_terima_hasil'   => 'Delivery in progress',
                'status.selesai'              => 'Completed',
                'status.batal'                => 'Cancelled',
                'status.ditolak'              => 'Declined',

                // ============== ERRORS / MESSAGES ==============
                'msg.invalidCredentials' => 'Email or password is incorrect.',
                'msg.lockedOut'          => 'Account temporarily locked. We sent an unlock link to your email.',
                'msg.unverified'         => 'Please verify your email first. We just sent a fresh code.',
            ],
            self::LANG_ID => [
                'global.appName'        => 'Mellogang Visuals',
                'global.appTagline'     => 'Photo & Video Maker',
                'global.menu'           => 'Menu',
                'global.close'          => 'Tutup',
                'global.closeMenu'      => 'Tutup menu',
                'global.openMenu'       => 'Buka menu',
                'global.language'       => 'Bahasa',
                'global.login'          => 'Masuk',
                'global.logout'         => 'Keluar',
                'global.profile'        => 'Profil',
                'global.save'           => 'Simpan',
                'global.cancel'         => 'Batal',
                'global.back'           => 'Kembali',
                'global.delete'         => 'Hapus',
                'global.edit'           => 'Edit',
                'global.view'           => 'Lihat',
                'global.open'           => 'Buka',
                'global.loading'        => 'Memuat…',
                'global.search'         => 'Cari',
                'global.filter'         => 'Filter',
                'global.reset'          => 'Reset',
                'global.all'            => 'Semua',
                'global.copyright'      => '© :year MellogangVisuals. Hak cipta dilindungi.',
                'global.bookNow'        => 'Pesan sekarang',

                'nav.home'        => 'Beranda',
                'nav.packages'    => 'Paket',
                'nav.portfolio'   => 'Portofolio',
                'nav.status'      => 'Status Pesanan',
                'nav.contact'     => 'Kontak',
                'nav.dashboard'   => 'Dashboard',

                'home.heroTitle'    => 'Foto &amp; video, gampang banget.',
                'home.heroSubtitle' => 'Layanan foto dan video premium untuk wedding, corporate, event, dan kebutuhan lainnya. Lihat paket, eksplor hasil kerja, dan pesan tanggal kamu dalam hitungan menit.',
                'home.cta.viewPackages' => 'Lihat paket',
                'home.cta.yourOrders'  => 'Pesanan kamu',
                'home.cta.checkStatus'  => 'Cek status pesanan',
                'home.checkStatusHelp' => 'Lacak pesanan dengan kode pemesanan kamu.',
                'home.checkCta'        => 'Cek',
                'home.popularPackages' => 'Paket populer',
                'home.viewAll'         => 'Lihat semua paket →',
                'home.recentWork'      => 'Hasil kerja terbaru',
                'home.viewAllWork'     => 'Lihat semua portofolio →',
                'home.book'            => 'Pesan',

                'auth.login.title'       => 'Masuk',
                'auth.login.subtitle'    => 'Akses pesanan kamu, lacak produksi, dan unduh hasil akhir.',
                'auth.login.tab'         => 'Masuk',
                'auth.login.registerTab' => 'Daftar',
                'auth.login.emailLabel'  => 'Alamat email',
                'auth.login.emailPh'     => 'kamu@contoh.com',
                'auth.login.passLabel'   => 'Kata sandi',
                'auth.login.passPh'      => 'Masukkan kata sandi',
                'auth.login.submit'      => 'Masuk',
                'auth.login.google'      => 'Lanjut dengan Google',
                'auth.login.orEmail'     => 'atau masuk dengan email',
                'auth.login.foot'        => 'Baru di sini?',
                'auth.login.footCta'     => 'Buat akun',

                'auth.register.title'    => 'Buat akun',
                'auth.register.subtitle' => 'Pesan sesi foto &amp; video, lacak produksi, unduh hasil akhir.',
                'auth.register.tab'      => 'Daftar',
                'auth.register.signinTab'=> 'Masuk',
                'auth.register.nameLabel'=> 'Nama lengkap',
                'auth.register.namePh'   => 'Nama lengkap kamu',
                'auth.register.emailLabel'=> 'Email',
                'auth.register.phoneLabel'=> 'Telepon',
                'auth.register.phonePh'  => '08xxxxxxxxxx',
                'auth.register.passLabel'=> 'Kata sandi',
                'auth.register.passPh'   => 'Minimal 8 karakter, huruf + angka',
                'auth.register.confirmLabel'=> 'Konfirmasi kata sandi',
                'auth.register.confirmPh'=> 'Ulangi kata sandi',
                'auth.register.submit'   => 'Buat akun',
                'auth.register.google'   => 'Lanjut dengan Google',
                'auth.register.orEmail'  => 'atau daftar dengan email',
                'auth.register.foot'     => 'Sudah punya akun?',
                'auth.register.footCta'  => 'Masuk',
                'auth.register.spam'     => 'Kami kirim kode 6 digit ke email kamu. Cek Spam / Promosi kalau tidak masuk.',

                'dashboard.welcome'         => 'Selamat datang kembali, :name.',
                'dashboard.admin.title'     => 'Ringkasan operasional',
                'dashboard.admin.subtitle'  => 'Pesanan terbaru, pembayaran tertunda, dan kesehatan produksi.',
                'dashboard.admin.kpi.packages' => 'Paket aktif',
                'dashboard.admin.kpi.portfolio'=> 'Item portofolio',
                'dashboard.admin.kpi.orders' => 'Total pesanan',
                'dashboard.admin.kpi.pending' => 'Pembayaran tertunda',
                'dashboard.admin.recentOrders' => 'Pesanan terbaru',
                'dashboard.admin.viewAll'    => 'Lihat semua pesanan →',
                'dashboard.admin.noOrders'   => 'Belum ada pesanan.',

                'dashboard.customer.title'  => 'Pesanan kamu',
                'dashboard.customer.subtitle'=> 'Lacak semua pesanan di satu tempat.',
                'dashboard.customer.noOrders'=> 'Kamu belum punya pesanan.',
                'dashboard.customer.browse' => 'Lihat paket',
                'dashboard.customer.col.code'=> 'Kode',
                'dashboard.customer.col.package'=> 'Paket',
                'dashboard.customer.col.event'=> 'Tanggal acara',
                'dashboard.customer.col.status'=> 'Status',
                'dashboard.customer.col.total'=> 'Total',
                'dashboard.customer.col.action'=> 'Aksi',
                'dashboard.customer.pay'    => 'Bayar',
                'dashboard.customer.detail' => 'Detail',
                'dashboard.customer.recommendedWork' => 'Inspirasi untuk shoot berikutnya',
                'dashboard.customer.viewAll' => 'Lihat semua →',

                'status.menunggu_pembayaran' => 'Menunggu Pembayaran',
                'status.menunggu_pelunasan'  => 'Menunggu Pelunasan',
                'status.menunggu_verifikasi' => 'Menunggu Verifikasi',
                'status.lunas'                => 'Lunas',
                'status.revisi_pelanggan'     => 'Revisi Diminta',
                'status.revisi_diproses'      => 'Revisi Diproses',
                'status.serah_terima_hasil'   => 'Serah Terima Hasil',
                'status.selesai'              => 'Selesai',
                'status.batal'                => 'Batal',
                'status.ditolak'              => 'Ditolak',

                'msg.invalidCredentials' => 'Email atau kata sandi salah.',
                'msg.lockedOut'          => 'Akun dikunci sementara. Kami kirim link pembuka ke email kamu.',
                'msg.unverified'         => 'Verifikasi email dulu. Kami kirim kode baru.',
            ],
        ];
    }
}

/**
 * Helper global. Dipakai di view: <?= t('home.heroTitle') ?>
 */
if (! function_exists('t')) {
    function t(string $key, array $params = []): string
    {
        return I18n::t($key, $params);
    }
}
