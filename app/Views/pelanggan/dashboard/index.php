<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container">
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <!-- Hero greeting + CTA -->
  <section class="customer-hero">
    <div class="customer-hero__text">
      <div class="customer-hero__chip"><?= \App\Support\I18n::isEn() ? 'Customer dashboard' : 'Dashboard pelanggan' ?></div>
      <h1 class="customer-hero__title"><?= esc(t('dashboard.welcome', ['name' => esc($nama)])) ?></h1>
      <p class="customer-hero__sub"><?= esc(t('dashboard.customer.subtitle')) ?></p>
      <div class="customer-hero__cta">
        <a class="btn btn-primary" href="<?= site_url('pelanggan/pemesanan/buat') ?>">
          <span><?= esc(t('home.cta.viewPackages')) ?></span>
          <svg class="btn-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <a class="btnGhost" href="<?= site_url('status-pesanan') ?>"><?= esc(t('nav.status')) ?></a>
      </div>
    </div>
    <div class="customer-hero__stats">
      <div class="kpi">
        <div class="kpi__value"><?= count($orders) ?></div>
        <div class="kpi__label"><?= \App\Support\I18n::isEn() ? 'Active orders' : 'Pesanan aktif' ?></div>
      </div>
      <div class="kpi">
        <div class="kpi__value"><?= count(array_filter($orders, fn($o) => str_contains((string)($o['status_pemesanan'] ?? ''), 'revisi'))) ?></div>
        <div class="kpi__label"><?= \App\Support\I18n::isEn() ? 'In revision' : 'Direvisi' ?></div>
      </div>
      <div class="kpi">
        <div class="kpi__value"><?= count(array_filter($orders, fn($o) => str_contains((string)($o['status_pemesanan'] ?? ''), 'selesai') || str_contains((string)($o['status_pemesanan'] ?? ''), 'serah'))) ?></div>
        <div class="kpi__label"><?= \App\Support\I18n::isEn() ? 'Delivered' : 'Selesai' ?></div>
      </div>
    </div>
  </section>

  <!-- Orders table -->
  <section class="panel">
    <h3 class="section__title" style="margin:0 0 12px 0;"><?= esc(t('dashboard.customer.title')) ?></h3>

    <?php if (empty($orders)): ?>
      <div class="empty-state">
        <div class="empty-state__art">📷</div>
        <div class="empty-state__title"><?= esc(t('dashboard.customer.noOrders')) ?></div>
        <a class="btn btn-primary" href="<?= site_url('pelanggan/pemesanan/buat') ?>" style="margin-top:14px;">
          <?= esc(t('dashboard.customer.browse')) ?>
        </a>
      </div>
    <?php else: ?>
      <div class="order-grid">
        <?php foreach ($orders as $o):
          $kode = (string)($o['kode_pemesanan'] ?? '');
          $kodeUrl = site_url('status-pesanan?kode=' . urlencode($kode));
          $status = (string)($o['status_pemesanan'] ?? '');
          $st = strtolower($status);
          $showPay = ! in_array($st, ['batal','ditolak','lunas','selesai','serah_terima_hasil'], true);
        ?>
          <a class="order-card" href="<?= $kodeUrl ?>">
            <div class="order-card__head">
              <span class="order-card__code"><?= esc($kode) ?></span>
              <span class="pill status-<?= esc($o['status_color'] ?? 'muted') ?>"><?= esc($o['status_label'] ?? $status) ?></span>
            </div>
            <div class="order-card__pkg"><?= esc($o['nama_paket'] ?? '-') ?></div>
            <div class="order-card__meta">
              <span>📅 <?= esc($o['tanggal_acara'] ?? '-') ?></span>
              <span>💰 Rp <?= number_format((int)($o['total_biaya'] ?? 0), 0, ',', '.') ?></span>
            </div>
            <?php if ($showPay): ?>
              <div class="order-card__cta"><?= \App\Support\I18n::isEn() ? 'Pay or track →' : 'Bayar / lacak →' ?></div>
            <?php else: ?>
              <div class="order-card__cta"><?= \App\Support\I18n::isEn() ? 'View details →' : 'Lihat detail →' ?></div>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- Portofolio preview -->
  <?php if (! empty($porto)): ?>
    <section class="panel">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <h3 class="section__title" style="margin:0;"><?= esc(t('dashboard.customer.recommendedWork')) ?></h3>
        <a class="link" href="<?= site_url('/portofolio') ?>"><?= esc(t('dashboard.customer.viewAll')) ?></a>
      </div>
      <div class="porto-strip">
        <?php foreach ($porto as $p): ?>
          <a class="porto-strip__item" href="<?= site_url('/portofolio') ?>" style="background-image:url('<?= esc($p['thumb'] ?? '') ?>');" aria-label="<?= esc($p['judul'] ?? '') ?>">
            <div class="porto-strip__overlay">
              <div class="porto-strip__title"><?= esc($p['judul'] ?? '') ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
