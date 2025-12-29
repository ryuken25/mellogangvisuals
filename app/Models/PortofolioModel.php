<?php

namespace App\Models;

use CodeIgniter\Model;

class PortofolioModel extends Model
{
    protected $table      = 'portofolio';
    protected $primaryKey = 'id_portfolio';

    protected $allowedFields = [
        'id_paket',
        'judul',
        'deskripsi',
        'kategori',
        'url_media',
        'tanggal_publikasi',
        'created_at',
        'updated_at',
        'thumbnail',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $returnType = 'array';
}
