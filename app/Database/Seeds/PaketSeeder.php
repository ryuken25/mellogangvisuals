<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaketSeeder extends Seeder
{
    public function run()
    {
        $table = 'paket';
        $now = date('Y-m-d H:i:s');

        // Harga: kelipatan 250k, min 1jt max 4jt
        $rows = [
            [
                'nama_paket' => 'Basic Reels',
                'kategori'   => 'event',
                'deskripsi'  => '1 video reels cinematic (30-60 detik) untuk acara/event. Include color grading.',
                'durasi_jam' => 3,
                'harga'      => 1000000,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_paket' => 'Event Highlight',
                'kategori'   => 'event',
                'deskripsi'  => 'Highlight video 1-2 menit. Cocok untuk ulang tahun, gathering, dan event kecil.',
                'durasi_jam' => 5,
                'harga'      => 1250000,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_paket' => 'Product Cinematic',
                'kategori'   => 'product',
                'deskripsi'  => 'Video produk cinematic + cut dinamis. Cocok untuk UMKM / brand promo.',
                'durasi_jam' => 5,
                'harga'      => 1500000,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_paket' => 'Corporate Profile Mini',
                'kategori'   => 'corporate',
                'deskripsi'  => 'Video company profile singkat (1-3 menit). Include interview sederhana.',
                'durasi_jam' => 6,
                'harga'      => 2000000,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_paket' => 'Wedding Highlight',
                'kategori'   => 'wedding',
                'deskripsi'  => 'Wedding highlight cinematic (3-5 menit). Include teaser reels.',
                'durasi_jam' => 8,
                'harga'      => 3000000,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_paket' => 'Premium Wedding',
                'kategori'   => 'wedding',
                'deskripsi'  => 'Full day coverage + highlight cinematic + teaser. Best value untuk wedding day.',
                'durasi_jam' => 10,
                'harga'      => 4000000,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $this->db->table($table)->insertBatch($rows);
    }
}
