<?php

namespace App\Models;

use CodeIgniter\Model;

class PengeluaranOperasionalModel extends Model
{
    protected $table      = 'pengeluaran_operasional';
    protected $primaryKey = 'id_pengeluaran';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_pemesanan',
        'nama_pengeluaran',
        'nominal',
        'tanggal_pengeluaran',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
