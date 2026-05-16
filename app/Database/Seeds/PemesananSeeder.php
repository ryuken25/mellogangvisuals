<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PemesananSeeder extends Seeder
{
    private function addIfExists(array &$row, array $fields, string $key, $value): void
    {
        if (in_array($key, $fields, true)) {
            $row[$key] = $value;
        }
    }

    public function run()
    {
        $table = 'pemesanan';
        $fields = $this->db->getFieldNames($table);

        $pelanggan = $this->db->table('user')
            ->select('id_user')
            ->where('role', 'pelanggan')
            ->orderBy('id_user', 'ASC')
            ->get()->getResultArray();

        $pakets = $this->db->table('paket')
            ->select('id_paket,harga,nama_paket')
            ->orderBy('id_paket', 'ASC')
            ->get()->getResultArray();

        $pelangganIds = array_column($pelanggan, 'id_user');

        $statusList = [
            'menunggu pembayaran',
            'menunggu verifikasi',
            'menunggu pelunasan',
            'diproses',
            'serah terima hasil',
            'selesai',
        ];

        for ($i = 1; $i <= 6; $i++) {
            $pk = $pakets[$i - 1] ?? $pakets[0] ?? ['id_paket' => 1, 'harga' => 1000000, 'nama_paket' => 'Paket'];
            $idPaket = (int) $pk['id_paket'];
            $harga   = (int) ($pk['harga'] ?? 1000000);

            $idUser = (int) ($pelangganIds ? $pelangganIds[($i - 1) % count($pelangganIds)] : 1);

            $kode = 'MLG' . date('ymd') . '-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT);

            $row = [
                'kode_pemesanan'    => $kode,
                'id_user'           => $idUser,
                'id_paket'          => $idPaket,
                'tanggal_pemesanan' => date('Y-m-d H:i:s', strtotime('-' . (10 - $i) . ' days')),
                'tanggal_acara'     => date('Y-m-d', strtotime('+' . (5 + $i) . ' days')),
                'lokasi_acara'      => 'Br. Batusesa, Desa Candikuning, Kec. Baturiti',
                'status_pemesanan'  => $statusList[$i - 1],
                'total_biaya'       => $harga,
                'catatan_pelanggan' => 'Request: cinematic & clean tone.',
                'catatan_admin'     => null,
            ];

            // Legacy schemas may carry a `jam_mulai_acara` column; populate
            // it only when present so the seeder works against both shapes.
            $this->addIfExists($row, $fields, 'jam_mulai_acara', date('H:i:s', strtotime('09:00:00 +' . $i . ' hours')));

            $this->db->table($table)->insert($row);
        }
    }
}
