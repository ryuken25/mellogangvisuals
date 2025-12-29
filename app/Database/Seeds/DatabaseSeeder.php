<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Matikan FK checks biar truncate aman
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');

        foreach ([
            'pengeluaran_operasional',
            'jadwal_produksi',
            'pembayaran',
            'detail_pemesanan',
            'pemesanan',
            'portofolio',
            'paket',
            'user',
        ] as $t) {
            if ($this->db->tableExists($t)) {
                $this->db->table($t)->truncate();
            }
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS=1');

        $this->call('UserSeeder');
        $this->call('PaketSeeder');
        $this->call('PortofolioSeeder');
        $this->call('PemesananSeeder');
        $this->call('PembayaranSeeder');
        $this->call('JadwalProduksiSeeder');

        if ($this->db->tableExists('pengeluaran_operasional')) {
            $this->call('PengeluaranOperasionalSeeder');
        }
    }
}
