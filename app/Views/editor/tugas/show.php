<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
    <h2 class="section__title">Detail Tugas</h2>
    <a class="btnGhost" href="<?= site_url('editor/tugas') ?>">Kembali</a>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="panel" style="margin-top:12px;">
    <div><b>Kode:</b> <?= esc($job['kode_pemesanan'] ?? '-') ?></div>
    <div><b>Status Produksi:</b> <span class="pill"><?= esc($job['status_produksi'] ?? '-') ?></span></div>
    <div><b>Status Pesanan:</b> <span class="pill"><?= esc($job['status_pemesanan'] ?? '-') ?></span></div>
    <div><b>Pelanggan:</b> <?= esc($job['nama_pelanggan'] ?? '-') ?> (<?= esc($job['no_telepon'] ?? '-') ?>)</div>
    <div><b>Paket:</b> <?= esc($job['nama_paket'] ?? '-') ?> (<?= esc($job['kategori'] ?? '-') ?>)</div>
    <div><b>Tanggal Acara:</b> <?= esc($job['tanggal_acara'] ?? '-') ?></div>
    <div><b>Lokasi:</b> <?= esc($job['lokasi_acara'] ?? '-') ?></div>

    <hr style="border:0;border-top:1px solid #e5e7eb;margin:14px 0;">

    <div><b>Shooting:</b> <?= esc($job['tanggal_shooting'] ?? '-') ?>
      (<?= esc($job['jam_mulai_shooting'] ?? '-') ?> - <?= esc($job['jam_selesai_shooting'] ?? '-') ?>)
    </div>
    <div><b>Editing:</b> <?= esc($job['tanggal_mulai_editing'] ?? '-') ?> → <?= esc($job['tanggal_selesai_editing'] ?? '-') ?></div>

    <?php if (!empty($job['catatan_pelanggan'])): ?>
      <div style="margin-top:10px;">
        <b>Catatan Pelanggan:</b><br>
        <pre style="white-space:pre-wrap;margin:0;"><?= esc($job['catatan_pelanggan']) ?></pre>
      </div>
    <?php endif; ?>
  </div>

  <div style="height:12px;"></div>

  <!-- Revisi panel -->
  <?php if (!empty($canAcceptReject)): ?>
    <div class="panel">
      <div class="alert error">
        Ada <b>request revisi</b> dari pelanggan. Pilih tindakan:
      </div>

      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px;">
        <form method="post" action="<?= site_url('editor/tugas/revisi/accept/' . (int)$job['id_jadwal']) ?>"
              onsubmit="return confirm('Terima revisi? Status produksi akan berubah ke REVISI.');">
          <?= csrf_field() ?>
          <button class="btnPrimary" type="submit">Terima Revisi</button>
        </form>

        <form method="post" action="<?= site_url('editor/tugas/revisi/reject/' . (int)$job['id_jadwal']) ?>"
              onsubmit="return confirm('Tolak revisi? Status pemesanan akan dikembalikan.');">
          <?= csrf_field() ?>
          <button class="btnGhost" type="submit">Tolak Revisi</button>
        </form>
      </div>
    </div>

    <div style="height:12px;"></div>
  <?php elseif (!empty($revPending)): ?>
    <div class="panel">
      <div class="alert error">
        Revisi pelanggan masih <b>pending</b>. Menunggu kamu accept / reject.
      </div>
    </div>
    <div style="height:12px;"></div>
  <?php elseif (!empty($revProcess)): ?>
    <div class="panel">
      <div class="alert ok">
        Revisi sedang <b>diproses</b>. Setelah selesai, update status ke <b>revisi selesai</b> dan kirim link terbaru.
      </div>
    </div>
    <div style="height:12px;"></div>
  <?php endif; ?>

  <div class="panel">
    <h3 class="section__title" style="margin-bottom:10px;">Update Progres</h3>

    <?php if (!$canEdit): ?>
      <?php if (($statusNow ?? '') === 'done'): ?>
        <div class="alert ok">Editing sudah <b>DONE</b>. Tunggu revisi pelanggan (jika ada).</div>
      <?php elseif (($statusNow ?? '') === 'revisi selesai'): ?>
        <div class="alert ok">Revisi sudah <b>SELESAI</b>. Tunggu revisi tambahan atau serah terima hasil.</div>
      <?php else: ?>
        <div class="alert error">Tidak ada status lanjutan yang valid.</div>
      <?php endif; ?>

    <?php else: ?>
      <form method="post" enctype="multipart/form-data" action="<?= site_url('editor/tugas/update/'.$job['id_jadwal']) ?>">
        <?= csrf_field() ?>

        <div class="row">
          <div>
            <div class="label">Update ke Status</div>
            <select class="input" name="tahap" required>
              <?php foreach ($nextOptions as $opt): ?>
                <option value="<?= esc($opt) ?>"><?= esc($opt) ?></option>
              <?php endforeach; ?>
            </select>
            <small class="muted">
              Alur: cut-to-cut → finishing → done, dan revisi → revisi selesai.
              Revisi hanya dimulai lewat tombol <b>Terima Revisi</b>.
            </small>
          </div>

          <div>
            <div class="label">URL Preview (opsional)</div>
            <input class="input" name="url_preview" placeholder="https://drive.google.com/...">
            <small class="muted">URL akan masuk ke <b>catatan admin</b> (untuk ditampilkan di status pesanan saat lunas).</small>
          </div>
        </div>

        <div class="row">
          <div>
            <div class="label">Catatan (opsional)</div>
            <textarea class="input" name="catatan" rows="3"></textarea>
          </div>
        </div>

        <div class="row">
          <div>
            <div class="label">File Preview (opsional) JPG/PNG/PDF max 10MB</div>
            <input class="input" type="file" name="file_preview" accept="image/png,image/jpeg,application/pdf">
          </div>
        </div>

        <button class="btnPrimary" type="submit">Simpan</button>
      </form>
    <?php endif; ?>
  </div>

  <div style="height:12px;"></div>

  <div class="panel">
    <h3 class="section__title" style="margin-bottom:10px;">Preview File</h3>

    <?php if (empty($files)): ?>
      <div class="muted">Belum ada file preview.</div>
    <?php else: ?>
      <ul style="margin:0;padding-left:18px;">
        <?php foreach ($files as $f): ?>
          <li>
            <a class="link" target="_blank"
               href="<?= site_url('editor/tugas/file/'.$job['id_jadwal'].'/'.$f) ?>">
              <?= esc($f) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if (!empty($job['catatan_produksi'])): ?>
      <hr style="border:0;border-top:1px solid #e5e7eb;margin:14px 0;">
      <h3 class="section__title" style="margin-bottom:10px;">Catatan Produksi (log)</h3>
      <pre style="white-space:pre-wrap;margin:0;"><?= esc($job['catatan_produksi']) ?></pre>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
