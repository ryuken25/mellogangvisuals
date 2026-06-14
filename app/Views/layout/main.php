<!doctype html>
<html lang="<?= esc(\App\Support\I18n::htmlLang()) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'MellogangVisuals') ?></title>

  <meta name="theme-color" content="#0A0E0D">
  <meta name="description" content="MellogangVisuals — Photo &amp; Video Maker. Pesan paket foto/video, lacak status pesanan, dan unduh hasil dari drive.">
  <meta property="og:title" content="MellogangVisuals — Photo &amp; Video Maker">
  <meta property="og:description" content="Pemesanan dan pelacakan produksi foto/video MellogangVisuals.">
  <meta property="og:image" content="<?= base_url('assets/images/logomlg.png') ?>">
  <meta property="og:type" content="website">
  <link rel="icon" type="image/png" href="<?= base_url('assets/images/logomlg.png') ?>">

  <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>

  <?= $this->include('layout/navbar') ?>

  <main class="page">
    <?= $this->renderSection('content') ?>
  </main>

  <?= $this->include('layout/footer') ?>

  <script>
    // Modal auto-show: kalau controller set flash 'show_tugas_popup' (editor login)
    (function() {
      var show = <?= json_encode((bool) session()->getFlashdata('show_tugas_popup')) ?>;
      if (!show) return;
      function fire(){
        var el = document.getElementById('tugasModal');
        if (!el) return;
        if (window.bootstrap && bootstrap.Modal) {
          bootstrap.Modal.getOrCreateInstance(el).show();
        } else {
          el.classList.add('show');
          el.style.display = 'block';
        }
      }
      if (document.readyState === 'complete') fire();
      else window.addEventListener('load', fire);
    })();
  </script>
</body>
</html>
