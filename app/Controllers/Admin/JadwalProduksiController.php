<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class JadwalProduksiController extends BaseController
{
    private function norm($s): string
    {
        return strtolower(trim((string)$s));
    }

    private function adminAllowedStatus(): array
    {
        return ['pra produksi', 'shooting', 'cut-to-cut'];
    }

    /** hanya order yang sudah punya pembayaran VALID (DP/lunas) dan belum dibuat jadwal */
    private function getEligibleOrdersForCreate($db): array
    {
        // Ambil order yang ada pembayaran VALID (DP atau pelunasan)
        // dan belum punya jadwal_produksi
        return $db->table('pemesanan p')
            ->select('p.id_pemesanan, p.kode_pemesanan, p.tanggal_acara, p.jam_mulai_acara')
            ->join('pembayaran py', 'py.id_pemesanan = p.id_pemesanan AND LOWER(py.status_verifikasi) = "valid"', 'inner')
            ->join('jadwal_produksi j', 'j.id_pemesanan = p.id_pemesanan', 'left')
            ->where('j.id_pemesanan', null)
            ->groupBy('p.id_pemesanan')
            ->orderBy('p.id_pemesanan', 'DESC')
            ->get()->getResultArray();
    }

    public function index()
    {
        $db = db_connect();

        $rows = $db->table('jadwal_produksi j')
            ->select("j.*, pm.kode_pemesanan, u.nama_lengkap AS nama_pelanggan, e.nama_lengkap AS nama_editor")
            ->join('pemesanan pm', 'pm.id_pemesanan = j.id_pemesanan', 'left')
            ->join('user u', 'u.id_user = pm.id_user', 'left')
            ->join('user e', 'e.id_user = j.id_editor', 'left')
            ->orderBy('j.id_jadwal', 'DESC')
            ->get()->getResultArray();

        return view('admin/jadwal/index', [
            'title' => 'Jadwal Produksi',
            'rows' => $rows,
        ]);
    }

    public function create()
    {
        $db = db_connect();

        $pemesanan = $this->getEligibleOrdersForCreate($db);

        $editors = $db->table('user')
            ->select('id_user,nama_lengkap')
            ->where('role', 'editor')
            ->orderBy('nama_lengkap', 'ASC')
            ->get()->getResultArray();

        return view('admin/jadwal/form', [
            'title' => 'Buat Jadwal',
            'mode' => 'create',
            'pemesanan' => $pemesanan,
            'editors' => $editors,
            'row' => null,
            'allowedStatus' => $this->adminAllowedStatus(),
        ]);
    }

    /** endpoint availability (2 fotografer) */
    public function availability()
    {
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
        $db = db_connect();

        $idPemesanan = (int)$this->request->getPost('id_pemesanan');

        // pastikan pemesanan eligible (punya pembayaran valid dan belum ada jadwal)
        $eligible = $this->getEligibleOrdersForCreate($db);
        $eligibleIds = array_map(fn($x) => (int)$x['id_pemesanan'], $eligible);
        if (!in_array($idPemesanan, $eligibleIds, true)) {
            return redirect()->back()->withInput()->with('error', 'Pemesanan tidak eligible (belum ada pembayaran valid / sudah punya jadwal).');
        }

        // Ambil info request pelanggan (tanggal acara & jam mulai)
        $pm = $db->table('pemesanan')->select('tanggal_acara,jam_mulai_acara')->where('id_pemesanan', $idPemesanan)->get()->getRowArray();
        $fallbackTanggal = (string)($pm['tanggal_acara'] ?? '');
        $fallbackJam = (string)($pm['jam_mulai_acara'] ?? '');

        $tanggalShooting = (string)($this->request->getPost('tanggal_shooting') ?: '');
        $jamMulai = (string)($this->request->getPost('jam_mulai_shooting') ?: '');

        if ($tanggalShooting === '' && $fallbackTanggal !== '') $tanggalShooting = $fallbackTanggal;
        if ($jamMulai === '' && $fallbackJam !== '') $jamMulai = $fallbackJam;

        $data = [
            'id_pemesanan' => $idPemesanan,
            'id_editor' => (int)$this->request->getPost('id_editor'),
            'tanggal_shooting' => $tanggalShooting ?: null,
            'jam_mulai_shooting' => $jamMulai ?: null,
            'jam_selesai_shooting' => $this->request->getPost('jam_selesai_shooting') ?: null,
            'tanggal_mulai_editing' => $this->request->getPost('tanggal_mulai_editing') ?: null,
            'tanggal_selesai_editing' => $this->request->getPost('tanggal_selesai_editing') ?: null,
            'status_produksi' => $this->request->getPost('status_produksi') ?: 'pra produksi',
            'catatan_produksi' => $this->request->getPost('catatan_produksi') ?: null,
        ];

        $st = $this->norm($data['status_produksi']);
        if (!in_array($st, $this->adminAllowedStatus(), true)) {
            $data['status_produksi'] = 'pra produksi';
        }

        $db->table('jadwal_produksi')->insert($data);

        return redirect()->to(site_url('admin/jadwal'))->with('success', 'Jadwal berhasil dibuat.');
    }

    public function edit($idJadwal)
    {
        $db = db_connect();

        $row = $db->table('jadwal_produksi')->where('id_jadwal', (int)$idJadwal)->get()->getRowArray();
        if (!$row) return redirect()->to(site_url('admin/jadwal'))->with('error', 'Data jadwal tidak ditemukan.');

        // untuk edit: tampilkan pemesanan yg dipakai + eligible lain
        $pemesanan = $this->getEligibleOrdersForCreate($db);
        $current = $db->table('pemesanan')->select('id_pemesanan,kode_pemesanan,tanggal_acara,jam_mulai_acara')
            ->where('id_pemesanan', (int)$row['id_pemesanan'])->get()->getRowArray();

        if ($current) {
            $exists = false;
            foreach ($pemesanan as $pm) {
                if ((int)$pm['id_pemesanan'] === (int)$current['id_pemesanan']) { $exists = true; break; }
            }
            if (!$exists) array_unshift($pemesanan, $current);
        }

        $editors = $db->table('user')
            ->select('id_user,nama_lengkap')
            ->where('role', 'editor')
            ->orderBy('nama_lengkap', 'ASC')
            ->get()->getResultArray();

        return view('admin/jadwal/form', [
            'title' => 'Edit Jadwal',
            'mode' => 'edit',
            'pemesanan' => $pemesanan,
            'editors' => $editors,
            'row' => $row,
            'allowedStatus' => $this->adminAllowedStatus(),
        ]);
    }

    public function update($idJadwal)
    {
        $db = db_connect();

        $old = $db->table('jadwal_produksi')->where('id_jadwal', (int)$idJadwal)->get()->getRowArray();
        if (!$old) return redirect()->to(site_url('admin/jadwal'))->with('error', 'Data jadwal tidak ditemukan.');

        $newStatusRaw = (string)$this->request->getPost('status_produksi');
        $newStatus = $this->norm($newStatusRaw);
        $oldStatus = $this->norm($old['status_produksi'] ?? '');

        // admin hanya boleh ubah sampai cut-to-cut
        if (!in_array($newStatus, $this->adminAllowedStatus(), true)) {
            return redirect()->back()->with('error', 'Status ini harus diupdate oleh editor (finishing/revisi/done/revisi selesai).');
        }

        // aturan: shooting -> cut-to-cut harus sudah ada jam_selesai_shooting
        if ($newStatus === 'cut-to-cut') {
            if ($oldStatus !== 'shooting') {
                return redirect()->back()->with('error', 'Tidak bisa masuk cut-to-cut sebelum status shooting.');
            }
            $jamSelesai = $old['jam_selesai_shooting'] ?? null;
            if (empty($jamSelesai)) {
                return redirect()->back()->with('error', 'Isi jam selesai shooting dulu sebelum mulai editing.');
            }
        }

        $data = [
            'id_pemesanan' => (int)$this->request->getPost('id_pemesanan'),
            'id_editor' => (int)$this->request->getPost('id_editor'),
            'tanggal_shooting' => $this->request->getPost('tanggal_shooting') ?: null,
            'jam_mulai_shooting' => $this->request->getPost('jam_mulai_shooting') ?: null,
            'jam_selesai_shooting' => $this->request->getPost('jam_selesai_shooting') ?: null,
            'tanggal_mulai_editing' => $this->request->getPost('tanggal_mulai_editing') ?: null,
            'tanggal_selesai_editing' => $this->request->getPost('tanggal_selesai_editing') ?: null,
            'status_produksi' => $newStatusRaw,
            'catatan_produksi' => $this->request->getPost('catatan_produksi') ?: $old['catatan_produksi'],
        ];

        $now = date('Y-m-d H:i:s');
        $logLine = "[{$now}] ADMIN: status_produksi -> {$newStatusRaw}";
        $oldCat = (string)($old['catatan_produksi'] ?? '');
        $data['catatan_produksi'] = trim($oldCat . "\n" . $logLine);

        $db->table('jadwal_produksi')->where('id_jadwal', (int)$idJadwal)->update($data);

        return redirect()->to(site_url('admin/jadwal'))->with('success', 'Jadwal berhasil diupdate.');
    }
}
