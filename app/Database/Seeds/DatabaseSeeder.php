<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Tables in reverse dependency order — child tables first so the
        // truncate works whether or not foreign-key checks are honoured.
        $tables = [
            'pengeluaran_operasional',
            'jadwal_produksi',
            'pembayaran',
            'detail_pemesanan',
            'pemesanan',
            'portofolio',
            'paket',
            'user',
        ];

        $isMysql = $this->isMysqlDriver();

        if ($isMysql) {
            $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        }

        try {
            foreach ($tables as $t) {
                if ($this->db->tableExists($t)) {
                    $this->db->table($t)->truncate();
                }
            }
        } finally {
            if ($isMysql) {
                $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            }
        }

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

    private function isMysqlDriver(): bool
    {
        $driver = strtolower((string) ($this->db->DBDriver ?? ''));

        return in_array($driver, ['mysqli', 'mysql', 'mariadb', 'pdo_mysql'], true);
    }
}
