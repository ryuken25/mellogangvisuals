<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePengeluaranOperasionalTable extends Migration
{
    public function up()
    {
        // ✅ FIX: kalau tabel sudah ada (mis. hasil import SQL / dibuat manual), stop di sini
        if ($this->db->tableExists('pengeluaran_operasional')) {
            return;
        }

        $this->forge->addField([
            'id_pengeluaran' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_pemesanan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true, // boleh kosong untuk pengeluaran umum
            ],
            'nama_pengeluaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'nominal' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'tanggal_pengeluaran' => [
                'type' => 'DATE',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_pengeluaran', true);

        // FK optional ke pemesanan
        $this->forge->addForeignKey(
            'id_pemesanan',
            'pemesanan',
            'id_pemesanan',
            'SET NULL',
            'CASCADE'
        );

        $this->forge->createTable('pengeluaran_operasional', true); // true = IF NOT EXISTS (driver support)
    }

    public function down()
    {
        // ✅ aman juga
        if ($this->db->tableExists('pengeluaran_operasional')) {
            $this->forge->dropTable('pengeluaran_operasional', true);
        }
    }
}
