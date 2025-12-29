<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PortofolioSeeder extends Seeder
{
    public function run()
    {
        $table = 'portofolio';
        $now = date('Y-m-d H:i:s');

        // 6 url sesuai request
        $urls = [
            'https://www.youtube.com/watch?v=FzovkTS7ZgE',
            'https://www.youtube.com/watch?v=vaOXHP0zlVU',
            'https://www.youtube.com/watch?v=4WgJEnnQvHM',
            'https://www.youtube.com/watch?v=h6Q0_5upkk4',
            'https://www.youtube.com/watch?v=7RwTWRgLmHY',
            'https://www.youtube.com/watch?v=8kSnL2fBCTU',
        ];

        $rows = [
            [
                'id_paket'           => 1,
                'judul'             => 'Event Reels - Highlight 01',
                'deskripsi'         => 'Reels cinematic untuk event, cut dinamis + grading.',
                'kategori'          => 'event',
                'url_media'         => $urls[0],
                'tanggal_publikasi' => date('Y-m-d', strtotime('-120 days')),
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id_paket'           => 2,
                'judul'             => 'Event Highlight - Highlight 02',
                'deskripsi'         => 'Video highlight 1-2 menit untuk momen spesial.',
                'kategori'          => 'event',
                'url_media'         => $urls[1],
                'tanggal_publikasi' => date('Y-m-d', strtotime('-110 days')),
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id_paket'           => 3,
                'judul'             => 'Product Cinematic - Showcase',
                'deskripsi'         => 'Video produk cinematic untuk promosi brand.',
                'kategori'          => 'product',
                'url_media'         => $urls[2],
                'tanggal_publikasi' => date('Y-m-d', strtotime('-95 days')),
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id_paket'           => 4,
                'judul'             => 'Corporate Mini Profile',
                'deskripsi'         => 'Company profile singkat dengan storytelling rapi.',
                'kategori'          => 'corporate',
                'url_media'         => $urls[3],
                'tanggal_publikasi' => date('Y-m-d', strtotime('-80 days')),
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id_paket'           => 5,
                'judul'             => 'Wedding Highlight - Teaser',
                'deskripsi'         => 'Wedding highlight cinematic + teaser reels.',
                'kategori'          => 'wedding',
                'url_media'         => $urls[4],
                'tanggal_publikasi' => date('Y-m-d', strtotime('-60 days')),
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id_paket'           => 6,
                'judul'             => 'Premium Wedding - Full Day',
                'deskripsi'         => 'Full coverage wedding day dengan editing premium.',
                'kategori'          => 'wedding',
                'url_media'         => $urls[5],
                'tanggal_publikasi' => date('Y-m-d', strtotime('-45 days')),
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
        ];

        $this->db->table($table)->insertBatch($rows);
    }
}
