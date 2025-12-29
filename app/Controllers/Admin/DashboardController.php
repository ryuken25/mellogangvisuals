<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $countPaket  = $db->table('paket')->countAllResults();
        $countPorto  = $db->table('portofolio')->countAllResults();
        $countOrder  = $db->table('pemesanan')->countAllResults();
        $pendingPay  = $db->table('pembayaran')->where('status_verifikasi', 'Menunggu')->countAllResults();

        $orders = $db->table('pemesanan p')
            ->select('p.id_pemesanan, p.kode_pemesanan, p.status_pemesanan, p.tanggal_pemesanan, u.nama_lengkap, pk.nama_paket')
            ->join('user u', 'u.id_user = p.id_user', 'left')
            ->join('paket pk', 'pk.id_paket = p.id_paket', 'left')
            ->orderBy('p.id_pemesanan', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        return view('admin/dashboard/index', [
            'title' => 'Admin Dashboard',
            'countPaket' => $countPaket,
            'countPorto' => $countPorto,
            'countOrder' => $countOrder,
            'pendingPay' => $pendingPay,
            'orders' => $orders,
        ]);
    }
}
