<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $table = 'user';
        $now = date('Y-m-d H:i:s');

        $rows = [
            [
                'nama_lengkap' => 'Darma',
                'email'        => 'admin@mellogang.test',
                'no_telepon'   => '081200000001',
                'password'     => password_hash('123123', PASSWORD_DEFAULT),
                'role'         => 'admin',
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'nama_lengkap' => 'Winayagatar',
                'email'        => 'editor1@mellogang.test',
                'no_telepon'   => '081200000002',
                'password'     => password_hash('123123', PASSWORD_DEFAULT),
                'role'         => 'editor',
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'nama_lengkap' => 'Atar',
                'email'        => 'editor@mellogang.test',
                'no_telepon'   => '081200000003',
                'password'     => password_hash('123123', PASSWORD_DEFAULT),
                'role'         => 'editor',
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'nama_lengkap' => 'Pengguna 1',
                'email'        => 'pengguna1@mellogang.test',
                'no_telepon'   => '081200000004',
                'password'     => password_hash('123123', PASSWORD_DEFAULT),
                'role'         => 'pelanggan',
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'nama_lengkap' => 'Pengguna 2',
                'email'        => 'pengguna2@mellogang.test',
                'no_telepon'   => '081200000005',
                'password'     => password_hash('123123', PASSWORD_DEFAULT),
                'role'         => 'pelanggan',
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'nama_lengkap' => 'Pengguna 3',
                'email'        => 'pengguna3@mellogang.test',
                'no_telepon'   => '081200000006',
                'password'     => password_hash('123123', PASSWORD_DEFAULT),
                'role'         => 'pelanggan',
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ];

        $this->db->table($table)->insertBatch($rows);
    }
}
