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

<!-- Modal Pop-up Notifikasi Project -->
<div class="modal fade" id="tugasModal" tabindex="-1" aria-labelledby="tugasModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" style="border:none;border-radius:18px;overflow:hidden;box-shadow:0 20px 60px rgba(14,32,48,.18);">

      <!-- Header — clean white -->
      <div class="modal-header" style="background:#fff;border-bottom:1px solid var(--line);padding:20px 24px;">
        <h5 class="modal-title" id="tugasModalLabel" style="color:var(--dark);font-weight:900;font-size:18px;margin:0;">
          Notifikasi Tugas
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <!-- Tab Navigation — solid block style -->
      <div class="tugasTabBar">
        <button class="tugasTab tugasTab--active" onclick="switchTab('berlangsung')" id="tabBerlangsung">
          Sedang Berlangsung
          <?php if (!empty($tugasBerlangsung)): ?>
            <span class="tugasTab__badge"><?= count($tugasBerlangsung) ?></span>
          <?php endif; ?>
        </button>
        <button class="tugasTab" onclick="switchTab('mendatang')" id="tabMendatang">
          Mendatang
          <?php if (!empty($tugasMendatang)): ?>
            <span class="tugasTab__badge"><?= count($tugasMendatang) ?></span>
          <?php endif; ?>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body" style="padding:16px 20px;max-height:420px;overflow-y:auto;background:var(--bg);">

        <!-- Tab: Sedang Berlangsung -->
        <div id="panelBerlangsung">
          <?php if (empty($tugasBerlangsung)): ?>
            <div style="text-align:center;padding:30px 0;color:var(--muted);">
              <div style="font-size:2rem;margin-bottom:8px;">---</div>
              <div style="font-size:13px;">Tidak ada project yang sedang berlangsung.</div>
            </div>
          <?php else: ?>
            <?php foreach ($tugasBerlangsung as $t):
              $tglDeadline = $t['tanggal_selesai_editing'] ?? null;
              $status = strtolower(trim($t['status_produksi'] ?? ''));
              $sisaHari = $tglDeadline ? (int)((strtotime($tglDeadline) - strtotime($today)) / 86400) : null;

              // Warna pill status
              if ($status === 'revisi') { $pillBg = '#fee2e2'; $pillColor = '#991b1b'; }
              elseif ($status === 'finishing') { $pillBg = '#fef9c3'; $pillColor = '#854d0e'; }
              else { $pillBg = 'rgba(19,226,183,.12)'; $pillColor = '#0f766e'; }

              // Warna deadline
              if ($sisaHari !== null && $sisaHari < 0) { $deadlineColor = '#dc2626'; }
              elseif ($sisaHari !== null && $sisaHari <= 1) { $deadlineColor = '#dc2626'; }
              elseif ($sisaHari !== null && $sisaHari <= 3) { $deadlineColor = '#ca8a04'; }
              else { $deadlineColor = '#0f766e'; }
            ?>
              <div class="tugasCard">
                <div class="tugasCard__info">
                  <div class="tugasCard__title"><?= esc($t['kode_pemesanan'] ?? '-') ?></div>
                  <div class="tugasCard__meta">
                    <?= esc($t['nama_pelanggan'] ?? '-') ?> &middot; <?= esc($t['nama_paket'] ?? '-') ?>
                  </div>
                  <div style="margin-top:6px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span class="tugasCard__pill" style="background:<?= $pillBg ?>;color:<?= $pillColor ?>;">
                      <?= esc($t['status_produksi'] ?? '-') ?>
                    </span>
                    <?php if ($tglDeadline): ?>
                      <span style="font-size:11px;font-weight:600;color:var(--muted);">
                        Deadline: <?= esc($tglDeadline) ?>
                      </span>
                      <span style="font-size:11px;font-weight:700;color:<?= $deadlineColor ?>;">
                        <?php if ($sisaHari !== null && $sisaHari < 0): ?>
                          — Kamu telat <?= abs($sisaHari) ?> hari
                        <?php elseif ($sisaHari === 0): ?>
                          — Deadline hari ini!
                        <?php elseif ($sisaHari !== null && $sisaHari === 1): ?>
                          — Sisa 1 hari
                        <?php elseif ($sisaHari !== null): ?>
                          — Sisa <?= $sisaHari ?> hari
                        <?php endif; ?>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
                <a href="<?= site_url('editor/tugas/'.$t['id_jadwal']) ?>" class="tugasCard__btn">
                  Lihat Progres
                </a>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Tab: Mendatang -->
        <div id="panelMendatang" style="display:none;">
          <?php if (empty($tugasMendatang)): ?>
            <div style="text-align:center;padding:30px 0;color:var(--muted);">
              <div style="font-size:13px;">Belum ada project mendatang.</div>
            </div>
          <?php else: ?>
            <?php foreach ($tugasMendatang as $t):
              $tglMulai = $t['tanggal_mulai_editing'] ?? null;
              $tglDeadline = $t['tanggal_selesai_editing'] ?? null;
              $status = strtolower(trim($t['status_produksi'] ?? ''));

              if ($status === \App\Support\Status::PROD_SHOOTING) { $pillBg = '#fef9c3'; $pillColor = '#854d0e'; }
              elseif ($status === \App\Support\Status::PROD_PRA_PRODUKSI) { $pillBg = '#e5e7eb'; $pillColor = '#374151'; }
              else { $pillBg = 'rgba(19,226,183,.12)'; $pillColor = '#0f766e'; }
            ?>
              <div class="tugasCard">
                <div class="tugasCard__info">
                  <div class="tugasCard__title"><?= esc($t['kode_pemesanan'] ?? '-') ?></div>
                  <div class="tugasCard__meta">
                    <?= esc($t['nama_pelanggan'] ?? '-') ?> &middot; <?= esc($t['nama_paket'] ?? '-') ?>
                  </div>
                  <div style="margin-top:6px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span class="tugasCard__pill" style="background:<?= $pillBg ?>;color:<?= $pillColor ?>;">
                      <?= esc($t['status_produksi'] ?? '-') ?>
                    </span>
                    <?php if ($tglMulai): ?>
                      <span style="font-size:11px;color:var(--muted);font-weight:600;">
                        Mulai: <?= esc($tglMulai) ?>
                      </span>
                    <?php endif; ?>
                    <?php if ($tglDeadline): ?>
                      <span style="font-size:11px;color:var(--muted);font-weight:600;">
                        Deadline: <?= esc($tglDeadline) ?>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
                <a href="<?= site_url('editor/tugas/'.$t['id_jadwal']) ?>" class="tugasCard__btn tugasCard__btn--outline">
                  Lihat Progres
                </a>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      </div>

      <!-- Footer -->
      <div class="modal-footer" style="background:#fff;border-top:1px solid var(--line);padding:14px 20px;">
        <button type="button" class="btnGhost" style="padding:10px 18px;font-size:13px;" data-bs-dismiss="modal">Tutup</button>
        <a href="<?= site_url('editor/tugas') ?>" class="btnPrimary" style="padding:10px 18px;font-size:13px;">Lihat Semua Tugas</a>
      </div>

    </div>
  </div>
</div>

<style>
  /* ===== Tab Bar: solid block style ===== */
  .tugasTabBar {
    display: flex;
    gap: 0;
    background: #fff;
    padding: 12px 20px 0 20px;
  }
  .tugasTab {
    flex: 1;
    padding: 10px 16px;
    border: none;
    border-radius: 10px 10px 0 0;
    font-weight: 800;
    font-size: 13px;
    cursor: pointer;
    transition: all .2s;
    background: #e5e7eb;
    color: var(--muted);
    text-align: center;
  }
  .tugasTab--active {
    background: linear-gradient(135deg, var(--dark), var(--dark2)) !important;
    color: #fff !important;
  }
  .tugasTab:hover:not(.tugasTab--active) {
    background: #d1d5db;
    color: var(--dark);
  }
  .tugasTab__badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 900;
    padding: 1px 7px;
    border-radius: 999px;
    margin-left: 5px;
    vertical-align: middle;
  }
  .tugasTab--active .tugasTab__badge {
    background: rgba(255,255,255,.2);
    color: #fff;
  }
  .tugasTab:not(.tugasTab--active) .tugasTab__badge {
    background: #d1d5db;
    color: var(--muted);
  }

  /* ===== Task Card ===== */
  .tugasCard {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
  }
  .tugasCard__info {
    flex: 1;
    min-width: 0;
  }
  .tugasCard__title {
    font-weight: 800;
    font-size: 14px;
    color: var(--dark);
  }
  .tugasCard__meta {
    font-size: 12px;
    color: var(--muted);
    margin-top: 2px;
  }
  .tugasCard__pill {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
  }
  .tugasCard__btn {
    flex-shrink: 0;
    display: inline-block;
    padding: 8px 18px;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--mint), var(--mint2));
    color: var(--dark);
    font-weight: 800;
    font-size: 12px;
    text-decoration: none;
    white-space: nowrap;
    transition: opacity .2s;
  }
  .tugasCard__btn:hover { opacity: .85; }
  .tugasCard__btn--outline {
    background: #fff;
    border: 1.5px solid var(--line);
    color: var(--dark);
  }
  .tugasCard__btn--outline:hover {
    background: var(--bg);
    opacity: 1;
  }
</style>

<script>
  function switchTab(tab) {
    var panelB = document.getElementById('panelBerlangsung');
    var panelM = document.getElementById('panelMendatang');
    var tabB = document.getElementById('tabBerlangsung');
    var tabM = document.getElementById('tabMendatang');

    if (tab === 'berlangsung') {
      panelB.style.display = '';
      panelM.style.display = 'none';
      tabB.classList.add('tugasTab--active');
      tabM.classList.remove('tugasTab--active');
    } else {
      panelB.style.display = 'none';
      panelM.style.display = '';
      tabM.classList.add('tugasTab--active');
      tabB.classList.remove('tugasTab--active');
    }
  }
</script>


<div class="adminCards" style="margin-top:12px;">
  <a class="adminCard" href="<?= site_url('editor/tugas?status=' . \App\Support\Status::PROD_CUT_TO_CUT) ?>" style="text-decoration:none;">
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
