<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'MellogangVisuals') ?></title>

  <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>

<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
  <div class="container adminShell">
    <aside class="adminShell__sidebar">
      <?= $this->include('admin/_sidebar') ?>
    </aside>

    <section class="adminShell__content">
      <?= $this->renderSection('adminContent') ?>
    </section>
  </div>
<?= $this->endSection() ?>


</body>
</html>
