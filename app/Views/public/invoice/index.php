<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container" style="max-width: 900px;">
  <div class="panel" style="margin-top: 14px;">
    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;">
      <div>
        <h2 class="section__title" style="margin:0;">Invoice</h2>
        <div class="muted">No: <b><?= esc($invoiceNo) ?></b></div>
        <div class="muted">Tanggal: <?= esc(date('d/m/Y H:i', strtotime($issuedAt))) ?></div>
      </div>

      <div style="display:flex;gap:10px;align-items:flex-start;">
        <button class="btnGhost" type="button" onclick="window.print()">Print / Save PDF</button>
        <a class="btnPrimary" href="<?= site_url('invoice/'.urlencode($order['kode_pemesanan']).'?download=1') ?>">Download</a>
      </div>
    </div>

    <hr style="border:0;border-top:1px solid #e5e7eb;margin:14px 0;">

    <div class="adminTwoCol">
      <div>
        <h3 class="miniTitle">Ditagihkan kepada</h3>
        <div><b><?= esc($order['nama_lengkap'] ?? '-') ?></b></div>
        <div class="muted"><?= esc($order['email'] ?? '-') ?></div>
        <div class="muted"><?= esc($order['no_telepon'] ?? '-') ?></div>
        <div style="margin-top:8px;"><b>Kode Pesanan:</b> <?= esc($order['kode_pemesanan']) ?></div>
      </div>

      <div>
        <h3 class="miniTitle">MellogangVisuals</h3>
        <div class="muted">Pembayaran via transfer / e-wallet</div>
        <div style="margin-top:8px;">
          <div><b>A/N:</b> I KADEK DARMADI</div>
          <div><b>BCA:</b> 7680513222</div>
          <div><b>MANDIRI:</b> 1450015365766</div>
          <div><b>BNI:</b> 0695785189</div>
          <div><b>BRI:</b> 477401009136504</div>
          <div><b>BPD BALI:</b> 0100202547258</div>
          <div><b>SEABANK:</b> 901081603711</div>
          <div><b>E-Wallet (OVO/DANA/SHOPEE):</b> 082236004917</div>
        </div>
      </div>
    </div>

    <hr style="border:0;border-top:1px solid #e5e7eb;margin:14px 0;">

    <h3 class="miniTitle">Rincian Pesanan</h3>

    <table class="table" style="margin-top:10px;">
      <thead>
        <tr>
          <th>Item</th>
          <th style="width:90px;">Qty</th>
          <th style="width:160px;">Harga</th>
          <th style="width:160px;">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($detail)): ?>
          <tr><td colspan="4" class="muted">Tidak ada detail add-on. (Paket utama tetap dihitung di total)</td></tr>
        <?php else: ?>
          <?php foreach ($detail as $d): ?>
            <tr>
              <td><?= esc($d['nama_item'] ?? '-') ?></td>
              <td><?= (int)($d['qty'] ?? 0) ?></td>
              <td>Rp <?= number_format((int)($d['harga_satuan'] ?? 0), 0, ',', '.') ?></td>
              <td>Rp <?= number_format((int)($d['subtotal'] ?? 0), 0, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <div style="display:flex;justify-content:flex-end;margin-top:12px;">
      <div style="min-width:320px;">
        <div class="infoRow"><span>Total Order</span><b>Rp <?= number_format((int)($order['total_biaya'] ?? 0), 0, ',', '.') ?></b></div>
        <div class="infoRow"><span>Total Terbayar (Valid)</span><b>Rp <?= number_format((int)($totalValid ?? 0), 0, ',', '.') ?></b></div>
        <div class="infoRow" style="border-top:1px dashed #e5e7eb;padding-top:10px;margin-top:10px;">
          <span>Sisa</span>
          <?php $sisa = max(0, (int)($order['total_biaya'] ?? 0) - (int)($totalValid ?? 0)); ?>
          <b>Rp <?= number_format($sisa, 0, ',', '.') ?></b>
        </div>
      </div>
    </div>

    <hr style="border:0;border-top:1px solid #e5e7eb;margin:14px 0;">

    <h3 class="miniTitle">Riwayat Pembayaran Valid</h3>
    <table class="table" style="margin-top:10px;">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Jenis</th>
          <th>Metode</th>
          <th style="width:180px;">Jumlah</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($validPays ?? []) as $p): ?>
          <tr>
            <td><?= esc($p['tanggal_bayar'] ?? '-') ?></td>
            <td><?= esc($p['jenis_pembayaran'] ?? '-') ?></td>
            <td><?= esc($p['metode_pembayaran'] ?? '-') ?></td>
            <td>Rp <?= number_format((int)($p['jumlah_bayar'] ?? 0), 0, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
</div>

<style>
@media print {
  header, footer, nav, .btnPrimary, .btnGhost { display: none !important; }
  .container { max-width: 100% !important; }
  .panel { box-shadow: none !important; border: 0 !important; }
}
</style>

<?= $this->endSection() ?>
