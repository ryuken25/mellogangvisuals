<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\GoogleAuth;
use App\Support\Status;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $countPaket  = (int) $db->table('paket')->countAllResults();
        $countPorto  = (int) $db->table('portofolio')->countAllResults();
        $countOrder  = (int) $db->table('pemesanan')->countAllResults();
        $pendingPay  = (int) $db->table('pembayaran')
            ->where('status_verifikasi', Status::VERIF_MENUNGGU)
            ->countAllResults();

        $orders = $db->table('pemesanan p')
            ->select('p.id_pemesanan, p.kode_pemesanan, p.status_pemesanan, p.tanggal_pemesanan, p.total_biaya, u.nama_lengkap, pk.nama_paket')
            ->join('user u', 'u.id_user = p.id_user', 'left')
            ->join('paket pk', 'pk.id_paket = p.id_paket', 'left')
            ->orderBy('p.id_pemesanan', 'DESC')
            ->limit(8)
            ->get()->getResultArray();

        foreach ($orders as &$o) {
            $st = (string) ($o['status_pemesanan'] ?? '');
            $o['status_label'] = Status::orderLabel($st);
            $o['status_color'] = Status::orderColor($st);
        }
        unset($o);

        // Preview portofolio + paket (top 4 each)
        $portos = $db->table('portofolio')
            ->orderBy('id_portfolio', 'DESC')
            ->limit(4)
            ->get()->getResultArray();
        foreach ($portos as &$po) {
            $tn = (string)($po['thumbnail'] ?? '');
            $po['thumb'] = $tn !== ''
                ? base_url('uploads/portofolio/' . $tn)
                : base_url('assets/images/porto_placeholder.png');
        }
        unset($po);

        $googleOn = false;
        try { $googleOn = (new GoogleAuth())->isConfigured(); } catch (\Throwable $e) {}

        return view('admin/dashboard/index', [
            'title'      => 'Admin dashboard',
            'countPaket' => $countPaket,
            'countPorto' => $countPorto,
            'countOrder' => $countOrder,
            'pendingPay' => $pendingPay,
            'orders'     => $orders,
            'portos'     => $portos,
            'googleOn'   => $googleOn,
        ]);
    }
}
