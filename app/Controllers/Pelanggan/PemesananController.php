<?php

namespace App\Controllers\Pelanggan;

use App\Controllers\BaseController;
use App\Models\PaketModel;

class PemesananController extends BaseController
{
    private function guard()
    {
        if (!session()->get('logged_in')) return redirect()->to(site_url('login'))->send();
        if (session()->get('role') !== 'pelanggan') return redirect()->to(site_url('/'))->send();
    }

    private function norm($s): string
    {
        return strtolower(trim((string)$s));
    }

    public function create($idPaket = null)
    {
        $this->guard();

        $paketModel = new PaketModel();
        $paket = $paketModel->where('is_active', 1)->orderBy('harga', 'ASC')->findAll();

        $selectedId = $idPaket ? (int)$idPaket : (int) old('id_paket');

        return view('pelanggan/pemesanan/create', [
            'title' => 'Buat Pemesanan',
            'paket' => $paket,
            'selectedId' => $selectedId,
        ]);
    }

    private function genKode($db): string
    {
        do {
            $kode = 'MLG' . date('ymd') . '-' . str_pad((string)rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $exists = $db->table('pemesanan')->where('kode_pemesanan', $kode)->countAllResults() > 0;
        } while ($exists);
        return $kode;
    }

    /**
     * Endpoint availability pelanggan (2 fotografer per tanggal).
     * Hitung dari jadwal_produksi.tanggal_shooting.
     */
    public function availability()
    {
        $this->guard();

        $db = db_connect();
        $date = trim((string)$this->request->getGet('date'));

        if (!preg_match('~^\d{4}-\d{2}-\d{2}$~', $date)) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Invalid date',
            ]);
        }

        $booked = (int)$db->table('jadwal_produksi')
            ->where('tanggal_shooting', $date)
            ->countAllResults();

        $capacity = 2;
        $remaining = max($capacity - $booked, 0);

        return $this->response->setJSON([
            'ok' => true,
            'date' => $date,
            'capacity' => $capacity,
            'booked' => $booked,
            'remaining' => $remaining,
        ]);
    }

    public function store()
    {
        $this->guard();

        $rules = [
            'id_paket' => 'required|is_natural_no_zero',
            'tanggal_acara' => 'required',
            'jam_mulai_acara' => 'required',
            'lokasi_acara' => 'required|max_length[150]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal.');
        }

        $db = db_connect();
        $idUser = (int) session()->get('id_user');

        $idPaket = (int)$this->request->getPost('id_paket');

        $paket = $db->table('paket')->where('id_paket', $idPaket)->get()->getRowArray();
        $hargaPaket = (int)($paket['harga'] ?? 0);

        if ($hargaPaket <= 0) {
            return redirect()->back()->withInput()->with('error', 'Paket tidak valid.');
        }

        $kode = $this->genKode($db);

        $tanggalAcara = (string)$this->request->getPost('tanggal_acara');
        $jamMulai = (string)$this->request->getPost('jam_mulai_acara');

        // basic validate format
        if (!preg_match('~^\d{4}-\d{2}-\d{2}$~', $tanggalAcara)) {
            return redirect()->back()->withInput()->with('error', 'Format tanggal acara tidak valid.');
        }
        if (!preg_match('~^\d{2}:\d{2}(:\d{2})?$~', $jamMulai)) {
            return redirect()->back()->withInput()->with('error', 'Format jam mulai tidak valid.');
        }

        $db->table('pemesanan')->insert([
            'kode_pemesanan' => $kode,
            'id_user' => $idUser,
            'id_paket' => $idPaket,
            'tanggal_pemesanan' => date('Y-m-d H:i:s'),
            'tanggal_acara' => $tanggalAcara,
            'jam_mulai_acara' => $jamMulai,
            'lokasi_acara' => $this->request->getPost('lokasi_acara'),
            'status_pemesanan' => 'menunggu pembayaran',
            'total_biaya' => $hargaPaket,
            'catatan_pelanggan' => $this->request->getPost('catatan_pelanggan'),
            'catatan_admin' => null,
        ]);

        return redirect()->to(site_url('pelanggan'))->with('success', 'Pemesanan berhasil dibuat. Kode: '.$kode);
    }
}
