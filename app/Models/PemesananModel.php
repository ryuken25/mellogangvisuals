<?php

namespace App\Models;

use CodeIgniter\Model;

class PemesananModel extends Model
{
    protected $table      = 'pemesanan';
    protected $primaryKey = 'id_pemesanan';

    protected $allowedFields = [
        'kode_pemesanan',
        'id_user',
        'id_paket',
        'tanggal_pemesanan',
        'tanggal_acara',
        'lokasi_acara',
        'status_pemesanan',
        'total_biaya',
        'catatan_pelanggan',
        'catatan_admin',
    ];

    // tabel pemesanan di rancangan kamu tidak punya created_at/updated_at
    protected $useTimestamps = false;

    protected $returnType = 'array';
}
