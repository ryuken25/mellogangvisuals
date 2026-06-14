<?= $this->extend('layout/admin') ?>

<?= $this->section('adminContent') ?>

<div class="admin-hero">
  <div>
    <div class="admin-hero__chip"><?= \App\Support\I18n::isEn() ? 'Operations' : 'Operasional' ?></div>
    <h1 class="admin-hero__title"><?= esc(t('dashboard.admin.title')) ?></h1>
    <p class="admin-hero__sub"><?= esc(t('dashboard.admin.subtitle')) ?></p>
  </div>
  <div class="admin-hero__art">⚙️</div>
</div>

<div class="admin-kpis">
  <a class="admin-kpi" href="<?= site_url('admin/paket') ?>" style="text-decoration:none;color:inherit;">
    <div class="admin-kpi__label"><?= esc(t('dashboard.admin.kpi.packages')) ?></div>
    <div class="admin-kpi__value"><?= (int)($countPaket ?? 0) ?></div>
    <div class="admin-kpi__sub">→</div>
  </a>
  <a class="admin-kpi" href="<?= site_url('admin/portofolio') ?>" style="text-decoration:none;color:inherit;">
    <div class="admin-kpi__label"><?= esc(t('dashboard.admin.kpi.portfolio')) ?></div>
    <div class="admin-kpi__value"><?= (int)($countPorto ?? 0) ?></div>
    <div class="admin-kpi__sub">→</div>
  </a>
  <a class="admin-kpi" href="<?= site_url('admin/pemesanan') ?>" style="text-decoration:none;color:inherit;">
    <div class="admin-kpi__label"><?= esc(t('dashboard.admin.kpi.orders')) ?></div>
    <div class="admin-kpi__value"><?= (int)($countOrder ?? 0) ?></div>
    <div class="admin-kpi__sub">→</div>
  </a>
  <a class="admin-kpi" href="<?= site_url('admin/pembayaran') ?>" style="text-decoration:none;color:inherit;">
    <div class="admin-kpi__label"><?= esc(t('dashboard.admin.kpi.pending')) ?></div>
    <div class="admin-kpi__value" style="color:<?= ($pendingPay ?? 0) > 0 ? 'var(--warn)' : 'var(--text)' ?>;">
      <?= (int)($pendingPay ?? 0) ?>
    </div>
    <div class="admin-kpi__sub">→</div>
  </a>
</div>

<?php if (! empty($portos)): ?>
<section class="panel">
  <h3 class="section__title" style="margin:0 0 12px 0;"><?= \App\Support\I18n::isEn() ? 'Latest portfolio' : 'Portofolio terbaru' ?></h3>
  <div class="porto-strip">
    <?php foreach ($portos as $p): ?>
      <a class="porto-strip__item" href="<?= site_url('admin/portofolio') ?>" style="background-image:url('<?= esc($p['thumb'] ?? '') ?>');" aria-label="<?= esc($p['judul'] ?? '') ?>">
        <div class="porto-strip__overlay">
          <div class="porto-strip__title"><?= esc($p['judul'] ?? '') ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="panel">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
    <h3 class="section__title" style="margin:0;"><?= esc(t('dashboard.admin.recentOrders')) ?></h3>
    <a class="link" href="<?= site_url('admin/pemesanan') ?>"><?= esc(t('dashboard.admin.viewAll')) ?></a>
  </div>

  <?php if (empty($orders)): ?>
    <div class="empty-state">
      <div class="empty-state__art">📦</div>
      <div class="empty-state__title"><?= esc(t('dashboard.admin.noOrders')) ?></div>
    </div>
  <?php else: ?>
    <div class="order-grid">
      <?php foreach ($orders as $o):
        $st = (string)($o['status_pemesanan'] ?? '');
      ?>
        <a class="order-card" href="<?= site_url('admin/pemesanan/'.$o['id_pemesanan']) ?>">
          <div class="order-card__head">
            <span class="order-card__code"><?= esc($o['kode_pemesanan']) ?></span>
            <span class="pill status-<?= esc($o['status_color'] ?? 'muted') ?>"><?= esc($o['status_label'] ?? $st) ?></span>
          </div>
          <div class="order-card__pkg"><?= esc($o['nama_paket'] ?? '-') ?></div>
          <div class="order-card__meta">
            <span>👤 <?= esc($o['nama_lengkap'] ?? '-') ?></span>
            <span>📅 <?= esc($o['tanggal_pemesanan'] ?? '-') ?></span>
            <span>💰 Rp <?= number_format((int)($o['total_biaya'] ?? 0), 0, ',', '.') ?></span>
          </div>
          <div class="order-card__cta"><?= \App\Support\I18n::isEn() ? 'Open details →' : 'Buka detail →' ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?= $this->endSection() ?>
