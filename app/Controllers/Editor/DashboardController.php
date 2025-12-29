<?php

namespace App\Controllers\Editor;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    private function guard()
    {
        if (!session()->get('logged_in')) return redirect()->to(site_url('login'))->send();
        if (session()->get('role') !== 'editor') return redirect()->to(site_url('/'))->send();
    }

    private function countByStatus($db, int $idEditor, string $status): int
    {
        // pakai LOWER biar aman beda kapital
        return (int) $db->table('jadwal_produksi')
            ->where('id_editor', $idEditor)
            ->where("LOWER(status_produksi) = " . $db->escape(strtolower($status)), null, false)
            ->countAllResults();
    }

    public function index()
    {
        $this->guard();

        $db = db_connect();
        $idEditor = (int) session()->get('id_user');

        // A/B/C/D
        $countA = $this->countByStatus($db, $idEditor, 'cut-to-cut');
        $countB = $this->countByStatus($db, $idEditor, 'finishing');
        $countC = $this->countByStatus($db, $idEditor, 'revisi');
        $countD = $this->countByStatus($db, $idEditor, 'done');

        $totalTugas = (int) $db->table('jadwal_produksi')
            ->where('id_editor', $idEditor)
            ->countAllResults();

        // daftar terbaru (limit 8)
        $rows = $db->table('jadwal_produksi j')
            ->select("
                j.id_jadwal, j.status_produksi,
                j.tanggal_mulai_editing, j.tanggal_selesai_editing,
                pm.kode_pemesanan, pm.tanggal_acara,
                pk.nama_paket,
                u.nama_lengkap AS nama_pelanggan
            ")
            ->join('pemesanan pm', 'pm.id_pemesanan = j.id_pemesanan', 'left')
            ->join('paket pk', 'pk.id_paket = pm.id_paket', 'left')
            ->join('user u', 'u.id_user = pm.id_user', 'left')
            ->where('j.id_editor', $idEditor)
            ->orderBy('j.id_jadwal', 'DESC')
            ->limit(8)
            ->get()->getResultArray();

        return view('editor/dashboard/index', [
            'title' => 'Dashboard Editor',
            'countA' => $countA,
            'countB' => $countB,
            'countC' => $countC,
            'countD' => $countD,
            'totalTugas' => $totalTugas,
            'rows' => $rows,
        ]);
    }
}
