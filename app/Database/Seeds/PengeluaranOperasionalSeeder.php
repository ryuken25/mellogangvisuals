<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PengeluaranOperasionalSeeder extends Seeder
{
    private function withTimestamps(string $table, array $row): array
    {
        $fields = $this->db->getFieldNames($table);
        $now = date('Y-m-d H:i:s');

        if (in_array('created_at', $fields, true) && !isset($row['created_at'])) {
            $row['created_at'] = $now;
        }
        if (in_array('updated_at', $fields, true) && !isset($row['updated_at'])) {
            $row['updated_at'] = $now;
        }
        return $row;
    }

    private function filterToFields(string $table, array $row): array
    {
        $fields = array_flip($this->db->getFieldNames($table));
        return array_intersect_key($row, $fields);
    }

    public function run()
    {
        $table = 'pengeluaran_operasional';

        $rows = [
            [
                'tanggal'   => date('Y-m-d', strtotime('-10 days')),
                'kategori'  => 'Transport',
                'deskripsi' => 'Bensin & parkir untuk liputan.',
                'jumlah'    => 250000,
            ],
            [
                'tanggal'   => date('Y-m-d', strtotime('-9 days')),
                'kategori'  => 'Konsumsi',
                'deskripsi' => 'Snack tim produksi (shooting).',
                'jumlah'    => 150000,
            ],
            [
                'tanggal'   => date('Y-m-d', strtotime('-8 days')),
                'kategori'  => 'Sewa Alat',
                'deskripsi' => 'Sewa lighting / audio kecil.',
                'jumlah'    => 500000,
            ],
            [
                'tanggal'   => date('Y-m-d', strtotime('-7 days')),
                'kategori'  => 'Lisensi Musik',
                'deskripsi' => 'Lisensi musik untuk video highlight.',
                'jumlah'    => 300000,
            ],
            [
                'tanggal'   => date('Y-m-d', strtotime('-6 days')),
                'kategori'  => 'Perawatan Gear',
                'deskripsi' => 'Cleaning kit & maintenance ringan.',
                'jumlah'    => 200000,
            ],
            [
                'tanggal'   => date('Y-m-d', strtotime('-5 days')),
                'kategori'  => 'Internet & Cloud',
                'deskripsi' => 'Kuota / upload footage & storage.',
                'jumlah'    => 250000,
            ],
        ];

        foreach ($rows as $r) {
            $fields = $this->db->getFieldNames($table);

            // support variasi nama kolom
            if (!in_array('jumlah', $fields, true) && in_array('nominal', $fields, true)) {
                $r['nominal'] = $r['jumlah'];
                unset($r['jumlah']);
            }
            if (!in_array('tanggal', $fields, true) && in_array('tgl', $fields, true)) {
                $r['tgl'] = $r['tanggal'];
                unset($r['tanggal']);
            }

            $r = $this->filterToFields($table, $this->withTimestamps($table, $r));
            $this->db->table($table)->insert($r);
        }
    }
}
