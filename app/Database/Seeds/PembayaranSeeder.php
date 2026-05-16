<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PembayaranSeeder extends Seeder
{
    private function round250k(int $value): int
    {
        $step = 250000;
        if ($value <= 0) return $step;
        return (int) (round($value / $step) * $step);
    }

    public function run()
    {
        $table = 'pembayaran';

        $orders = $this->db->table('pemesanan')
            ->select('id_pemesanan,kode_pemesanan,total_biaya,status_pemesanan')
            ->orderBy('id_pemesanan', 'ASC')
            ->limit(6)
            ->get()->getResultArray();

        $metode = ['Transfer', 'BCA', 'MANDIRI', 'E-Wallet', 'Tunai', 'QRIS'];

        $rows = [];
        foreach ($orders as $idx => $o) {
            $total = (int) ($o['total_biaya'] ?? 0);
            if ($total <= 0) {
                // Fall back to the package price when the order has no total.
                $pk = $this->db->table('pemesanan pm')
                    ->select('pk.harga')
                    ->join('paket pk', 'pk.id_paket = pm.id_paket', 'left')
                    ->where('pm.id_pemesanan', (int) $o['id_pemesanan'])
                    ->get()->getRowArray();
                $total = (int) ($pk['harga'] ?? 1000000);
            }

            $jenis  = ($idx < 3) ? 'DP' : 'Pelunasan';
            $jumlah = ($jenis === 'DP') ? $this->round250k((int) round($total * 0.3)) : $total;

            if ($jumlah <= 0) {
                $jumlah = 250000;
            }

            $statusVerif = ['Menunggu', 'valid', 'valid', 'ditolak', 'valid', 'Menunggu'][$idx] ?? 'Menunggu';

            $rows[] = [
                'id_pemesanan'      => (int) $o['id_pemesanan'],
                'jenis_pembayaran'  => $jenis,
                'tanggal_bayar'     => date('Y-m-d H:i:s', strtotime('-' . (7 - $idx) . ' days')),
                'metode_pembayaran' => $metode[$idx % count($metode)],
                'jumlah_bayar'      => $jumlah,
                'bukti_bayar'       => null,
                'status_verifikasi' => $statusVerif,
                'catatan_verifikasi'=> null,
            ];
        }

        $this->db->table($table)->insertBatch($rows);
    }
}
