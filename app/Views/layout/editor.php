<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
  <div class="container adminShell">
    <aside class="adminShell__sidebar">
      <?= $this->include('editor/_sidebar') ?>
    </aside>

    <section class="adminShell__content">
      <?= $this->renderSection('editorContent') ?>
    </section>
  </div>
<?= $this->endSection() ?>
