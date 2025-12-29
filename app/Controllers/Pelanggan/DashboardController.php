<?php

namespace App\Controllers\Pelanggan;

use App\Controllers\BaseController;

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

        return view('pelanggan/dashboard/index', [
            'title' => 'Dashboard Pelanggan',
            'orders' => $orders,
        ]);
    }
}
