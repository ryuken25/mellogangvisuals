<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container">
  <h2 class="section__title">Buat Pemesanan</h2>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="panel" style="margin-top:12px;">
    <form method="post" action="<?= site_url('pelanggan/pemesanan/simpan') ?>" id="orderForm">
      <?= csrf_field() ?>

      <div class="row">
        <div>
          <div class="label">Pilih Paket</div>
          <select class="input" name="id_paket" required>
            <option value="">-- pilih paket --</option>
            <?php foreach ($paket as $p): ?>
              <?php $pid = (int)$p['id_paket']; ?>
              <option value="<?= $pid ?>" <?= ($selectedId === $pid) ? 'selected' : '' ?>>
                <?= esc($p['nama_paket']) ?> — Rp <?= number_format((int)$p['harga'],0,',','.') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <div class="label">
            Tanggal Acara
            <span id="availBadge" class="availBadge">—</span>
          </div>

          <input
            class="input"
            type="date"
            name="tanggal_acara"
            id="tanggal_acara"
            value="<?= esc(old('tanggal_acara') ?? '') ?>"
            required
            autocomplete="off"
          >

          <small class="muted" id="availHelp">
            Hanya ada 2 slot per tanggal.
          </small>
          <div id="availMsg" class="muted" style="margin-top:6px;"></div>
        </div>

        <div>
          <div class="label">Jam Mulai (request kamu)</div>
          <input class="input" type="time" name="jam_mulai_acara"
                 value="<?= esc(old('jam_mulai_acara') ?? '') ?>" required>
        </div>
      </div>

      <div class="row">
        <div>
          <div class="label">Lokasi Acara</div>
          <input class="input" name="lokasi_acara" maxlength="150"
                 value="<?= esc(old('lokasi_acara') ?? '') ?>" required>
        </div>
      </div>

      <div class="row">
        <div>
          <div class="label">Catatan Pelanggan (opsional)</div>
          <textarea class="input" name="catatan_pelanggan" rows="3"><?= esc(old('catatan_pelanggan') ?? '') ?></textarea>
        </div>
      </div>

      <button class="btnPrimary" id="btnSubmit" type="submit">Simpan Pemesanan</button>
      <a class="btnGhost" href="<?= site_url('pelanggan') ?>">Kembali</a>
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

  /* warna input tanggal ikut availability */
  .availInput--green{ border-color:#86efac !important; box-shadow:0 0 0 3px rgba(134,239,172,.25); }
  .availInput--yellow{ border-color:#fde047 !important; box-shadow:0 0 0 3px rgba(253,224,71,.25); }
  .availInput--red{ border-color:#fca5a5 !important; box-shadow:0 0 0 3px rgba(252,165,165,.25); }

  /* tombol disabled */
  .btnDisabled{
    opacity:.6;
    cursor:not-allowed;
    pointer-events:none;
  }
</style>

<script>
(function(){
  const tgl = document.getElementById('tanggal_acara');
  const badge = document.getElementById('availBadge');
  const msg = document.getElementById('availMsg');
  const btn = document.getElementById('btnSubmit');
  const form = document.getElementById('orderForm');

  // ✅ cegah user ngetik tanggal (tetap bisa pakai date picker)
  if (tgl){
    tgl.addEventListener('keydown', (e) => e.preventDefault());
    tgl.addEventListener('paste', (e) => e.preventDefault());
  }

  function setBadge(remaining, booked, capacity){
    badge.classList.remove('avail--green','avail--yellow','avail--red');
    if (remaining >= 2) badge.classList.add('avail--green');
    else if (remaining === 1) badge.classList.add('avail--yellow');
    else badge.classList.add('avail--red');
    badge.textContent = `Sisa ${remaining}/${capacity}`;
  }

  function setInputColor(remaining){
    tgl.classList.remove('availInput--green','availInput--yellow','availInput--red');
    if (remaining >= 2) tgl.classList.add('availInput--green');
    else if (remaining === 1) tgl.classList.add('availInput--yellow');
    else tgl.classList.add('availInput--red');
  }

  function setSubmitEnabled(enabled){
    if (!btn) return;
    btn.disabled = !enabled;
    btn.classList.toggle('btnDisabled', !enabled);
  }

  async function refreshAvailability(){
    const date = (tgl.value || '').trim();
    msg.textContent = '';

    if (!date){
      badge.textContent = '—';
      badge.classList.remove('avail--green','avail--yellow','avail--red');
      tgl.classList.remove('availInput--green','availInput--yellow','availInput--red');
      setSubmitEnabled(false);
      return;
    }

    try{
      const res = await fetch(`<?= site_url('pelanggan/pemesanan/availability') ?>?date=${encodeURIComponent(date)}`);
      const js = await res.json();

      if (!js || !js.ok){
        badge.textContent = '—';
        badge.classList.remove('avail--green','avail--yellow','avail--red');
        tgl.classList.remove('availInput--green','availInput--yellow','availInput--red');
        setSubmitEnabled(false);
        return;
      }

      setBadge(js.remaining, js.booked, js.capacity);
      setInputColor(js.remaining);

      if (Number(js.remaining) <= 0){
        msg.textContent = 'Tanggal penuh (0/2). Pilih hari lain ya.';
        setSubmitEnabled(false);
      } else {
        setSubmitEnabled(true);
      }
    }catch(e){
      badge.textContent = '—';
      badge.classList.remove('avail--green','avail--yellow','avail--red');
      tgl.classList.remove('availInput--green','availInput--yellow','availInput--red');
      setSubmitEnabled(false);
    }
  }

  if (tgl) tgl.addEventListener('change', refreshAvailability);

  // safety: kalau somehow user bypass disable, tetap guard
  if (form){
    form.addEventListener('submit', (e) => {
      if (btn && btn.disabled){
        e.preventDefault();
        msg.textContent = 'Tanggal penuh (0/2). Pilih hari lain ya.';
      }
    });
  }

  // init
  refreshAvailability();
})();
</script>

<?= $this->endSection() ?>
