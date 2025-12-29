<div class="panel" style="padding:14px;">
  <div class="section__title" style="margin-bottom:10px;">Menu Editor</div>

  <?php
    $uri = service('uri');
    $p1 = $uri->getSegment(1); // editor
    $p2 = $uri->getSegment(2); // tugas, dll
    $isDash = ($p1 === 'editor' && ($p2 === null || $p2 === ''));
    $isTugas = ($p1 === 'editor' && $p2 === 'tugas');
  ?>

  <div style="display:flex;flex-direction:column;gap:8px;">
    <a class="<?= $isDash ? 'btnPrimary' : 'btnGhost' ?>" href="<?= site_url('editor') ?>">
      Ringkasan tugas
    </a>

    <a class="<?= $isTugas ? 'btnPrimary' : 'btnGhost' ?>" href="<?= site_url('editor/tugas') ?>">
      Proyek saya
    </a>

  <div style="margin-top:12px;" class="muted">
    Progres editing aktif setelah admin set status produksi ke <b>cut-to-cut</b>.
  </div>
</div>
