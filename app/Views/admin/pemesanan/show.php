<?= $this->extend('layout/admin') ?>
<?= $this->section('adminContent') ?>

<div class="container">

  <h2 class="section__title">Detail Pemesanan</h2>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <style>
    .btnOk{
      background:#16a34a;
      color:#fff;
      padding:8px 12px;
      border-radius:10px;
      display:inline-block;
      text-decoration:none;
      border:none;
    }
    .btnOk:hover{ opacity:.95; }
  </style>

  <div class="panel">
    <div class="adminTwoCol">
      <div>
        <h3 class="section__title" style="margin-bottom:8px;">Info Pemesanan</h3>
        <div><b>Kode:</b> <?= esc($order['kode_pemesanan']) ?></div>
        <div><b>Status:</b> <?= esc($order['status_pemesanan']) ?></div>
        <div><b>Tanggal Pesan:</b> <?= esc($order['tanggal_pemesanan']) ?></div>
        <div><b>Tanggal Acara:</b> <?= esc($order['tanggal_acara']) ?></div>
        <?php if (!empty($order['jam_mulai_acara'])): ?>
          <div><b>Jam Mulai Acara:</b> <?= esc($order['jam_mulai_acara']) ?></div>
        <?php endif; ?>
        <div><b>Lokasi:</b> <?= esc($order['lokasi_acara']) ?></div>
        <div><b>Total:</b> Rp <?= number_format((int)$order['total_biaya'], 0, ',', '.') ?></div>
        <div><b>Paket:</b> <?= esc($order['nama_paket'] ?? '-') ?> (<?= esc($order['kategori'] ?? '-') ?>)</div>

        <div style="margin-top:10px;">
          <div><b>Total Valid:</b> Rp <?= number_format((int)($totalValid ?? 0), 0, ',', '.') ?></div>
          <div><b>Sisa:</b> Rp <?= number_format((int)($sisa ?? 0), 0, ',', '.') ?></div>

          <?php if ((int)($totalValid ?? 0) > 0): ?>
            <div style="margin-top:10px;">
              <a class="btnPrimary" href="<?= site_url('admin/pemesanan/invoice/'.$order['id_pemesanan']) ?>" target="_blank" rel="noopener">
                Invoice
              </a>
            </div>
          <?php else: ?>
            <div class="muted" style="margin-top:8px;">Invoice muncul setelah ada pembayaran valid.</div>
          <?php endif; ?>
        </div>

        <div style="margin-top:12px;">
          <form method="post" action="<?= site_url('admin/pemesanan/delete/'.$order['id_pemesanan']) ?>"
                onsubmit="return confirm('Yakin hapus pemesanan <?= esc($order['kode_pemesanan']) ?> ?');">
            <?= csrf_field() ?>
            <button class="btnMini" type="submit" style="background:#ef4444;color:#fff;border:none;">
              Delete Pemesanan
            </button>
          </form>
        </div>
      </div>

      <div>
        <h3 class="section__title" style="margin-bottom:8px;">Info Pelanggan</h3>
        <div><b>Nama:</b> <?= esc($order['nama_lengkap'] ?? '-') ?></div>
        <div><b>Email:</b> <?= esc($order['email'] ?? '-') ?></div>
        <div><b>Telepon:</b> <?= esc($order['no_telepon'] ?? '-') ?></div>

        <?php if (!empty($order['catatan_admin'])): ?>
          <hr style="border:0;border-top:1px solid #e5e7eb;margin:14px 0;">
          <h3 class="section__title" style="margin-bottom:8px;">Catatan Admin / Log Editor</h3>
          <pre style="white-space:pre-wrap;margin:0;"><?= esc($order['catatan_admin']) ?></pre>
        <?php endif; ?>
      </div>
    </div>

    <hr style="border:0;border-top:1px solid #e5e7eb;margin:14px 0;">

    <h3 class="section__title" style="margin-bottom:8px;">Pembayaran</h3>
    <?php if (empty($pay)): ?>
      <div class="muted">Belum ada pembayaran.</div>
    <?php else: ?>
      <table class="table">
        <thead><tr><th>Jenis</th><th>Jumlah</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($pay as $p): ?>
          <tr>
            <td><?= esc($p['jenis_pembayaran']) ?></td>
            <td>Rp <?= number_format((int)$p['jumlah_bayar'], 0, ',', '.') ?></td>
            <td><span class="pill"><?= esc($p['status_verifikasi']) ?></span></td>
            <td>
              <a class="btnOk" href="<?= site_url('admin/pembayaran/verify/'.$p['id_pembayaran']) ?>">
                Verifikasi / Lihat Bukti
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <hr style="border:0;border-top:1px solid #e5e7eb;margin:14px 0;">

    <h3 class="section__title" style="margin-bottom:8px;">Jadwal Produksi</h3>
    <?php if (!$jadwal): ?>
      <div class="muted">
        Belum dibuat.
        <a class="link" href="<?= site_url('admin/jadwal/create?id_pemesanan='.$order['id_pemesanan']) ?>">Buat jadwal</a>
      </div>
    <?php else: ?>
      <div><b>Status Produksi:</b> <?= esc($jadwal['status_produksi']) ?></div>
      <div><b>Shooting:</b> <?= esc($jadwal['tanggal_shooting']) ?> (<?= esc($jadwal['jam_mulai_shooting']) ?> - <?= esc($jadwal['jam_selesai_shooting']) ?>)</div>
      <div><b>Editing:</b> <?= esc($jadwal['tanggal_mulai_editing']) ?> → <?= esc($jadwal['tanggal_selesai_editing']) ?></div>
      <div style="margin-top:8px;"><a class="link" href="<?= site_url('admin/jadwal/edit/'.$jadwal['id_jadwal']) ?>">Edit jadwal</a></div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
