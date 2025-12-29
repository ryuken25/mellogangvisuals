<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container">
  <div class="panel">
    <h2 class="section__title">Portofolio</h2>
    <p class="auth-sub">Kumpulan hasil karya photo & video MellogangVisuals.</p>

    <?php if (empty($items)): ?>
      <div class="alert ok">Belum ada portofolio.</div>
    <?php else: ?>
      <div class="listGrid4">
        <?php foreach ($items as $po): ?>
          <a class="portoCard3" href="<?= esc($po['url_media'] ?? '#') ?>" target="_blank" rel="noopener">
            <div class="portoCard3__thumb"
                 style="background-image:url('<?= esc($po['thumb'] ?? base_url('assets/images/porto_placeholder.png')) ?>');">
            </div>

            <div class="portoCard3__body">
              <div class="portoCard3__title"><?= esc($po['judul']) ?></div>

              <div class="portoCard3__meta">
                <span class="pill"><?= esc($po['kategori']) ?></span>
                <?php if (!empty($po['id_paket']) && isset($paketMap[$po['id_paket']])): ?>
                  <span class="muted"><?= esc($paketMap[$po['id_paket']]) ?></span>
                <?php endif; ?>
              </div>

              <?php if (!empty($po['deskripsi'])): ?>
                <div class="portoCard3__desc"><?= esc($po['deskripsi']) ?></div>
              <?php endif; ?>

              <?php if (!empty($po['tanggal_publikasi'])): ?>
                <div class="muted" style="margin-top:8px;"><?= esc($po['tanggal_publikasi']) ?></div>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
.portoCard3{
  display:block;
  border:1px solid #e5e7eb;
  border-radius:16px;
  overflow:hidden;
  text-decoration:none;
  color:inherit;
  background:#fff;
  transition:transform .12s ease, box-shadow .12s ease;
}
.portoCard3:hover{
  transform:translateY(-2px);
  box-shadow:0 10px 30px rgba(0,0,0,.08);
}
.portoCard3__thumb{
  width:100%;
  aspect-ratio: 16/10;
  background-size:cover;
  background-position:center;
  background-color:#f3f4f6;
}
.portoCard3__body{ padding:12px; }
.portoCard3__title{
  font-weight:700;
  margin-bottom:8px;
}
.portoCard3__meta{
  display:flex;
  gap:8px;
  align-items:center;
  flex-wrap:wrap;
}
.portoCard3__desc{
  margin-top:8px;
  color:#374151;
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow:hidden;
}
</style>

<?= $this->endSection() ?>
