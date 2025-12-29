<?= $this->extend('layout/admin') ?>
<?= $this->section('adminContent') ?>

<div class="container">

  <div class="sectionHead">
    <div>
      <h2 class="section__title">Pemesanan</h2>
      <div class="muted">Kelola daftar pemesanan pelanggan.</div>
    </div>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert--success" style="margin-top:12px;">
      <?= esc(session()->getFlashdata('success')) ?>
    </div>
  <?php endif; ?>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error" style="margin-top:12px;">
      <?= esc(session()->getFlashdata('error')) ?>
    </div>
  <?php endif; ?>

  <div class="panel" style="margin-top:12px;">
    <form method="get" class="filters" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
      <input class="input" name="kode" value="<?= esc($kode ?? '') ?>"
             placeholder="Cari kode pemesanan..." style="max-width:220px;">

      <select class="input" name="status" style="max-width:220px;">
        <option value="">Semua Status</option>
        <?php
          $opts = [
            'menunggu pembayaran',
            'menunggu verifikasi',
            'diproses',
            'terjadwal',
            'produksi',
            'selesai',
            'revisi diajukan',
            'revisi diproses',
            'revisi selesai',
            'serah terima hasil',
            'dibatalkan',
          ];
          foreach ($opts as $opt):
        ?>
          <option value="<?= esc($opt) ?>" <?= (($status ?? '') === $opt) ? 'selected' : '' ?>>
            <?= esc(ucwords($opt)) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button class="btnPrimary" type="submit">Filter</button>
      <a class="btnGhost" href="<?= site_url('admin/pemesanan') ?>">Reset</a>
    </form>

    <div style="height:12px"></div>

    <?php if (empty($rows)): ?>
      <div class="emptyState">
        <div class="emptyState__title">Belum ada pemesanan</div>
        <div class="emptyState__desc">Data pemesanan akan muncul setelah pelanggan membuat pemesanan.</div>
      </div>
    <?php else: ?>
      <div class="tableWrap">
        <table class="table">
          <thead>
            <tr>
              <th style="width:160px;">Kode</th>
              <th>Pelanggan</th>
              <th>Paket</th>
              <th style="width:170px;">Tanggal</th>
              <th style="width:170px;">Total</th>
              <th style="width:180px;">Status</th>
              <th style="width:160px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td>
                  <a class="link" href="<?= site_url('admin/pemesanan/'.$r['id_pemesanan']) ?>">
                    <?= esc($r['kode_pemesanan']) ?>
                  </a>
                </td>
                <td><?= esc($r['nama_lengkap'] ?? '-') ?></td>
                <td><?= esc($r['nama_paket'] ?? '-') ?></td>

                <td>
                  <?php
                    $tgl = $r['tanggal_pemesanan'] ?? null;
                    echo $tgl ? esc(date('d/m/Y H:i', strtotime($tgl))) : '-';
                  ?>
                </td>

                <td>Rp <?= number_format((int)($r['total_biaya'] ?? 0), 0, ',', '.') ?></td>

                <td><span class="pill"><?= esc((string)($r['status_pemesanan'] ?? '-')) ?></span></td>

                <td style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                  <a class="btnMini" href="<?= site_url('admin/pemesanan/'.$r['id_pemesanan']) ?>">Detail</a>

                  <form method="post" action="<?= site_url('admin/pemesanan/delete/'.$r['id_pemesanan']) ?>"
                        onsubmit="return confirm('Yakin hapus pemesanan <?= esc($r['kode_pemesanan']) ?> ?');">
                    <?= csrf_field() ?>
                    <button class="btnMini" type="submit" style="background:#ef4444;color:#fff;border:none;">
                      Delete
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </div>
</div>

<?= $this->endSection() ?>
