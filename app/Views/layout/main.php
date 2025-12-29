<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'MellogangVisuals') ?></title>

  <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>

  <?= $this->include('layout/navbar') ?>

  <main class="page">
    <?= $this->renderSection('content') ?>
  </main>

  <?= $this->include('layout/footer') ?>

</body>
</html>
