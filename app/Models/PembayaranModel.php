<?php

namespace App\Models;

use CodeIgniter\Model;

class PembayaranModel extends Model
{
    protected $table      = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';

    protected $allowedFields = [
        'id_pemesanan',
        'jenis_pembayaran',
        'tanggal_bayar',
        'metode_pembayaran',
        'jumlah_bayar',
        'bukti_bayar',
        'status_verifikasi',
        'catatan_verifikasi',
    ];

    protected $useTimestamps = false;

    protected $returnType = 'array';
}
