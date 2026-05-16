<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JadwalProduksiSeeder extends Seeder
{
    public function run()
    {
        $table = 'jadwal_produksi';

        if (! $this->db->tableExists($table)) {
            return;
        }

        $fields = $this->db->getFieldNames($table);

        $editors = $this->db->table('user')
            ->select('id_user')
            ->where('role', 'editor')
            ->orderBy('id_user', 'ASC')
            ->get()->getResultArray();

        $orders = $this->db->table('pemesanan')
            ->select('id_pemesanan, tanggal_acara')
            ->orderBy('id_pemesanan', 'ASC')
            ->limit(6)
            ->get()->getResultArray();

        if ($orders === [] || $editors === []) {
            return;
        }

        $statusProduksi = ['cut-to-cut', 'cut-to-cut', 'finishing', 'finishing', 'done', 'done'];
        $now            = date('Y-m-d H:i:s');

        foreach ($orders as $idx => $order) {
            $editor   = $editors[$idx % count($editors)];
            $shooting = $order['tanggal_acara'] ?? date('Y-m-d', strtotime('+' . ($idx + 1) . ' days'));
            $startEd  = date('Y-m-d', strtotime($shooting . ' +1 day'));
            $endEd    = date('Y-m-d', strtotime($shooting . ' +' . (3 + ($idx % 3)) . ' days'));
            $status   = $statusProduksi[$idx] ?? 'cut-to-cut';

            $row = [
                'id_pemesanan'            => (int) $order['id_pemesanan'],
                'id_editor'               => (int) $editor['id_user'],
                'tanggal_shooting'        => $shooting,
                'jam_mulai_shooting'      => '09:00:00',
                'jam_selesai_shooting'    => '15:00:00',
                'tanggal_mulai_editing'   => $startEd,
                'tanggal_selesai_editing' => $endEd,
                'status_produksi'         => $status,
                'catatan_produksi'        => 'Progress flow: cut-to-cut -> finishing -> done.',
            ];

            if (in_array('created_at', $fields, true)) {
                $row['created_at'] = $now;
            }
            if (in_array('updated_at', $fields, true)) {
                $row['updated_at'] = $now;
            }

            $this->db->table($table)->insert(array_intersect_key($row, array_flip($fields)));
        }
    }
}
