<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\PaketModel;

class KatalogController extends BaseController
{
    public function index()
    {
        $paket = (new PaketModel())
            ->where('is_active', 1)
            ->orderBy('id_paket', 'DESC')
            ->findAll();

        return view('public/katalog/index', [
            'title' => 'Paket',
            'paket' => $paket,
        ]);
    }
}
