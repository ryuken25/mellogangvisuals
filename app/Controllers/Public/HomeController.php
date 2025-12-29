<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\PaketModel;
use App\Models\PortofolioModel;

class HomeController extends BaseController
{
    public function index()
    {
        $paketModel = new PaketModel();
        $portoModel = new PortofolioModel();

        $paket = $paketModel
            ->where('is_active', 1)
            ->orderBy('id_paket', 'DESC')
            ->findAll(3);

        $portofolio = $portoModel
            ->orderBy('id_portfolio', 'DESC')
            ->findAll(4);

        return view('public/home/index', [
            'title'      => 'Beranda',
            'paket'      => $paket,
            'portofolio' => $portofolio,
        ]);
    }
}
