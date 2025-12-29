<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJadwalProduksiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_jadwal' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_pemesanan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_editor' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tanggal_shooting' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'jam_mulai_shooting' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'jam_selesai_shooting' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'tanggal_mulai_editing' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'tanggal_selesai_editing' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status_produksi' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'Pra produksi',
            ],
            'catatan_produksi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_jadwal', true);
        $this->forge->addKey('id_pemesanan');
        $this->forge->addKey('id_editor');

        $this->forge->createTable('jadwal_produksi', true, [
            'ENGINE'  => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('jadwal_produksi', true);
    }
}
