<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class PemesananController extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $status = $this->request->getGet('status');
        $kode   = $this->request->getGet('kode');

        $builder = $db->table('pemesanan p')
            ->select('p.id_pemesanan, p.kode_pemesanan, p.status_pemesanan, p.tanggal_pemesanan, p.total_biaya,
                     u.nama_lengkap, pk.nama_paket')
            ->join('user u', 'u.id_user = p.id_user', 'left')
            ->join('paket pk', 'pk.id_paket = p.id_paket', 'left')
            ->orderBy('p.id_pemesanan', 'DESC');

        if ($status) $builder->where('p.status_pemesanan', $status);
        if ($kode)   $builder->like('p.kode_pemesanan', $kode);

        $rows = $builder->get()->getResultArray();

        return view('admin/pemesanan/index', [
            'title' => 'Admin - Pemesanan',
            'rows'  => $rows,
            'status'=> $status,
            'kode'  => $kode,
        ]);
    }

    public function show($id)
    {
        $db = db_connect();

        $order = $db->table('pemesanan p')
            ->select('p.*, u.nama_lengkap, u.email, u.no_telepon, pk.nama_paket, pk.kategori')
            ->join('user u', 'u.id_user = p.id_user', 'left')
            ->join('paket pk', 'pk.id_paket = p.id_paket', 'left')
            ->where('p.id_pemesanan', (int)$id)
            ->get()->getRowArray();

        if (! $order) return redirect()->to(site_url('admin/pemesanan'));

        $pay    = $db->table('pembayaran')->where('id_pemesanan', (int)$id)->orderBy('id_pembayaran','DESC')->get()->getResultArray();
        $jadwal = $db->table('jadwal_produksi')->where('id_pemesanan', (int)$id)->get()->getRowArray();

        $totalValid = 0;
        foreach ($pay as $p) {
            if (strtolower((string)($p['status_verifikasi'] ?? '')) === 'valid') {
                $totalValid += (int)($p['jumlah_bayar'] ?? 0);
            }
        }
        $totalOrder = (int)($order['total_biaya'] ?? 0);
        $sisa = max(0, $totalOrder - $totalValid);

        return view('admin/pemesanan/show', [
            'title'      => 'Detail Pemesanan',
            'order'      => $order,
            'pay'        => $pay,
            'jadwal'     => $jadwal,
            'totalValid' => $totalValid,
            'sisa'       => $sisa,
        ]);
    }

    public function delete($id)
    {
        $db = db_connect();
        $id = (int)$id;

        $order = $db->table('pemesanan')->where('id_pemesanan', $id)->get()->getRowArray();
        if (!$order) {
            return redirect()->to(site_url('admin/pemesanan'))->with('error', 'Pemesanan tidak ditemukan.');
        }

        $db->transStart();
        $db->table('pembayaran')->where('id_pemesanan', $id)->delete();
        $db->table('detail_pemesanan')->where('id_pemesanan', $id)->delete();
        $db->table('jadwal_produksi')->where('id_pemesanan', $id)->delete();
        $db->table('pemesanan')->where('id_pemesanan', $id)->delete();
        $db->transComplete();

        return redirect()->to(site_url('admin/pemesanan'))->with('success', 'Pemesanan berhasil dihapus.');
    }

    // invoice admin (tetap sama gaya kamu)
    public function invoice($id)
    {
        $db = db_connect();

        $order = $db->table('pemesanan p')
            ->select('p.*, u.nama_lengkap, u.email, u.no_telepon, pk.nama_paket, pk.kategori')
            ->join('user u', 'u.id_user = p.id_user', 'left')
            ->join('paket pk', 'pk.id_paket = p.id_paket', 'left')
            ->where('p.id_pemesanan', (int)$id)
            ->get()->getRowArray();

        if (! $order) return redirect()->to(site_url('admin/pemesanan'))->with('error', 'Pemesanan tidak ditemukan.');

        $paymentsValid = $db->table('pembayaran')
            ->where('id_pemesanan', (int)$id)
            ->where('LOWER(status_verifikasi)', 'valid')
            ->orderBy('id_pembayaran', 'ASC')
            ->get()->getResultArray();

        if (empty($paymentsValid)) {
            return redirect()->to(site_url('admin/pemesanan/'.$id))
                ->with('error', 'Invoice hanya tersedia jika sudah ada pembayaran yang valid (DP atau Pelunasan).');
        }

        $totalOrder = (int)($order['total_biaya'] ?? 0);
        $totalValid = 0;
        foreach ($paymentsValid as $p) $totalValid += (int)($p['jumlah_bayar'] ?? 0);

        $sisa = max(0, $totalOrder - $totalValid);
        $invoiceNo = 'INV-' . ($order['kode_pemesanan'] ?? ('ORD-'.$order['id_pemesanan']));

        return view('admin/pemesanan/invoice', [
            'title'        => 'Invoice - ' . ($order['kode_pemesanan'] ?? ''),
            'order'        => $order,
            'invoiceNo'    => $invoiceNo,
            'paymentsValid'=> $paymentsValid,
            'totalOrder'   => $totalOrder,
            'totalValid'   => $totalValid,
            'sisa'         => $sisa,
        ]);
    }
}
