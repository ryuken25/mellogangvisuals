<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

class StatusController extends BaseController
{
    private function norm($s): string
    {
        return strtolower(trim((string)$s));
    }

    private function extractUrls(string $text): array
    {
        if ($text === '') return [];
        preg_match_all('~https?://[^\s<>"\']+~i', $text, $m);
        $urls = $m[0] ?? [];
        $u = [];
        foreach ($urls as $x) $u[$x] = true;
        return array_keys($u);
    }

    private function stripUrls(string $text): string
    {
        if ($text === '') return '';
        return (string)preg_replace('~https?://[^\s<>"\']+~i', '[link disembunyikan]', $text);
    }

    // ✅ FIX: regex revisi (sebelumnya \bREVISI:\b sering gagal match)
    private function countRevisi(string $catatanPelanggan): int
    {
        if (trim($catatanPelanggan) === '') return 0;
        preg_match_all('~\bREVISI\s*:\s*~i', $catatanPelanggan, $m);
        return (int)count($m[0] ?? []);
    }

    private function prodRank(?string $statusProd, bool $hasJadwal): int
    {
        if (!$hasJadwal) return 0;

        $sp = $this->norm($statusProd ?? '');
        $rank = 4;

        if ($sp === '') return $rank;
        if (str_contains($sp, 'shoot')) return 5;
        if (str_contains($sp, 'cut')) return 6;
        if (str_contains($sp, 'finish')) return 7;

        if ($sp === 'done' || $sp === 'revisi selesai' || str_contains($sp, 'revisi selesai')) return 8;
        if ($sp === 'revisi' || str_contains($sp, 'revisi')) return 9;

        return $rank;
    }

    public function index()
    {
        $db = db_connect();

        $loggedIn = (bool) session()->get('logged_in');
        $role     = (string) session()->get('role');
        $idUser   = (int) (session()->get('id_user') ?? 0);

        $kode = trim((string) $this->request->getGet('kode'));

        // list kiri (punya pelanggan kalau login)
        $myOrders = [];
        if ($loggedIn && $role === 'pelanggan') {
            $myOrders = $db->table('pemesanan p')
                ->select('p.id_pemesanan, p.kode_pemesanan, p.tanggal_acara, p.status_pemesanan, p.total_biaya, pk.nama_paket')
                ->join('paket pk', 'pk.id_paket = p.id_paket', 'left')
                ->where('p.id_user', $idUser)
                ->orderBy('p.id_pemesanan', 'DESC')
                ->get()->getResultArray();
        }

        $order = null;
        if ($kode !== '') {
            $order = $db->table('pemesanan p')
                ->select('p.*, u.nama_lengkap, pk.nama_paket')
                ->join('user u', 'u.id_user = p.id_user', 'left')
                ->join('paket pk', 'pk.id_paket = p.id_paket', 'left')
                ->where('p.kode_pemesanan', $kode)
                ->get()->getRowArray();
        }

        // jadwal terakhir
        $jadwal = null;
        if ($order) {
            $jadwal = $db->table('jadwal_produksi')
                ->where('id_pemesanan', (int)$order['id_pemesanan'])
                ->orderBy('id_jadwal', 'DESC')
                ->get()->getRowArray();
        }

        // pembayaran summary
        $totalValid = 0;
        $dpValid = 0;
        $hasPayment = false;

        if ($order) {
            $payRows = $db->table('pembayaran')
                ->select('jenis_pembayaran, jumlah_bayar, status_verifikasi')
                ->where('id_pemesanan', (int)$order['id_pemesanan'])
                ->get()->getResultArray();

            foreach ($payRows as $p) {
                $hasPayment = true;
                $st = $this->norm($p['status_verifikasi'] ?? '');
                if ($st !== 'valid') continue;

                $totalValid += (int)($p['jumlah_bayar'] ?? 0);
                if ($this->norm($p['jenis_pembayaran'] ?? '') === 'dp') {
                    $dpValid += (int)($p['jumlah_bayar'] ?? 0);
                }
            }
        }

        $totalOrder = (int)($order['total_biaya'] ?? 0);
        $sisa = $order ? max($totalOrder - $totalValid, 0) : 0;

        $isLunas = ($order && $totalOrder > 0 && $totalValid >= $totalOrder);

        $ownerId = (int)($order['id_user'] ?? 0);
        $isOwner = ($loggedIn && $role === 'pelanggan' && $ownerId === $idUser);

        $canSeeMoney = ($loggedIn && ($role === 'admin' || $isOwner));
        $canSeeLinks = ($isLunas && $canSeeMoney);

        $statusPesanan = $this->norm($order['status_pemesanan'] ?? '');
        $delivered = (str_contains($statusPesanan, 'serah terima') || $statusPesanan === 'selesai');

        $sp = $this->norm($jadwal['status_produksi'] ?? '');
        $isFinalEditing = in_array($sp, ['done', 'revisi selesai'], true);

        $revCount = $order ? $this->countRevisi((string)($order['catatan_pelanggan'] ?? '')) : 0;

        $revPending = ($statusPesanan === 'revisi pelanggan');
        $revProcess = ($statusPesanan === 'revisi diproses');

        $canRevisi = false;
        if ($order && $loggedIn && $role === 'pelanggan' && $isOwner) {
            if (!$delivered && $revCount < 2 && $isFinalEditing && !$revPending && !$revProcess) {
                $canRevisi = true;
            }
        }

        $canSesuai = false;
        if ($order && $loggedIn && $role === 'pelanggan' && $isOwner) {
            if (!$delivered && $isFinalEditing) $canSesuai = true;
        }

        // invoice eligibility
        $canInvoice = false;
        $invoiceUrl = null;
        if ($order && $loggedIn) {
            if ($canSeeMoney && $totalValid > 0) {
                $canInvoice = true;
                $invoiceUrl = site_url('invoice/' . urlencode($order['kode_pemesanan']));
            }
        }

        // catatan admin + link filter
        $adminNoteAll = $order ? trim((string)($order['catatan_admin'] ?? '')) : '';
        $adminUrlsAll = $this->extractUrls($adminNoteAll);

        $publicLog = $jadwal ? $this->stripUrls((string)($jadwal['catatan_produksi'] ?? '')) : '';

        $adminNote = $canSeeLinks ? $adminNoteAll : $this->stripUrls($adminNoteAll);
        $adminUrls = $canSeeLinks ? $adminUrlsAll : [];

        // steps
        $steps = [];
        if ($order) {
            $hasJadwal = (bool)$jadwal;
            $rank = $this->prodRank($jadwal['status_produksi'] ?? '', $hasJadwal);

            $payLabel = 'Pembayaran (DP)';
            if ($isLunas) $payLabel = 'Lunas';
            elseif ($dpValid > 0) $payLabel = 'DP diterima';

            $payState = 'pending';
            if ($isLunas || $dpValid > 0) $payState = 'done';
            elseif ($hasPayment) $payState = 'process';

            $confirmState = 'process';
            if ($hasPayment || $payState === 'done' || $statusPesanan !== 'menunggu pembayaran') {
                $confirmState = 'done';
            }

            $s4 = 'pending';
            if ($hasJadwal) $s4 = ($rank >= 5) ? 'done' : 'process';
            else $s4 = ($payState === 'done') ? 'process' : 'pending';

            $stepByRank = function(int $need, int $rank): string {
                if ($rank <= 0) return 'pending';
                if ($rank > $need) return 'done';
                if ($rank === $need) return 'process';
                return 'pending';
            };

            $s5 = $stepByRank(5, $rank);
            $s6 = $stepByRank(6, $rank);
            $s7 = $stepByRank(7, $rank);
            $s8 = ($rank >= 8) ? 'done' : 'pending';

            // ✅ FIX: revisi step
            // - kalau 0 revisi dan belum delivered => pending (bukan done)
            $s9 = 'pending';
            if ($delivered) $s9 = 'done';
            elseif ($revPending || $revProcess || $sp === 'revisi') $s9 = 'process';
            elseif ($revCount > 0 && $rank >= 8) $s9 = 'done';
            else $s9 = 'pending';

            $s10 = 'pending';
            if ($delivered) $s10 = 'done';
            elseif ($rank >= 8) $s10 = 'process';

            $steps = [
                ['label' => 'Permintaan diterima', 'state' => 'done'],
                ['label' => 'Menunggu konfirmasi admin', 'state' => $confirmState],
                ['label' => $payLabel, 'state' => $payState],
                ['label' => 'Penjadwalan', 'state' => $s4],
                ['label' => 'Proses shooting', 'state' => $s5],
                ['label' => 'Proses editing (cut-to-cut)', 'state' => $s6],
                ['label' => 'Proses editing (finishing)', 'state' => $s7],
                ['label' => 'Proses editing (done)', 'state' => $s8],
                ['label' => 'Revisi pelanggan (' . $revCount . '/2)', 'state' => $s9],
                ['label' => 'Serah terima hasil', 'state' => $s10],
            ];
        }

        return view('public/status/index', [
            'title'       => 'Status Pesanan',
            'loggedIn'    => $loggedIn,
            'role'        => $role,
            'kode'        => $kode,
            'myOrders'    => $myOrders,
            'order'       => $order,
            'jadwal'      => $jadwal,
            'steps'       => $steps,

            'canSeeMoney' => $canSeeMoney,
            'canInvoice'  => $canInvoice,
            'invoiceUrl'  => $invoiceUrl,
            'totalValid'  => $totalValid,
            'sisa'        => $sisa,
            'isLunas'     => $isLunas,

            'revCount'    => $revCount,
            'revPending'  => $revPending,
            'revProcess'  => $revProcess,
            'canRevisi'   => $canRevisi,
            'canSesuai'   => $canSesuai,
            'delivered'   => $delivered,

            'canSeeLinks' => $canSeeLinks,
            'adminNote'   => $adminNote,
            'adminUrls'   => $adminUrls,
            'publicLog'   => $publicLog,
        ]);
    }

    public function revisi($idPemesanan)
    {
        $db = db_connect();

        if (!session()->get('logged_in')) {
            return redirect()->to(site_url('login'))->with('error', 'Login dulu ya untuk mengajukan revisi.');
        }
        if ((string)session()->get('role') !== 'pelanggan') {
            return redirect()->to(site_url('/'))->with('error', 'Akses ditolak.');
        }

        $idUser = (int)session()->get('id_user');
        $idPemesanan = (int)$idPemesanan;

        $order = $db->table('pemesanan')->where('id_pemesanan', $idPemesanan)->get()->getRowArray();
        if (!$order || (int)$order['id_user'] !== $idUser) {
            return redirect()->to(site_url('status-pesanan'))->with('error', 'Pesanan tidak ditemukan / bukan milik kamu.');
        }

        $statusPesanan = $this->norm($order['status_pemesanan'] ?? '');
        if (str_contains($statusPesanan, 'serah terima') || $statusPesanan === 'selesai') {
            return redirect()->to(site_url('status-pesanan?kode=' . urlencode($order['kode_pemesanan'])))
                ->with('error', 'Pesanan sudah masuk serah terima hasil.');
        }

        $revCount = $this->countRevisi((string)($order['catatan_pelanggan'] ?? ''));
        if ($revCount >= 2) {
            return redirect()->to(site_url('status-pesanan?kode=' . urlencode($order['kode_pemesanan'])))
                ->with('error', 'Batas revisi sudah 2x. Pesanan masuk serah terima hasil.');
        }

        $jadwal = $db->table('jadwal_produksi')
            ->where('id_pemesanan', $idPemesanan)
            ->orderBy('id_jadwal', 'DESC')
            ->get()->getRowArray();

        $sp = $this->norm($jadwal['status_produksi'] ?? '');
        if (!in_array($sp, ['done', 'revisi selesai'], true)) {
            return redirect()->to(site_url('status-pesanan?kode=' . urlencode($order['kode_pemesanan'])))
                ->with('error', 'Revisi hanya bisa diajukan setelah status produksi DONE / REVISI SELESAI.');
        }

        if ($statusPesanan === 'revisi pelanggan' || $statusPesanan === 'revisi diproses') {
            return redirect()->to(site_url('status-pesanan?kode=' . urlencode($order['kode_pemesanan'])))
                ->with('error', 'Revisi kamu sudah diajukan dan sedang diproses.');
        }

        $catatan = trim((string)$this->request->getPost('catatan_revisi'));
        if ($catatan === '') {
            return redirect()->back()->with('error', 'Catatan revisi wajib diisi.');
        }

        $now = date('Y-m-d H:i:s');

        $oldCp = trim((string)($order['catatan_pelanggan'] ?? ''));
        $lineCp = "[{$now}] REVISI: {$catatan}";
        $newCp = trim($oldCp . "\n" . $lineCp);

        $db->table('pemesanan')->where('id_pemesanan', $idPemesanan)->update([
            'status_pemesanan'  => 'revisi pelanggan',
            'catatan_pelanggan' => $newCp,
        ]);

        return redirect()
            ->to(site_url('status-pesanan?kode=' . urlencode($order['kode_pemesanan'])))
            ->with('success', 'Revisi berhasil dikirim. Menunggu editor menerima revisi.');
    }

    public function selesai($idPemesanan)
    {
        $db = db_connect();

        if (!session()->get('logged_in')) {
            return redirect()->to(site_url('login'))->with('error', 'Login dulu ya.');
        }
        if ((string)session()->get('role') !== 'pelanggan') {
            return redirect()->to(site_url('/'))->with('error', 'Akses ditolak.');
        }

        $idUser = (int)session()->get('id_user');
        $idPemesanan = (int)$idPemesanan;

        $order = $db->table('pemesanan')->where('id_pemesanan', $idPemesanan)->get()->getRowArray();
        if (!$order || (int)$order['id_user'] !== $idUser) {
            return redirect()->to(site_url('status-pesanan'))->with('error', 'Pesanan tidak ditemukan / bukan milik kamu.');
        }

        $statusPesanan = $this->norm($order['status_pemesanan'] ?? '');
        if (str_contains($statusPesanan, 'serah terima') || $statusPesanan === 'selesai') {
            return redirect()->to(site_url('status-pesanan?kode=' . urlencode($order['kode_pemesanan'])))
                ->with('success', 'Pesanan sudah masuk serah terima hasil.');
        }

        $jadwal = $db->table('jadwal_produksi')
            ->where('id_pemesanan', $idPemesanan)
            ->orderBy('id_jadwal', 'DESC')
            ->get()->getRowArray();

        $sp = $this->norm($jadwal['status_produksi'] ?? '');
        if (!in_array($sp, ['done', 'revisi selesai'], true)) {
            return redirect()->to(site_url('status-pesanan?kode=' . urlencode($order['kode_pemesanan'])))
                ->with('error', 'Konfirmasi hanya bisa setelah status produksi DONE / REVISI SELESAI.');
        }

        $now = date('Y-m-d H:i:s');
        $oldCp = trim((string)($order['catatan_pelanggan'] ?? ''));
        $lineCp = "[{$now}] PELANGGAN: pesanan sudah sesuai";
        $newCp = trim($oldCp . "\n" . $lineCp);

        $db->table('pemesanan')->where('id_pemesanan', $idPemesanan)->update([
            'status_pemesanan'  => 'serah terima hasil',
            'catatan_pelanggan' => $newCp,
        ]);

        return redirect()
            ->to(site_url('status-pesanan?kode=' . urlencode($order['kode_pemesanan'])))
            ->with('success', 'Terima kasih! Pesanan masuk tahap serah terima hasil.');
    }
}
