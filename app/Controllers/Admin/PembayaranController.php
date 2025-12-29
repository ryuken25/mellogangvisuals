<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class PembayaranController extends BaseController
{
    private function normStatus($s): string
    {
        return strtolower(trim((string)$s));
    }

    public function index()
    {
        $db = db_connect();
        $status = $this->request->getGet('status'); // menunggu|valid|ditolak|all

        $q = $db->table('pembayaran p')
            ->select('p.id_pembayaran, p.id_pemesanan, p.jenis_pembayaran, p.tanggal_bayar, p.metode_pembayaran, p.jumlah_bayar, p.bukti_bayar, p.status_verifikasi,
                      pm.kode_pemesanan, pm.total_biaya,
                      u.nama_lengkap')
            ->join('pemesanan pm', 'pm.id_pemesanan = p.id_pemesanan', 'left')
            ->join('user u', 'u.id_user = pm.id_user', 'left')
            ->orderBy('p.id_pembayaran', 'DESC');

        if ($status && strtolower($status) !== 'all') {
            $st = strtolower($status);
            // aman untuk case "Menunggu" / "menunggu"
            $q->where("LOWER(p.status_verifikasi) = '{$st}'", null, false);
        }

        $rows = $q->get()->getResultArray();

        return view('admin/pembayaran/index', [
            'title'  => 'Pembayaran',
            'rows'   => $rows,
            'status' => $status,
        ]);
    }

    public function verifyForm($idPembayaran)
    {
        $db = db_connect();

        $row = $db->table('pembayaran p')
            ->select('p.*, pm.kode_pemesanan, pm.total_biaya, pm.status_pemesanan, u.nama_lengkap')
            ->join('pemesanan pm', 'pm.id_pemesanan = p.id_pemesanan', 'left')
            ->join('user u', 'u.id_user = pm.id_user', 'left')
            ->where('p.id_pembayaran', (int)$idPembayaran)
            ->get()->getRowArray();

        if (!$row) {
            return redirect()->to(site_url('admin/pembayaran'))->with('error', 'Data pembayaran tidak ditemukan.');
        }

        return view('admin/pembayaran/verify', [
            'title' => 'Verifikasi Pembayaran',
            'row'   => $row,
        ]);
    }

    public function verify($idPembayaran)
    {
        $db = db_connect();

        $status = $this->normStatus($this->request->getPost('status_verifikasi'));
        $catatan = (string)$this->request->getPost('catatan_verifikasi');

        if (!in_array($status, ['menunggu', 'valid', 'ditolak'], true)) {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }

        $row = $db->table('pembayaran')->where('id_pembayaran', (int)$idPembayaran)->get()->getRowArray();
        if (!$row) {
            return redirect()->to(site_url('admin/pembayaran'))->with('error', 'Data pembayaran tidak ditemukan.');
        }

        // Simpan "Menunggu" (kapital) biar konsisten sama data kamu
        $storeStatus = ($status === 'menunggu') ? 'Menunggu' : $status;

        $db->table('pembayaran')->where('id_pembayaran', (int)$idPembayaran)->update([
            'status_verifikasi'  => $storeStatus,
            'catatan_verifikasi' => $catatan,
        ]);

        // ===== Update status pemesanan dengan aman (jangan ganggu status produksi/terjadwal/dll) =====
        $idPemesanan = (int)($row['id_pemesanan'] ?? 0);
        $order = $db->table('pemesanan')->where('id_pemesanan', $idPemesanan)->get()->getRowArray();

        if ($order) {
            $totalOrder = (int)($order['total_biaya'] ?? 0);
            $currentStatus = $this->normStatus($order['status_pemesanan'] ?? '');

            // status yang boleh di-override otomatis oleh pembayaran
            $allowed = ['menunggu pembayaran', 'menunggu pelunasan', 'menunggu verifikasi', 'lunas'];

            if (in_array($currentStatus, $allowed, true)) {
                $payRows = $db->table('pembayaran')
                    ->select('jenis_pembayaran, jumlah_bayar, status_verifikasi')
                    ->where('id_pemesanan', $idPemesanan)
                    ->get()->getResultArray();

                $sumValid = 0;
                $hasWaiting = false;

                foreach ($payRows as $r) {
                    $st = $this->normStatus($r['status_verifikasi'] ?? '');
                    if ($st === 'menunggu') $hasWaiting = true;
                    if ($st !== 'valid') continue;
                    $sumValid += (int)($r['jumlah_bayar'] ?? 0);
                }

                $newStatus = 'menunggu pembayaran';
                if ($totalOrder > 0 && $sumValid >= $totalOrder) {
                    $newStatus = 'lunas';
                } elseif ($hasWaiting) {
                    $newStatus = 'menunggu verifikasi';
                } elseif ($sumValid > 0) {
                    $newStatus = 'menunggu pelunasan';
                }

                $db->table('pemesanan')->where('id_pemesanan', $idPemesanan)->update([
                    'status_pemesanan' => $newStatus,
                ]);
            }
        }

        return redirect()->to(site_url('admin/pembayaran/verify/'.$idPembayaran))
            ->with('success', 'Verifikasi tersimpan.');
    }

    /**
     * Preview/Download bukti bayar by id_pembayaran
     * - default: inline (preview)
     * - download pakai ?download=1
     */
    public function file($idPembayaran)
    {
        $db = db_connect();

        $row = $db->table('pembayaran p')
            ->select('p.id_pembayaran, p.bukti_bayar, p.jenis_pembayaran, pm.kode_pemesanan')
            ->join('pemesanan pm', 'pm.id_pemesanan = p.id_pemesanan', 'left')
            ->where('p.id_pembayaran', (int)$idPembayaran)
            ->get()->getRowArray();

        if (!$row || empty($row['bukti_bayar'])) {
            return redirect()->back()->with('error', 'Bukti bayar tidak ditemukan.');
        }

        $filename = basename((string)$row['bukti_bayar']);
        $path = WRITEPATH . 'uploads/pembayaran/' . $filename;

        if (!is_file($path)) {
            return redirect()->back()->with('error', 'File tidak ditemukan di server.');
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = 'application/octet-stream';
        if (in_array($ext, ['jpg', 'jpeg'], true)) $mime = 'image/jpeg';
        if ($ext === 'png') $mime = 'image/png';
        if ($ext === 'webp') $mime = 'image/webp';
        if ($ext === 'pdf') $mime = 'application/pdf';

        $downloadName = 'bukti-' . ($row['kode_pemesanan'] ?? 'pembayaran') . '-' . strtolower($row['jenis_pembayaran'] ?? 'bayar') . '.' . $ext;

        $download = (int)($this->request->getGet('download') ?? 0) === 1;
        $disposition = $download ? 'attachment' : 'inline';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', $disposition . '; filename="' . $downloadName . '"')
            ->setBody(file_get_contents($path));
    }
}
