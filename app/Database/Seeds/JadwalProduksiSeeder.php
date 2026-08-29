<?php

namespace App\Database\Seeds;

use App\Support\Status;
use CodeIgniter\Database\Seeder;

class JadwalProduksiSeeder extends Seeder
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
        $table = 'jadwal_produksi';

        // Ambil editor IDs
        $editors = $this->db->table('user')
            ->select('id_user, nama_lengkap')
            ->where('role', 'editor')
            ->orderBy('id_user', 'ASC')
            ->get()->getResultArray();

        // Ambil 6 pemesanan
        $orders = $this->db->table('pemesanan')
            ->orderBy($this->db->getFieldNames('pemesanan')[0] ?? 'id_pemesanan', 'ASC')
            ->limit(6)
            ->get()->getResultArray();

        $statusProduksi = [
            Status::PROD_CUT_TO_CUT, Status::PROD_CUT_TO_CUT,
            Status::PROD_FINISHING, Status::PROD_FINISHING,
            Status::PROD_DONE, Status::PROD_DONE,
        ];

        foreach ($orders as $idx => $o) {
            // cari PK pemesanan
            $orderPk = null;
            foreach (['id_pemesanan', 'id', 'idOrder', 'id_pesan'] as $cand) {
                if (array_key_exists($cand, $o)) { $orderPk = $cand; break; }
            }
            if ($orderPk === null) $orderPk = array_key_first($o);

            $editor = $editors[$idx % max(1, count($editors))] ?? null;
            $idEditor = $editor ? (int) $editor['id_user'] : 1;

            $mulai = date('Y-m-d', strtotime("-" . (7 - $idx) . " days"));
            $selesai = date('Y-m-d', strtotime($mulai . " + " . (3 + ($idx % 3)) . " days"));

            $r = [
                'id_pemesanan'    => (int) $o[$orderPk],
                'id_editor'       => $idEditor,
                'tanggal_mulai'   => $mulai,
                'tanggal_selesai' => $selesai,
                'status_produksi' => $statusProduksi[$idx] ?? Status::PROD_CUT_TO_CUT,
                'catatan'         => 'Progress sesuai alur: cut-to-cut → finishing → done.',
                'progress'        => in_array($statusProduksi[$idx] ?? '', ['done'], true) ? 100 : (in_array($statusProduksi[$idx] ?? '', ['finishing'], true) ? 75 : 40),
            ];

            $fields = $this->db->getFieldNames($table);

            // id editor alternatif
            if (!in_array('id_editor', $fields, true)) {
                if (in_array('editor_id', $fields, true)) {
                    $r['editor_id'] = $r['id_editor'];
                    unset($r['id_editor']);
                } elseif (in_array('id_user_editor', $fields, true)) {
                    $r['id_user_editor'] = $r['id_editor'];
                    unset($r['id_editor']);
                }
            }

            // status alternatif
            if (!in_array('status_produksi', $fields, true) && in_array('status', $fields, true)) {
                $r['status'] = $r['status_produksi'];
                unset($r['status_produksi']);
            }

            $r = $this->filterToFields($table, $this->withTimestamps($table, $r));
            $this->db->table($table)->insert($r);
        }
    }
}
