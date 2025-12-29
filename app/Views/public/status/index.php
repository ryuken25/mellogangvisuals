<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container">
  <h2 class="section__title">Pelacakan Status Pemesanan</h2>
  <p class="auth-sub">Cek perkembangan pesananmu dengan memasukkan kode pemesanan atau login ke akun MellogangVisuals.</p>

  <?php $showRight = !empty($kode) && !empty($order); ?>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert ok" style="margin-top:10px;"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error" style="margin-top:10px;"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="statusGrid <?= $showRight ? '' : 'statusGrid--single' ?>">
    <!-- LEFT -->
    <div class="statusCard">
      <div class="chip">Cari pesanan</div>

      <div class="label" style="margin-top:10px;">Masukkan kode pemesanan</div>
      <form method="get" action="<?= site_url('status-pesanan') ?>" class="statusForm">
        <input class="input" name="kode" value="<?= esc($kode) ?>" placeholder="Contoh: MLG251215-0016">
        <button class="btnPrimary" type="submit">Lacak Pesanan</button>
      </form>

      <?php if (!$loggedIn): ?>
        <div class="dividerOr"><span>atau</span></div>
        <a class="btnGhost" href="<?= site_url('login') ?>">Login sebagai pelanggan</a>
      <?php endif; ?>

      <div class="lineHr"></div>

      <?php if ($loggedIn && ($role ?? '') === 'pelanggan'): ?>
        <h3 class="miniTitle">Semua pesanan saya</h3>

        <?php if (empty($myOrders)): ?>
          <div class="muted" style="margin-top:10px;">Belum ada pesanan.</div>
        <?php else: ?>
          <div class="panel" style="margin-top:10px;">
            <table class="table">
              <thead>
                <tr>
                  <th>Kode</th>
                  <th>Paket</th>
                  <th>Tanggal</th>
                  <th>Status</th>
                  <th>Total</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($myOrders as $o): ?>
                  <?php $k = (string)($o['kode_pemesanan'] ?? ''); ?>
                  <tr>
                    <td>
                      <a class="link" href="<?= site_url('status-pesanan?kode=' . urlencode($k)) ?>">
                        <?= esc($k) ?>
                      </a>
                    </td>
                    <td><?= esc($o['nama_paket'] ?? '-') ?></td>
                    <td><?= esc($o['tanggal_acara'] ?? '-') ?></td>
                    <td><span class="pill"><?= esc($o['status_pemesanan'] ?? '-') ?></span></td>
                    <td>Rp <?= number_format((int)($o['total_biaya'] ?? 0), 0, ',', '.') ?></td>
                    <td>
                      <a class="link" href="<?= site_url('pelanggan/pembayaran/upload/' . (int)$o['id_pemesanan']) ?>">
                        Pembayaran
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <h3 class="miniTitle">Informasi Pesanan</h3>
        <div class="muted" style="margin-top:10px;">Login untuk melihat semua pesananmu.</div>
      <?php endif; ?>

      <?php if ($showRight): ?>
        <div class="lineHr"></div>
        <h3 class="miniTitle">Informasi Pesanan</h3>

        <div class="infoList" style="margin-top:10px;">
          <div class="infoRow"><span>Kode pemesanan</span><b><?= esc($order['kode_pemesanan']) ?></b></div>
          <div class="infoRow"><span>Nama pelanggan</span><b><?= esc($order['nama_lengkap'] ?? '-') ?></b></div>
          <div class="infoRow"><span>Paket</span><b><?= esc($order['nama_paket'] ?? '-') ?></b></div>
          <div class="infoRow"><span>Tanggal acara</span><b><?= esc($order['tanggal_acara'] ?? '-') ?></b></div>
          <div class="infoRow"><span>Status saat ini</span><b><span class="pill"><?= esc($order['status_pemesanan'] ?? '-') ?></span></b></div>
        </div>

        <div style="height:10px;"></div>

        <!-- Nominal hanya admin/owner -->
        <?php if (!empty($canSeeMoney)): ?>
          <div class="panel">
            <div class="infoRow"><span>Total Valid</span><b>Rp <?= number_format((int)($totalValid ?? 0), 0, ',', '.') ?></b></div>
            <div class="infoRow"><span>Sisa</span><b>Rp <?= number_format((int)($sisa ?? 0), 0, ',', '.') ?></b></div>
          </div>
        <?php else: ?>
          <div class="panel">
            <small class="muted">Nominal pembayaran & invoice hanya bisa dilihat oleh admin / pemilik pesanan.</small>
          </div>
        <?php endif; ?>

        <!-- Invoice -->
        <div style="height:10px;"></div>
        <div class="panel">
          <div class="miniTitle">Invoice</div>
          <?php if (!empty($canInvoice) && !empty($invoiceUrl)): ?>
            <div style="margin-top:8px;">
              <a class="btnGhost" href="<?= esc($invoiceUrl) ?>">Buka / Download Invoice</a>
            </div>
            <small class="muted">Invoice tersedia jika ada pembayaran valid (DP / pelunasan).</small>
          <?php else: ?>
            <small class="muted">Invoice hanya untuk admin / pemilik pesanan, dan jika ada pembayaran valid.</small>
          <?php endif; ?>
        </div>

        <!-- Ajukan Revisi + Pesanan sudah sesuai -->
        <div style="height:10px;"></div>
        <div class="panel">
          <div class="miniTitle" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
            <span>Revisi Pelanggan (<?= (int)($revCount ?? 0) ?>/2)</span>

            <?php if (!empty($canSesuai)): ?>
              <form method="post" action="<?= site_url('status-pesanan/selesai/' . (int)$order['id_pemesanan']) ?>"
                    onsubmit="return confirm('Yakin pesanan sudah sesuai dan masuk serah terima hasil?');">
                <?= csrf_field() ?>
                <button class="btnGhost" type="submit">Pesanan sudah sesuai</button>
              </form>
            <?php endif; ?>
          </div>

          <?php if (!empty($delivered)): ?>
            <small class="muted">Pesanan sudah masuk tahap serah terima hasil.</small>

          <?php elseif ((int)($revCount ?? 0) >= 2): ?>
            <small class="muted">Batas revisi sudah 2x. Pesanan akan/ sudah masuk serah terima hasil.</small>

          <?php elseif (!empty($revPending)): ?>
            <small class="muted">Revisi sudah diajukan. Menunggu editor menerima revisi.</small>

          <?php elseif (!empty($revProcess)): ?>
            <small class="muted">Revisi sedang diproses oleh editor.</small>

          <?php elseif (!empty($canRevisi) && ($loggedIn && ($role ?? '') === 'pelanggan')): ?>
            <form method="post" action="<?= site_url('status-pesanan/revisi/' . (int)$order['id_pemesanan']) ?>" style="margin-top:10px;">
              <?= csrf_field() ?>
              <div class="label">Catatan revisi</div>
              <textarea class="input" name="catatan_revisi" rows="3" placeholder="Tuliskan revisi yang kamu minta..." required></textarea>
              <button class="btnPrimary" type="submit" style="margin-top:10px;">Kirim Revisi</button>
            </form>
            <small class="muted">Revisi hanya bisa diajukan setelah status produksi <b>done / revisi selesai</b>. Status produksi tidak berubah sampai editor menerima revisi.</small>
          <?php else: ?>
            <small class="muted">Revisi hanya untuk pemilik pesanan, max 2x, dan hanya setelah status produksi <b>done / revisi selesai</b>.</small>
          <?php endif; ?>
        </div>

      <?php endif; ?>
    </div>

    <!-- RIGHT -->
    <?php if ($showRight): ?>
      <div class="statusCard">
        <h3 class="miniTitle">Progress Pemesanan</h3>
        <p class="muted" style="margin-top:4px;">Ikuti setiap langkah proses hingga serah terima hasil akhir.</p>

        <div class="track" style="margin-top:12px;">
          <?php foreach ($steps as $i => $s): ?>
            <div class="trackItem">
              <div class="trackLeft">
                <div class="trackDot <?= esc($s['state']) ?>"></div>
                <?php if ($i < count($steps) - 1): ?>
                  <div class="trackLine"></div>
                <?php endif; ?>
              </div>

              <div class="trackMid">
                <div class="trackLabel"><?= esc($s['label']) ?></div>
              </div>

              <div class="trackRight">
                <?php if ($s['state'] === 'done'): ?>
                  <span class="pill pill--done">Selesai</span>
                <?php elseif ($s['state'] === 'process'): ?>
                  <span class="pill pill--process">Proses</span>
                <?php else: ?>
                  <span class="pill pill--pending">Pending</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div style="height:12px;"></div>

        <!-- Link / Pesan Editor (hanya kalau LUNAS + admin/owner) -->
        <div class="panel">
          <div class="miniTitle">Link / Pesan Editor</div>

          <?php if (!empty($canSeeLinks)): ?>
            <?php if (!empty($adminUrls)): ?>
              <div style="margin-top:8px;">
                <?php foreach ($adminUrls as $u): ?>
                  <div><a class="link" target="_blank" href="<?= esc($u) ?>"><?= esc($u) ?></a></div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php if (!empty(trim((string)$adminNote))): ?>
              <div style="margin-top:8px;">
                <pre style="white-space:pre-wrap;margin:0;"><?= esc($adminNote) ?></pre>
              </div>
            <?php endif; ?>

            <?php if (empty($adminUrls) && empty(trim((string)$adminNote))): ?>
              <small class="muted">Belum ada link/pesan dari editor.</small>
            <?php endif; ?>
          <?php else: ?>
            <small class="muted">
              Link editor hanya tampil jika pesanan <b>LUNAS</b> dan yang melihat adalah <b>admin / pemilik pesanan</b>.
              (DP saja belum menampilkan link.)
            </small>
          <?php endif; ?>
        </div>

        <div style="height:12px;"></div>

        <!-- Log Produksi (publik, tanpa URL) -->
        <div class="panel">
          <div class="miniTitle">Log Produksi</div>
          <?php if (!empty(trim((string)$publicLog))): ?>
            <pre style="white-space:pre-wrap;margin:0;"><?= esc($publicLog) ?></pre>
          <?php else: ?>
            <small class="muted">Belum ada log produksi.</small>
          <?php endif; ?>
        </div>

      </div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
