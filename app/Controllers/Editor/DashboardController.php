<?php

namespace App\Controllers\Editor;

use App\Controllers\BaseController;
use App\Support\Status;

class DashboardController extends BaseController
{
    private function guard()
    {
        if (!session()->get('logged_in')) return redirect()->to(site_url('login'))->send();
        if (session()->get('role') !== 'editor') return redirect()->to(site_url('/'))->send();
    }

    private function countByStatus($db, int $idEditor, string $status): int
    {
        // status_produksi sudah kanonik snake_case -> banding langsung (bound, index-friendly)
        return (int) $db->table('jadwal_produksi')
            ->where('id_editor', $idEditor)
            ->where('status_produksi', $status)
            ->countAllResults();
    }

    public function index()
    {
        $this->guard();

        $db = db_connect();
        $idEditor = (int) session()->get('id_user');

        // A/B/C/D
        $countA = $this->countByStatus($db, $idEditor, Status::PROD_CUT_TO_CUT);
        $countB = $this->countByStatus($db, $idEditor, Status::PROD_FINISHING);
        $countC = $this->countByStatus($db, $idEditor, Status::PROD_REVISI);
        $countD_done   = $this->countByStatus($db, $idEditor, Status::PROD_DONE);
        $countD_revisi = $this->countByStatus($db, $idEditor, Status::PROD_REVISI_SELESAI);
        $countD = $countD_done + $countD_revisi;

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

        // tugas untuk pop-up: 2 kategori
        $today = date('Y-m-d');

        // "Sedang Berlangsung" = sudah mulai editing (tanggal_mulai_editing <= hari ini)
        // dan belum selesai (bukan done / revisi selesai)
        $tugasBerlangsung = $db->table('jadwal_produksi j')
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
            ->whereNotIn('j.status_produksi', [Status::PROD_DONE, Status::PROD_REVISI_SELESAI])
            ->where('j.tanggal_mulai_editing <=', $today)
            ->orderBy('j.tanggal_selesai_editing', 'ASC')  // deadline terdekat (paling telat) di atas
            ->get()->getResultArray();

        // "Mendatang" = belum mulai editing (tanggal_mulai_editing > hari ini ATAU NULL)
        // dan belum selesai (bukan done / revisi selesai)
        $tugasMendatang = $db->table('jadwal_produksi j')
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
            ->whereNotIn('j.status_produksi', [Status::PROD_DONE, Status::PROD_REVISI_SELESAI])
            ->groupStart()
                ->where('j.tanggal_mulai_editing >', $today)
                ->orWhere('j.tanggal_mulai_editing', null)
            ->groupEnd()
            ->orderBy('j.tanggal_mulai_editing', 'ASC')
            ->get()->getResultArray();

        // flag: tampilkan popup setiap kali buka dashboard (jika ada data)
        $showPopup = !empty($tugasBerlangsung) || !empty($tugasMendatang);

        return view('editor/dashboard/index', [
            'title'            => 'Dashboard Editor',
            'countA'           => $countA,
            'countB'           => $countB,
            'countC'           => $countC,
            'countD'           => $countD,
            'totalTugas'       => $totalTugas,
            'rows'             => $rows,
            'tugasBerlangsung' => $tugasBerlangsung,
            'tugasMendatang'   => $tugasMendatang,
            'showPopup'        => $showPopup,
            'today'            => $today,
            // pass to layout so modal fires AFTER bootstrap bundle is loaded
            'autoShowModal'    => $showPopup ? 'tugasModal' : '',
        ]);
    }
}
