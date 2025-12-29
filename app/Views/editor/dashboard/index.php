<?= $this->extend('layout/editor') ?>
<?= $this->section('editorContent') ?>

<h2 class="section__title">Dashboard Editor</h2>
<p class="auth-sub">Ringkasan tugas berdasarkan status produksi.</p>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="adminCards" style="margin-top:12px;">
  <a class="adminCard" href="<?= site_url('editor/tugas?status=cut-to-cut') ?>" style="text-decoration:none;">
    <div class="adminCard__label">A — Cut-to-cut (baru)</div>
    <div class="adminCard__value"><?= (int)($countA ?? 0) ?></div>
  </a>

  <a class="adminCard" href="<?= site_url('editor/tugas?status=finishing') ?>" style="text-decoration:none;">
    <div class="adminCard__label">B — Finishing</div>
    <div class="adminCard__value"><?= (int)($countB ?? 0) ?></div>
  </a>

  <a class="adminCard" href="<?= site_url('editor/tugas?status=revisi') ?>" style="text-decoration:none;">
    <div class="adminCard__label">C — Revisi</div>
    <div class="adminCard__value"><?= (int)($countC ?? 0) ?></div>
  </a>

  <a class="adminCard" href="<?= site_url('editor/tugas?status=done') ?>" style="text-decoration:none;">
    <div class="adminCard__label">D — Done</div>
    <div class="adminCard__value"><?= (int)($countD ?? 0) ?></div>
  </a>
</div>

<div class="panel" style="margin-top:16px;">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
    <div>
      <h3 class="section__title" style="margin:0;">Pemesanan terbaru</h3>
      <div class="muted" style="margin-top:6px;">Total tugas kamu: <b><?= (int)($totalTugas ?? 0) ?></b></div>
    </div>
    <a class="link" href="<?= site_url('editor/tugas') ?>">Lihat semua →</a>
  </div>

  <div style="height:10px;"></div>

  <?php if (empty($rows)): ?>
    <div class="alert ok">Belum ada tugas untuk kamu.</div>
  <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Pelanggan</th>
          <th>Tanggal Acara</th>
          <th>Paket</th>
          <th>Status Produksi</th>
          <th>Jadwal Editing</th>
          <th style="width:160px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= esc($r['kode_pemesanan'] ?? '-') ?></td>
            <td><?= esc($r['nama_pelanggan'] ?? '-') ?></td>
            <td><?= esc($r['tanggal_acara'] ?? '-') ?></td>
            <td><?= esc($r['nama_paket'] ?? '-') ?></td>
            <td><span class="pill"><?= esc($r['status_produksi'] ?? '-') ?></span></td>
            <td><?= esc($r['tanggal_mulai_editing'] ?? '-') ?> → <?= esc($r['tanggal_selesai_editing'] ?? '-') ?></td>
            <td>
              <a class="btnPrimary" style="padding:8px 12px;" href="<?= site_url('editor/tugas/'.$r['id_jadwal']) ?>">
                Lihat Progres
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
