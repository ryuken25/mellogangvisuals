<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailPemesananModel extends Model
{
    protected $table      = 'detail_pemesanan';
    protected $primaryKey = 'id_detail';

    protected $allowedFields = [
        'id_pemesanan',
        'nama_item',
        'qty',
        'harga_satuan',
        'subtotal',
    ];

    protected $useTimestamps = false;

    protected $returnType = 'array';
}
