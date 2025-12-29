<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePembayaranTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_pembayaran' => [
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
            'jenis_pembayaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'tanggal_bayar' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'metode_pembayaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'jumlah_bayar' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'bukti_bayar' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'status_verifikasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'Menunggu',
            ],
            'catatan_verifikasi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_pembayaran', true);
        $this->forge->addKey('id_pemesanan');

        $this->forge->createTable('pembayaran', true, [
            'ENGINE'  => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('pembayaran', true);
    }
}
