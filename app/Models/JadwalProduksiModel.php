<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalProduksiModel extends Model
{
    protected $table      = 'jadwal_produksi';
    protected $primaryKey = 'id_jadwal';

    protected $allowedFields = [
        'id_pemesanan',
        'id_editor',
        'tanggal_shooting',
        'jam_mulai_shooting',
        'jam_selesai_shooting',
        'tanggal_mulai_editing',
        'tanggal_selesai_editing',
        'status_produksi',
        'catatan_produksi',
    ];

    protected $useTimestamps = false;

    protected $returnType = 'array';
}
