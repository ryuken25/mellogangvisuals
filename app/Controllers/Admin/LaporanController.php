<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class LaporanController extends BaseController
{
    private function normalizePay(?string $pay): string
    {
        $pay = strtolower(trim((string) $pay));
        if (!in_array($pay, ['valid', 'pending', 'all'], true)) return 'valid';
        return $pay;
    }

    /**
     * CSV supaya kebaca tabel di Excel:
     * - BOM UTF-8
     * - baris pertama "sep=;" biar Excel pakai delimiter ;
     * - Content-Disposition attachment (auto-download)
     */
    private function csvResponse(string $filename, array $headers, array $rows, string $delimiter = ';')
    {
        $fp = fopen('php://temp', 'r+');

        // Excel delimiter hint
        fwrite($fp, "\xEF\xBB\xBF");          // BOM
        fwrite($fp, "sep={$delimiter}\r\n");  // delimiter hint

        fputcsv($fp, $headers, $delimiter);

        foreach ($rows as $r) {
            fputcsv($fp, $r, $delimiter);
        }

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    /**
     * Pengeluaran kolom tanggal beda-beda di project kamu (biar gak error "Unknown column tanggal")
     */
    private function pengeluaranCols($db): array
    {
        $fields = [];
        try {
            $fields = $db->getFieldNames('pengeluaran_operasional');
        } catch (\Throwable $e) {
            // biarin, nanti fallback
        }

        $tanggal = in_array('tanggal_pengeluaran', $fields, true) ? 'tanggal_pengeluaran'
            : (in_array('tanggal', $fields, true) ? 'tanggal'
            : (in_array('created_at', $fields, true) ? 'created_at' : null));

        $nama = in_array('nama_pengeluaran', $fields, true) ? 'nama_pengeluaran'
            : (in_array('nama', $fields, true) ? 'nama' : 'nama_pengeluaran');

        $jumlah = in_array('jumlah', $fields, true) ? 'jumlah'
            : (in_array('nominal', $fields, true) ? 'nominal' : 'jumlah');

        $idPemesanan = in_array('id_pemesanan', $fields, true) ? 'id_pemesanan' : null;
        $createdAt = in_array('created_at', $fields, true) ? 'created_at' : null;

        return [$tanggal, $nama, $jumlah, $idPemesanan, $createdAt];
    }

    public function index()
    {
        $db = db_connect();
        $pay = $this->normalizePay($this->request->getGet('pay'));

        // =========================
        // 1) TOTAL PEMASUKAN (VALID)
        // =========================
        $rowSum = $db->table('pembayaran')
            ->selectSum('jumlah_bayar', 'total')
            ->where('status_verifikasi', 'valid')
            ->get()->getRowArray();

        $totalPemasukan = (int) ($rowSum['total'] ?? 0);

        // =========================
        // 2) PENGELUARAN
        // =========================
        [$colTanggal, $colNama, $colJumlah, $colIdPes, $colCreated] = $this->pengeluaranCols($db);

        $pengeluaran = [];
        $totalPengeluaran = 0;

        if ($colTanggal !== null) {
            $pengeluaran = $db->table('pengeluaran_operasional')
                ->orderBy($colTanggal, 'DESC')
                ->orderBy('id_pengeluaran', 'DESC')
                ->get()->getResultArray();

            foreach ($pengeluaran as $p) {
                $totalPengeluaran += (int) ($p[$colJumlah] ?? 0);
            }
        }

        $laba = $totalPemasukan - $totalPengeluaran;

        // edit pengeluaran via query ?edit_pengeluaran=ID
        $editId = (int) ($this->request->getGet('edit_pengeluaran') ?? 0);
        $editPengeluaran = null;
        if ($editId > 0) {
            $editPengeluaran = $db->table('pengeluaran_operasional')
                ->where('id_pengeluaran', $editId)
                ->get()->getRowArray();
        }

        // =========================
        // 3) PEMBAYARAN (VALID & MENUNGGU)
        // =========================
        $validPayments = [];
        $menungguPayments = [];

        if ($pay !== 'pending') {
            $validPayments = $db->table('pembayaran p')
                ->select('p.id_pembayaran, p.id_pemesanan, p.jenis_pembayaran, p.tanggal_bayar, p.metode_pembayaran, p.jumlah_bayar, p.status_verifikasi, o.kode_pemesanan, u.nama_lengkap', false)
                ->join('pemesanan o', 'o.id_pemesanan = p.id_pemesanan', 'left')
                ->join('user u', 'u.id_user = o.id_user', 'left')
                ->where('p.status_verifikasi', 'valid')
                ->orderBy('p.tanggal_bayar', 'DESC')
                ->orderBy('p.id_pembayaran', 'DESC')
                ->get()->getResultArray();
        }

        if ($pay !== 'valid') {
            // pending cuma "Menunggu" (bukan ditolak)
            $menungguPayments = $db->table('pembayaran p')
                ->select('p.id_pembayaran, p.id_pemesanan, p.jenis_pembayaran, p.tanggal_bayar, p.metode_pembayaran, p.jumlah_bayar, p.status_verifikasi, o.kode_pemesanan, u.nama_lengkap', false)
                ->join('pemesanan o', 'o.id_pemesanan = p.id_pemesanan', 'left')
                ->join('user u', 'u.id_user = o.id_user', 'left')
                ->where('p.status_verifikasi', 'Menunggu')
                ->orderBy('p.tanggal_bayar', 'DESC')
                ->orderBy('p.id_pembayaran', 'DESC')
                ->get()->getResultArray();
        }

        // =========================
        // 4) REKAP ORDER (SISA PELUNASAN + BELUM BAYAR)
        //    - pending mencakup:
        //      a) belum ada pembayaran sama sekali
        //      b) DP valid tapi belum lunas (sisa > 0)
        //      c) ada pembayaran menunggu verifikasi
        // =========================
        $rekapOrder = [];
        if ($pay !== 'valid') {
            $rekapOrder = $db->table('pemesanan o')
                ->select('
                    o.id_pemesanan,
                    o.kode_pemesanan,
                    o.tanggal_pemesanan,
                    o.status_pemesanan,
                    o.total_biaya,
                    u.nama_lengkap,
                    COUNT(p.id_pembayaran) AS total_payment,
                    COALESCE(SUM(CASE WHEN p.status_verifikasi = "valid" THEN p.jumlah_bayar ELSE 0 END),0) AS total_valid,
                    MAX(CASE WHEN LOWER(p.status_verifikasi) = "menunggu" THEN 1 ELSE 0 END) AS has_menunggu
                ', false)
                ->join('user u', 'u.id_user = o.id_user', 'left')
                ->join('pembayaran p', 'p.id_pemesanan = o.id_pemesanan', 'left')
                ->groupBy('o.id_pemesanan')
                ->orderBy('o.tanggal_pemesanan', 'DESC')
                ->get()->getResultArray();

            // filter: yang memang masih ada sisa
            $tmp = [];
            foreach ($rekapOrder as $r) {
                $total = (int) ($r['total_biaya'] ?? 0);
                $valid = (int) ($r['total_valid'] ?? 0);
                $sisa  = max($total - $valid, 0);

                if ($sisa <= 0) continue; // sudah lunas, gak masuk pending
                $r['sisa'] = $sisa;
                $tmp[] = $r;
            }
            $rekapOrder = $tmp;
        }

        return view('admin/laporan/index', [
            'title'           => 'Laporan',
            'pay'             => $pay,

            'totalPemasukan'  => $totalPemasukan,
            'totalPengeluaran'=> $totalPengeluaran,
            'laba'            => $laba,

            'validPayments'   => $validPayments,
            'menungguPayments'=> $menungguPayments,

            'rekapOrder'      => $rekapOrder,

            'pengeluaran'     => $pengeluaran,
            'pengCols'        => [
                'tanggal' => $colTanggal,
                'nama'    => $colNama,
                'jumlah'  => $colJumlah,
                'idpes'   => $colIdPes,
                'created' => $colCreated,
            ],
            'editPengeluaran' => $editPengeluaran,
        ]);
    }

    /**
     * Export rekap pembayaran per ORDER:
     * kolom: Tipe, Kode, Pelanggan, Tanggal, Jenis, Metode, Jumlah, Status, Total Order, Total Valid, Sisa
     *
     * pay=valid   -> order yang punya total_valid > 0
     * pay=pending -> order yang sisa > 0 ATAU ada menunggu (dan bukan murni ditolak doang)
     * pay=all     -> semua order
     */
    public function exportPembayaran()
    {
        $db = db_connect();
        $pay = $this->normalizePay($this->request->getGet('pay'));

        // summary per order
        $orders = $db->table('pemesanan o')
            ->select('
                o.id_pemesanan,
                o.kode_pemesanan,
                o.tanggal_pemesanan,
                o.total_biaya,
                u.nama_lengkap,
                COALESCE(SUM(CASE WHEN p.status_verifikasi = "valid" THEN p.jumlah_bayar ELSE 0 END),0) AS total_valid,
                MAX(CASE WHEN LOWER(p.status_verifikasi) = "menunggu" THEN 1 ELSE 0 END) AS has_menunggu,
                COUNT(p.id_pembayaran) AS total_payment
            ', false)
            ->join('user u', 'u.id_user = o.id_user', 'left')
            ->join('pembayaran p', 'p.id_pemesanan = o.id_pemesanan', 'left')
            ->groupBy('o.id_pemesanan')
            ->orderBy('o.tanggal_pemesanan', 'DESC')
            ->get()->getResultArray();

        // ambil pembayaran TERAKHIR per order (buat kolom Jenis/Metode/Jumlah/Status/Tanggal)
        $payRows = $db->table('pembayaran')
            ->select('id_pemesanan, tanggal_bayar, jenis_pembayaran, metode_pembayaran, jumlah_bayar, status_verifikasi')
            ->orderBy('tanggal_bayar', 'DESC')
            ->orderBy('id_pembayaran', 'DESC')
            ->get()->getResultArray();

        $lastPay = [];
        foreach ($payRows as $pr) {
            $idp = (int) ($pr['id_pemesanan'] ?? 0);
            if ($idp <= 0) continue;
            if (!isset($lastPay[$idp])) $lastPay[$idp] = $pr;
        }

        $rows = [];

        foreach ($orders as $o) {
            $idp   = (int) ($o['id_pemesanan'] ?? 0);
            $total = (int) ($o['total_biaya'] ?? 0);
            $valid = (int) ($o['total_valid'] ?? 0);
            $sisa  = max($total - $valid, 0);

            $hasMenunggu = (int) ($o['has_menunggu'] ?? 0) === 1;
            $totalPayment = (int) ($o['total_payment'] ?? 0);

            $lp = $lastPay[$idp] ?? null;

            $jenis  = $lp['jenis_pembayaran'] ?? '-';
            $metode = $lp['metode_pembayaran'] ?? '-';
            $jumlah = (int) ($lp['jumlah_bayar'] ?? 0);
            $status = $lp['status_verifikasi'] ?? '-';
            $tglBayar = $lp['tanggal_bayar'] ?? '';

            // tipe order
            $tipe = ($sisa <= 0) ? 'TERBAYAR' : 'PENDING';

            // filter mode
            if ($pay === 'valid') {
                // "valid" = minimal sudah ada pembayaran valid (DP atau pelunasan)
                if ($valid <= 0) continue;
            } elseif ($pay === 'pending') {
                // "pending" = masih ada sisa atau ada menunggu verifikasi atau belum bayar sama sekali
                if ($sisa <= 0 && !$hasMenunggu) continue;

                // sesuai request: jangan tampilkan yang murni ditolak doang
                // (jika total_valid=0, tidak menunggu, dan ada pembayaran -> kemungkinan ditolak)
                if ($valid <= 0 && !$hasMenunggu && $totalPayment > 0) {
                    continue;
                }
            }

            $rows[] = [
                $tipe,
                $o['kode_pemesanan'] ?? '',
                $o['nama_lengkap'] ?? '',
                $tglBayar !== '' ? date('m/d/Y H:i', strtotime($tglBayar)) : date('m/d/Y H:i', strtotime((string)($o['tanggal_pemesanan'] ?? ''))),
                $jenis,
                $metode,
                $jumlah, // angka murni biar gampang di-sum di Excel
                $status,
                $total,
                $valid,
                $sisa,
            ];
        }

        $filename = 'rekap_pembayaran_' . $pay . '_' . date('Ymd_His') . '.csv';

        return $this->csvResponse($filename, [
            'Tipe', 'Kode', 'Pelanggan', 'Tanggal', 'Jenis', 'Metode', 'Jumlah', 'Status', 'Total Order', 'Total Valid', 'Sisa'
        ], $rows);
    }

    public function exportPengeluaran()
    {
        $db = db_connect();
        [$colTanggal, $colNama, $colJumlah, $colIdPes, $colCreated] = $this->pengeluaranCols($db);

        if ($colTanggal === null) {
            return redirect()->to(site_url('admin/laporan'))->with('error', 'Tabel pengeluaran_operasional belum siap.');
        }

        $data = $db->table('pengeluaran_operasional')
            ->orderBy($colTanggal, 'DESC')
            ->orderBy('id_pengeluaran', 'DESC')
            ->get()->getResultArray();

        $rows = [];
        foreach ($data as $p) {
            $rows[] = [
                $p[$colTanggal] ?? '',
                $p[$colNama] ?? '',
                (int) ($p[$colJumlah] ?? 0),
                $colIdPes ? ($p[$colIdPes] ?? '') : '',
                $colCreated ? ($p[$colCreated] ?? '') : '',
            ];
        }

        $filename = 'pengeluaran_' . date('Ymd_His') . '.csv';

        return $this->csvResponse($filename, [
            'Tanggal', 'Nama Pengeluaran', 'Nominal', 'ID Pemesanan', 'Created At'
        ], $rows);
    }

    public function storePengeluaran()
    {
        $db = db_connect();
        [$colTanggal, $colNama, $colJumlah, $colIdPes, $colCreated] = $this->pengeluaranCols($db);

        $tanggal = $this->request->getPost('tanggal');
        $nama    = trim((string) $this->request->getPost('nama_pengeluaran'));
        $jumlah  = (int) $this->request->getPost('jumlah');
        $idPes   = (int) $this->request->getPost('id_pemesanan');

        if ($tanggal === '' || $nama === '' || $jumlah <= 0) {
            return redirect()->back()->withInput()->with('error', 'Tanggal, nama, dan nominal wajib diisi.');
        }

        $payload = [
            $colTanggal => $tanggal,
            $colNama    => $nama,
            $colJumlah  => $jumlah,
        ];

        if ($colIdPes) {
            $payload[$colIdPes] = $idPes > 0 ? $idPes : null;
        }
        if ($colCreated) {
            $payload[$colCreated] = date('Y-m-d H:i:s');
        }

        $db->table('pengeluaran_operasional')->insert($payload);

        return redirect()->to(site_url('admin/laporan'))->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    public function updatePengeluaran($id)
    {
        $db = db_connect();
        [$colTanggal, $colNama, $colJumlah, $colIdPes, $colCreated] = $this->pengeluaranCols($db);

        $id = (int) $id;
        if ($id <= 0) return redirect()->to(site_url('admin/laporan'))->with('error', 'ID tidak valid.');

        $tanggal = $this->request->getPost('tanggal');
        $nama    = trim((string) $this->request->getPost('nama_pengeluaran'));
        $jumlah  = (int) $this->request->getPost('jumlah');
        $idPes   = (int) $this->request->getPost('id_pemesanan');

        if ($tanggal === '' || $nama === '' || $jumlah <= 0) {
            return redirect()->back()->withInput()->with('error', 'Tanggal, nama, dan nominal wajib diisi.');
        }

        $payload = [
            $colTanggal => $tanggal,
            $colNama    => $nama,
            $colJumlah  => $jumlah,
        ];

        if ($colIdPes) {
            $payload[$colIdPes] = $idPes > 0 ? $idPes : null;
        }

        $db->table('pengeluaran_operasional')
            ->where('id_pengeluaran', $id)
            ->update($payload);

        return redirect()->to(site_url('admin/laporan'))->with('success', 'Pengeluaran berhasil diupdate.');
    }

    public function deletePengeluaran($id)
    {
        $db = db_connect();
        $id = (int) $id;

        if ($id <= 0) return redirect()->to(site_url('admin/laporan'))->with('error', 'ID tidak valid.');

        $db->table('pengeluaran_operasional')
            ->where('id_pengeluaran', $id)
            ->delete();

        return redirect()->to(site_url('admin/laporan'))->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
