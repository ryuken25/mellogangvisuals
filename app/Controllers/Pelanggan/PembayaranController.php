<?php

namespace App\Controllers\Pelanggan;

use App\Controllers\BaseController;

class PembayaranController extends BaseController
{
    private function guard()
    {
        if (!session()->get('logged_in')) return redirect()->to(site_url('login'));
        if (session()->get('role') !== 'pelanggan') return redirect()->to(site_url('/'));
        return null;
    }

    private function normStatus($s): string
    {
        return strtolower(trim((string)$s));
    }

    private function hitungRekap($db, int $idPemesanan): array
    {
        $rows = $db->table('pembayaran')
            ->select('jenis_pembayaran, status_verifikasi, jumlah_bayar')
            ->where('id_pemesanan', $idPemesanan)
            ->get()->getResultArray();

        $dpValid = 0;
        $totalValid = 0;
        $pendingCount = 0;

        foreach ($rows as $r) {
            $st = $this->normStatus($r['status_verifikasi'] ?? '');
            $jumlah = (int)($r['jumlah_bayar'] ?? 0);

            if ($st === 'menunggu') $pendingCount++;

            if ($st !== 'valid') continue;
            $totalValid += $jumlah;

            if (($r['jenis_pembayaran'] ?? '') === 'DP') {
                $dpValid += $jumlah;
            }
        }

        return [$dpValid, $totalValid, $pendingCount];
    }

    public function create($idPemesanan)
    {
        if ($resp = $this->guard()) return $resp;

        $db = db_connect();
        $idUser = (int) session()->get('id_user');

        $order = $db->table('pemesanan')
            ->select('id_pemesanan,kode_pemesanan,id_user,total_biaya,status_pemesanan')
            ->where('id_pemesanan', (int)$idPemesanan)
            ->get()->getRowArray();

        if (!$order || (int)$order['id_user'] !== $idUser) {
            return redirect()->to(site_url('status-pesanan'))
                ->with('error', 'Pesanan tidak ditemukan / bukan milik kamu.');
        }

        $total = (int)($order['total_biaya'] ?? 0);
        $dpDue = (int) ceil($total * 0.5);

        [$dpValid, $totalValid, $pendingCount] = $this->hitungRekap($db, (int)$idPemesanan);
        $sisa = max(0, $total - $totalValid);

        // kalau ada yang menunggu, jangan upload lagi -> lihat riwayat
        if ($pendingCount > 0) {
            return redirect()->to(site_url('pelanggan/pembayaran/riwayat/'.$order['id_pemesanan']))
                ->with('error', 'Masih ada pembayaran yang menunggu verifikasi. Cek riwayat dulu ya.');
        }

        $allowDP = ($dpValid <= 0);
        $pelunasanDue = $allowDP ? $total : $sisa;

        if ($sisa <= 0) {
            return redirect()->to(site_url('pelanggan/pembayaran/riwayat/'.$order['id_pemesanan']))
                ->with('success', 'Pesanan sudah lunas.');
        }

        return view('pelanggan/pembayaran/upload', [
            'order'        => $order,
            'total'        => $total,
            'dpDue'        => $dpDue,
            'pelunasanDue' => $pelunasanDue,
            'dpValid'      => $dpValid,
            'totalValid'   => $totalValid,
            'sisa'         => $sisa,
            'allowDP'      => $allowDP,
        ]);
    }

    public function store($idPemesanan)
    {
        if ($resp = $this->guard()) return $resp;

        $rules = [
            'jenis_pembayaran'  => 'required|in_list[DP,Pelunasan]',
            'metode_pembayaran' => 'required|max_length[50]',
            'jumlah_bayar'      => 'required|is_natural_no_zero',
            'bukti_bayar'       => 'uploaded[bukti_bayar]|max_size[bukti_bayar,2048]|ext_in[bukti_bayar,jpg,jpeg,png]|mime_in[bukti_bayar,image/jpg,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal. Pastikan file JPG/PNG max 2MB.');
        }

        $db = db_connect();
        $idUser = (int) session()->get('id_user');

        $order = $db->table('pemesanan')
            ->select('id_pemesanan,kode_pemesanan,id_user,total_biaya')
            ->where('id_pemesanan', (int)$idPemesanan)
            ->get()->getRowArray();

        if (!$order || (int)$order['id_user'] !== $idUser) {
            return redirect()->to(site_url('status-pesanan'))
                ->with('error', 'Pesanan tidak ditemukan / bukan milik kamu.');
        }

        $total = (int)($order['total_biaya'] ?? 0);
        $dpDue = (int) ceil($total * 0.5);

        [$dpValid, $totalValid, $pendingCount] = $this->hitungRekap($db, (int)$idPemesanan);
        $sisa = max(0, $total - $totalValid);

        if ($pendingCount > 0) {
            return redirect()->to(site_url('pelanggan/pembayaran/riwayat/'.$order['id_pemesanan']))
                ->with('error', 'Masih ada pembayaran yang menunggu verifikasi. Cek riwayat dulu ya.');
        }

        $jenis  = $this->request->getPost('jenis_pembayaran');
        $metode = $this->request->getPost('metode_pembayaran');
        $jumlah = (int) $this->request->getPost('jumlah_bayar');

        // DP harus tepat 50%
        if ($jenis === 'DP') {
            if ($dpValid > 0) {
                return redirect()->back()->withInput()->with('error', 'DP sudah pernah dibayar & valid. Silakan pilih Pelunasan.');
            }
            if ($jumlah !== $dpDue) {
                return redirect()->back()->withInput()->with('error', 'Jumlah DP harus tepat 50%: Rp ' . number_format($dpDue,0,',','.'));
            }
        } else { // Pelunasan
            $expected = ($dpValid > 0) ? $sisa : $total; // sisa kalau DP valid, full kalau belum
            if ($jumlah !== $expected) {
                return redirect()->back()->withInput()->with('error', 'Jumlah Pelunasan harus Rp ' . number_format($expected,0,',','.') . '.');
            }
        }

        $file = $this->request->getFile('bukti_bayar');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->withInput()->with('error', 'File upload tidak valid.');
        }

        $uploadPath = WRITEPATH . 'uploads/pembayaran';
        if (!is_dir($uploadPath)) mkdir($uploadPath, 0775, true);

        $ext = strtolower($file->getClientExtension());
        $newName = 'bukti_' . $order['kode_pemesanan'] . '_' . strtolower($jenis) . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $file->move($uploadPath, $newName);

        $db->table('pembayaran')->insert([
            'id_pemesanan'       => (int)$idPemesanan,
            'jenis_pembayaran'   => $jenis,
            'tanggal_bayar'      => date('Y-m-d H:i:s'),
            'metode_pembayaran'  => $metode,
            'jumlah_bayar'       => $jumlah,
            'bukti_bayar'        => $newName,
            'status_verifikasi'  => 'Menunggu',
            'catatan_verifikasi' => null,
        ]);

        return redirect()->to(site_url('pelanggan/pembayaran/riwayat/'.$order['id_pemesanan']))
            ->with('success', 'Bukti pembayaran terkirim. Menunggu verifikasi admin.');
    }

    public function riwayat($idPemesanan)
    {
        if ($resp = $this->guard()) return $resp;

        $db = db_connect();
        $idUser = (int) session()->get('id_user');

        $order = $db->table('pemesanan')
            ->select('id_pemesanan,kode_pemesanan,id_user,total_biaya')
            ->where('id_pemesanan', (int)$idPemesanan)
            ->get()->getRowArray();

        if (!$order || (int)$order['id_user'] !== $idUser) {
            return redirect()->to(site_url('status-pesanan'))
                ->with('error', 'Pesanan tidak ditemukan / bukan milik kamu.');
        }

        $rows = $db->table('pembayaran')
            ->where('id_pemesanan', (int)$idPemesanan)
            ->orderBy('id_pembayaran', 'DESC')
            ->get()->getResultArray();

        $total = (int)($order['total_biaya'] ?? 0);

        $valid = 0;
        $dpValid = 0;
        $pendingCount = 0;

        foreach ($rows as $r) {
            $st = $this->normStatus($r['status_verifikasi'] ?? '');
            if ($st === 'menunggu') $pendingCount++;

            if ($st === 'valid') {
                $valid += (int)$r['jumlah_bayar'];
                if (($r['jenis_pembayaran'] ?? '') === 'DP') $dpValid += (int)$r['jumlah_bayar'];
            }
        }

        $sisa = max(0, $total - $valid);

        return view('pelanggan/pembayaran/riwayat', [
            'order'        => $order,
            'rows'         => $rows,
            'total'        => $total,
            'valid'        => $valid,
            'sisa'         => $sisa,
            'dpValid'      => $dpValid,
            'pendingCount' => $pendingCount,
        ]);
    }

    // preview bukti (inline) untuk pelanggan pemilik pesanan
    public function file($idPembayaran)
    {
        if ($resp = $this->guard()) return $resp;

        $db = db_connect();
        $idUser = (int) session()->get('id_user');

        $row = $db->table('pembayaran p')
            ->select('p.bukti_bayar, pm.id_user')
            ->join('pemesanan pm', 'pm.id_pemesanan = p.id_pemesanan', 'left')
            ->where('p.id_pembayaran', (int)$idPembayaran)
            ->get()->getRowArray();

        if (!$row || empty($row['bukti_bayar']) || (int)$row['id_user'] !== $idUser) {
            return redirect()->back()->with('error', 'File tidak ditemukan / akses ditolak.');
        }

        $filename = basename($row['bukti_bayar']);
        $path = WRITEPATH . 'uploads/pembayaran/' . $filename;

        if (!is_file($path)) {
            return redirect()->back()->with('error', 'File tidak ditemukan di server.');
        }

        $mime = @mime_content_type($path) ?: 'image/jpeg';
        return $this->response->setContentType($mime)->setBody(file_get_contents($path));
    }

    // ====== GANTI / REPLACE BUKTI ======

    public function edit($idPembayaran)
    {
        if ($resp = $this->guard()) return $resp;

        $db = db_connect();
        $idUser = (int) session()->get('id_user');

        $row = $db->table('pembayaran p')
            ->select('p.*, pm.kode_pemesanan, pm.id_user')
            ->join('pemesanan pm', 'pm.id_pemesanan = p.id_pemesanan', 'left')
            ->where('p.id_pembayaran', (int)$idPembayaran)
            ->get()->getRowArray();

        if (!$row || (int)$row['id_user'] !== $idUser) {
            return redirect()->to(site_url('status-pesanan'))
                ->with('error', 'Data pembayaran tidak ditemukan / bukan milik kamu.');
        }

        $st = $this->normStatus($row['status_verifikasi'] ?? '');
        if (!in_array($st, ['menunggu','ditolak'], true)) {
            return redirect()->to(site_url('pelanggan/pembayaran/riwayat/'.$row['id_pemesanan']))
                ->with('error', 'Bukti tidak bisa diganti karena sudah diverifikasi.');
        }

        return view('pelanggan/pembayaran/ganti', ['row' => $row]);
    }

    public function update($idPembayaran)
    {
        if ($resp = $this->guard()) return $resp;

        $rules = [
            'bukti_bayar' => 'uploaded[bukti_bayar]|max_size[bukti_bayar,2048]|ext_in[bukti_bayar,jpg,jpeg,png]|mime_in[bukti_bayar,image/jpg,image/jpeg,image/png]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal. File harus JPG/PNG max 2MB.');
        }

        $db = db_connect();
        $idUser = (int) session()->get('id_user');

        $row = $db->table('pembayaran p')
            ->select('p.*, pm.kode_pemesanan, pm.id_user')
            ->join('pemesanan pm', 'pm.id_pemesanan = p.id_pemesanan', 'left')
            ->where('p.id_pembayaran', (int)$idPembayaran)
            ->get()->getRowArray();

        if (!$row || (int)$row['id_user'] !== $idUser) {
            return redirect()->to(site_url('status-pesanan'))
                ->with('error', 'Data pembayaran tidak ditemukan / bukan milik kamu.');
        }

        $st = $this->normStatus($row['status_verifikasi'] ?? '');
        if (!in_array($st, ['menunggu','ditolak'], true)) {
            return redirect()->to(site_url('pelanggan/pembayaran/riwayat/'.$row['id_pemesanan']))
                ->with('error', 'Bukti tidak bisa diganti karena sudah diverifikasi.');
        }

        $file = $this->request->getFile('bukti_bayar');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File upload tidak valid.');
        }

        $uploadPath = WRITEPATH . 'uploads/pembayaran';
        if (!is_dir($uploadPath)) mkdir($uploadPath, 0775, true);

        $ext = strtolower($file->getClientExtension());
        $newName = 'bukti_' . ($row['kode_pemesanan'] ?? 'MLG') . '_' . strtolower($row['jenis_pembayaran'] ?? 'bayar') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        $file->move($uploadPath, $newName);

        // hapus file lama
        if (!empty($row['bukti_bayar'])) {
            $oldPath = $uploadPath . '/' . basename($row['bukti_bayar']);
            if (is_file($oldPath)) @unlink($oldPath);
        }

        $db->table('pembayaran')->where('id_pembayaran', (int)$idPembayaran)->update([
            'bukti_bayar'        => $newName,
            'tanggal_bayar'      => date('Y-m-d H:i:s'),
            'status_verifikasi'  => 'Menunggu',
            'catatan_verifikasi' => null,
        ]);

        return redirect()->to(site_url('pelanggan/pembayaran/riwayat/'.$row['id_pemesanan']))
            ->with('success', 'Bukti pembayaran berhasil diganti. Menunggu verifikasi admin.');
    }
}
