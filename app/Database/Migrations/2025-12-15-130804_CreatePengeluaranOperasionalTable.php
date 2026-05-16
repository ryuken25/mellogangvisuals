<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePengeluaranOperasionalTable extends Migration
{
    public function up()
    {
        // Skip if the table was created out of band (e.g. via raw SQL import).
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
                'null'       => true, // nullable for general (non-order) expenses
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
        $this->forge->addKey('id_pemesanan');
        $this->forge->addKey('tanggal_pengeluaran');

        // Optional FK to pemesanan: ON UPDATE SET NULL keeps the row when the
        // referenced order is renumbered; ON DELETE CASCADE removes the
        // expense when its order is deleted.
        $this->forge->addForeignKey(
            'id_pemesanan',
            'pemesanan',
            'id_pemesanan',
            'SET NULL',
            'CASCADE'
        );

        $this->forge->createTable('pengeluaran_operasional', true);
    }

    public function down()
    {
        if ($this->db->tableExists('pengeluaran_operasional')) {
            $this->forge->dropTable('pengeluaran_operasional', true);
        }
    }
}
