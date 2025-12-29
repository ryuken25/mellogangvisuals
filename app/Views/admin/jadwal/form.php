<?= $this->extend('layout/admin') ?>
<?= $this->section('adminContent') ?>

<div class="container">
  <h2 class="section__title"><?= esc($title ?? 'Form Jadwal') ?></h2>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <?php
    $data = $row ?? null;

    $actionUrl = ($mode ?? 'create') === 'create'
      ? site_url('admin/jadwal')
      : site_url('admin/jadwal/update/' . (int)($data['id_jadwal'] ?? 0));

    $selectedPemesanan = (int)($data['id_pemesanan'] ?? 0);
    $selectedEditor    = (int)($data['id_editor'] ?? 0);
    $statusNow = (string)($data['status_produksi'] ?? 'pra produksi');
  ?>

  <div class="panel" style="margin-top:12px;">
    <form method="post" class="form" action="<?= $actionUrl ?>">
      <?= csrf_field() ?>

      <div class="row">
        <div>
          <div class="label">Pemesanan (hanya yang sudah DP/Lunas & belum dijadwalkan)</div>
          <select class="input" name="id_pemesanan" id="id_pemesanan" required>
            <option value="">-- pilih --</option>
            <?php foreach (($pemesanan ?? []) as $pm): ?>
              <?php
                $idpm = (int)($pm['id_pemesanan'] ?? 0);
                $tglAcara = (string)($pm['tanggal_acara'] ?? '');
                $jamMulai = (string)($pm['jam_mulai_acara'] ?? '');
              ?>
              <option
                value="<?= $idpm ?>"
                <?= ($selectedPemesanan === $idpm) ? 'selected' : '' ?>
                data-tanggal-acara="<?= esc($tglAcara) ?>"
                data-jam-mulai="<?= esc($jamMulai) ?>"
              >
                <?= esc($pm['kode_pemesanan'] ?? ('#'.$idpm)) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <small class="muted">Jika pemesanan sudah dibuat jadwal, tidak akan muncul lagi di list.</small>
        </div>

        <div>
          <div class="label">Editor</div>
          <select class="input" name="id_editor" required>
            <option value="">-- pilih editor --</option>
            <?php foreach (($editors ?? []) as $e): ?>
              <?php $ide = (int)($e['id_user'] ?? 0); ?>
              <option value="<?= $ide ?>" <?= ($selectedEditor === $ide) ? 'selected' : '' ?>>
                <?= esc($e['nama_lengkap'] ?? ('Editor #'.$ide)) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="row">
        <div>
          <div class="label">
            Tanggal Shooting
            <span id="availBadge" class="availBadge">—</span>
          </div>
          <input class="input" type="date" name="tanggal_shooting" id="tanggal_shooting"
                 value="<?= esc($data['tanggal_shooting'] ?? '') ?>">
          <small class="muted">Fotografer ada 2 slot per tanggal: hijau=2, kuning=1, merah=0.</small>
        </div>
        <div>
          <div class="label">Jam Mulai (auto dari request pelanggan)</div>
          <input class="input" type="time" name="jam_mulai_shooting" id="jam_mulai_shooting"
                 value="<?= esc($data['jam_mulai_shooting'] ?? '') ?>">
        </div>
        <div>
          <div class="label">Jam Selesai</div>
          <input class="input" type="time" name="jam_selesai_shooting" id="jam_selesai_shooting"
                 value="<?= esc($data['jam_selesai_shooting'] ?? '') ?>">
        </div>
      </div>

      <div class="row">
        <div>
          <div class="label">Tanggal Mulai Editing</div>
          <input class="input" type="date" name="tanggal_mulai_editing"
                 value="<?= esc($data['tanggal_mulai_editing'] ?? '') ?>">
        </div>
        <div>
          <div class="label">Tanggal Selesai Editing</div>
          <input class="input" type="date" name="tanggal_selesai_editing"
                 value="<?= esc($data['tanggal_selesai_editing'] ?? '') ?>">
        </div>
      </div>

      <div class="row">
        <div>
          <div class="label">Status Produksi</div>
          <select class="input" name="status_produksi">
            <?php
              $list = $allowedStatus ?? ['pra produksi','shooting','cut-to-cut'];
              foreach ($list as $s):
                $sel = (strtolower($statusNow) === strtolower($s)) ? 'selected' : '';
            ?>
              <option value="<?= esc($s) ?>" <?= $sel ?>><?= esc($s) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="muted">Admin set sampai cut-to-cut. Finishing/revisi/done/revisi selesai dikerjakan editor.</small>
        </div>

        <div>
          <div class="label">Catatan Produksi (opsional)</div>
          <input class="input" name="catatan_produksi" value="<?= esc($data['catatan_produksi'] ?? '') ?>">
        </div>
      </div>

      <button class="btnPrimary" type="submit">Simpan</button>
      <a class="btnGhost" href="<?= site_url('admin/jadwal') ?>">Kembali</a>
    </form>
  </div>
</div>

<style>
  .availBadge{
    display:inline-block;
    padding:4px 10px;
    border-radius:999px;
    font-size:12px;
    margin-left:8px;
    border:1px solid #e5e7eb;
    background:#f3f4f6;
  }
  .avail--green{ background:#dcfce7; border-color:#86efac; }
  .avail--yellow{ background:#fef9c3; border-color:#fde047; }
  .avail--red{ background:#fee2e2; border-color:#fca5a5; }
</style>

<script>
(function(){
  const pemesananSel = document.getElementById('id_pemesanan');
  const tglInput = document.getElementById('tanggal_shooting');
  const jamMulai = document.getElementById('jam_mulai_shooting');
  const badge = document.getElementById('availBadge');

  function setBadge(remaining, booked, capacity){
    badge.classList.remove('avail--green','avail--yellow','avail--red');
    if (remaining >= 2) badge.classList.add('avail--green');
    else if (remaining === 1) badge.classList.add('avail--yellow');
    else badge.classList.add('avail--red');
    badge.textContent = `Sisa ${remaining}/${capacity}`;
  }

  async function refreshAvailability(){
    const date = (tglInput.value || '').trim();
    if (!date){
      badge.textContent = '—';
      badge.classList.remove('avail--green','avail--yellow','avail--red');
      return;
    }

    try{
      const res = await fetch(`<?= site_url('admin/jadwal/availability') ?>?date=${encodeURIComponent(date)}`);
      const js = await res.json();
      if (!js || !js.ok){
        badge.textContent = '—';
        badge.classList.remove('avail--green','avail--yellow','avail--red');
        return;
      }
      setBadge(js.remaining, js.booked, js.capacity);
    }catch(e){
      badge.textContent = '—';
      badge.classList.remove('avail--green','avail--yellow','avail--red');
    }
  }

  // auto set tanggal shooting + jam mulai dari request pelanggan
  function autoFillFromOrder(){
    const opt = pemesananSel.options[pemesananSel.selectedIndex];
    if (!opt) return;

    const tglAcara = opt.getAttribute('data-tanggal-acara') || '';
    const jamReq = opt.getAttribute('data-jam-mulai') || '';

    if (tglAcara && !tglInput.value) tglInput.value = tglAcara;
    if (jamReq && !jamMulai.value) jamMulai.value = jamReq;

    refreshAvailability();
  }

  if (pemesananSel) pemesananSel.addEventListener('change', autoFillFromOrder);
  if (tglInput) tglInput.addEventListener('change', refreshAvailability);

  autoFillFromOrder();
  refreshAvailability();
})();
</script>

<?= $this->endSection() ?>
