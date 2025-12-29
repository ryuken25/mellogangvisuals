<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container">
  <h2 class="section__title">Upload Pembayaran</h2>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <div class="panel" style="margin-top:12px;">
    <div class="muted" style="margin-bottom:10px;">
      Kode Pesanan: <b><?= esc($order['kode_pemesanan']) ?></b>
      <div>Total: <b>Rp <?= number_format((int)$total,0,',','.') ?></b></div>
      <div>Sudah valid: <b>Rp <?= number_format((int)$totalValid,0,',','.') ?></b></div>
      <div>Sisa: <b>Rp <?= number_format((int)$sisa,0,',','.') ?></b></div>
      <div>DP (50%): <b>Rp <?= number_format((int)$dpDue,0,',','.') ?></b></div>
    </div>

    <div class="panel" style="background:#fafafa;margin-bottom:12px;">
      <div style="font-weight:700;margin-bottom:6px;">Instruksi Pembayaran</div>
      <div class="muted" style="margin-bottom:8px;">
        Transfer Pembayaran ke rekening berikut A/N: <b>I KADEK DARMADI</b>
      </div>
      <ul style="margin:0;padding-left:18px;">
        <li>BCA: <b>7680513222</b></li>
        <li>MANDIRI: <b>1450015365766</b></li>
        <li>BNI: <b>0695785189</b></li>
        <li>BRI: <b>477401009136504</b></li>
        <li>BPD BALI: <b>0100202547258</b></li>
        <li>SEABANK: <b>901081603711</b></li>
      </ul>
      <div class="muted" style="margin-top:8px;">
        E-Wallet (OVO/DANA/ShopeePay) ke: <b>082236004917</b>
      </div>
    </div>

    <form method="post" enctype="multipart/form-data" action="<?= site_url('pelanggan/pembayaran/upload/'.$order['id_pemesanan']) ?>">
      <?= csrf_field() ?>

      <div class="row">
        <div>
          <div class="label">Jenis Pembayaran</div>
          <select class="input" id="jenis_pembayaran" name="jenis_pembayaran" required>
            <?php if ($allowDP): ?>
              <option value="DP">DP (50%)</option>
            <?php endif; ?>
            <option value="Pelunasan"><?= $allowDP ? 'Pelunasan (Full)' : 'Pelunasan (Sisa)' ?></option>
          </select>
          <?php if (!$allowDP): ?>
            <small class="muted">DP sudah valid, jadi sekarang upload Pelunasan (sisa).</small>
          <?php endif; ?>
        </div>

        <div>
          <div class="label">Metode Pembayaran</div>
          <select class="input" name="metode_pembayaran" required>
            <option value="">-- pilih metode --</option>
            <option value="BCA">Transfer BCA</option>
            <option value="MANDIRI">Transfer Mandiri</option>
            <option value="BNI">Transfer BNI</option>
            <option value="BRI">Transfer BRI</option>
            <option value="BPD BALI">Transfer BPD Bali</option>
            <option value="SEABANK">Transfer Seabank</option>
            <option value="E-Wallet (OVO/DANA/ShopeePay)">E-Wallet (OVO/DANA/ShopeePay)</option>
            <option value="Tunai">Tunai</option>
          </select>
        </div>
      </div>

      <div class="row">
        <div>
          <div class="label">Jumlah Bayar</div>

          <!-- angka murni untuk backend -->
          <input type="hidden" id="jumlah_bayar" name="jumlah_bayar"
                 value="<?= (int)($allowDP ? $dpDue : $pelunasanDue) ?>">

          <!-- tampilan Rp -->
          <input class="input" id="jumlah_bayar_display" type="text" readonly
                 value="Rp <?= number_format((int)($allowDP ? $dpDue : $pelunasanDue),0,',','.') ?>">

          <small class="muted">Jumlah otomatis sesuai DP/Pelunasan.</small>
        </div>

        <div>
          <div class="label">Bukti Bayar (JPG/PNG max 2MB)</div>
          <input class="input" type="file" name="bukti_bayar" accept="image/png,image/jpeg" required>
        </div>
      </div>

      <button class="btnPrimary" type="submit">Kirim</button>
      <a class="btnGhost" href="<?= site_url('pelanggan/pembayaran/riwayat/'.$order['id_pemesanan']) ?>">Lihat Riwayat</a>
    </form>
  </div>
</div>

<script>
(function(){
  const jenis = document.getElementById('jenis_pembayaran');
  const hidden = document.getElementById('jumlah_bayar');
  const display = document.getElementById('jumlah_bayar_display');

  const dpDue = <?= (int)$dpDue ?>;
  const pelunasanDue = <?= (int)$pelunasanDue ?>;

  function formatRp(n){
    return 'Rp ' + (n || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function syncJumlah(){
    const val = (jenis && jenis.value === 'DP') ? dpDue : pelunasanDue;
    hidden.value = val;
    display.value = formatRp(val);
  }

  if (jenis) jenis.addEventListener('change', syncJumlah);
  syncJumlah();
})();
</script>

<?= $this->endSection() ?>
