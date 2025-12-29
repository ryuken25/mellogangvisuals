<?php

namespace App\Models;

use CodeIgniter\Model;

class PaketModel extends Model
{
    protected $table      = 'paket';
    protected $primaryKey = 'id_paket';

    protected $allowedFields = [
        'nama_paket',
        'kategori',
        'deskripsi',
        'durasi_jam',
        'harga',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $returnType = 'array';
}
