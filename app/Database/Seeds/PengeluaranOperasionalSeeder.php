<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PengeluaranOperasionalSeeder extends Seeder
{
    public function run()
    {
        $table = 'pengeluaran_operasional';

        if (! $this->db->tableExists($table)) {
            return;
        }

        $now    = date('Y-m-d H:i:s');
        $fields = $this->db->getFieldNames($table);

        $items = [
            ['name' => 'Bensin & parkir liputan',      'amount' => 250000, 'daysAgo' => 10],
            ['name' => 'Konsumsi tim produksi',        'amount' => 150000, 'daysAgo' => 9],
            ['name' => 'Sewa lighting & audio',        'amount' => 500000, 'daysAgo' => 8],
            ['name' => 'Lisensi musik highlight',      'amount' => 300000, 'daysAgo' => 7],
            ['name' => 'Cleaning kit & maintenance',   'amount' => 200000, 'daysAgo' => 6],
            ['name' => 'Internet & cloud storage',     'amount' => 250000, 'daysAgo' => 5],
        ];

        $rows = [];
        foreach ($items as $item) {
            $row = [
                'nama_pengeluaran'    => $item['name'],
                'nominal'             => $item['amount'],
                'tanggal_pengeluaran' => date('Y-m-d', strtotime('-' . $item['daysAgo'] . ' days')),
            ];

            if (in_array('created_at', $fields, true)) {
                $row['created_at'] = $now;
            }
            if (in_array('updated_at', $fields, true)) {
                $row['updated_at'] = $now;
            }

            $rows[] = array_intersect_key($row, array_flip($fields));
        }

        $this->db->table($table)->insertBatch($rows);
    }
}
