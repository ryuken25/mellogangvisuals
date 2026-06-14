<?php

namespace App\Controllers\Pelanggan;

use App\Controllers\BaseController;
use App\Libraries\GoogleAuth;
use App\Models\PortofolioModel;
use App\Support\Status;

class DashboardController extends BaseController
{
    private function guard()
    {
        if (!session()->get('logged_in')) return redirect()->to(site_url('login'))->send();
        if (session()->get('role') !== 'pelanggan') return redirect()->to(site_url('/'))->send();
    }

    public function index()
    {
        $this->guard();

        $db = db_connect();
        $idUser = (int) session()->get('id_user');

        $orders = $db->table('pemesanan p')
            ->select('p.id_pemesanan, p.kode_pemesanan, p.tanggal_acara, p.lokasi_acara, p.status_pemesanan, p.total_biaya, pk.nama_paket')
            ->join('paket pk', 'pk.id_paket = p.id_paket', 'left')
            ->where('p.id_user', $idUser)
            ->orderBy('p.id_pemesanan', 'DESC')
            ->get()->getResultArray();

        // Decorate orders
        foreach ($orders as &$o) {
            $st = (string) ($o['status_pemesanan'] ?? '');
            $o['status_label'] = Status::orderLabel($st);
            $o['status_color'] = Status::orderColor($st);
        }
        unset($o);

        // Preview portofolio untuk customer (max 6)
        $portoModel = new PortofolioModel();
        $portoRaw = $portoModel->orderBy('id_portfolio', 'DESC')->limit(6)->find();
        $portoMap = [];
        foreach ($portoRaw as &$po) {
            $thumb = base_url('assets/images/porto_placeholder.png');
            $tn = (string)($po['thumbnail'] ?? '');
            if ($tn !== '') $thumb = base_url('uploads/portofolio/' . $tn);
            $po['thumb'] = $thumb;
        }
        unset($po);

        // Cek apakah Google login diaktifkan (untuk tombol cepat)
        $googleOn = false;
        try { $googleOn = (new GoogleAuth())->isConfigured(); } catch (\Throwable $e) {}

        $nama = (string) session()->get('nama_lengkap') ?: 'there';

        return view('pelanggan/dashboard/index', [
            'title'  => 'Dashboard',
            'orders' => $orders,
            'porto'  => $portoRaw,
            'googleOn' => $googleOn,
            'nama'   => $nama,
        ]);
    }
}
