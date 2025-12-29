<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\PortofolioModel;
use App\Models\PaketModel;

class PortofolioController extends BaseController
{
    public function index()
    {
        $portoModel = new PortofolioModel();
        $paketModel = new PaketModel();

        $items = $portoModel->orderBy('id_portfolio', 'DESC')->findAll();

        // map nama paket
        $paketMap = [];
        foreach ($paketModel->findAll() as $p) {
            $paketMap[$p['id_paket']] = $p['nama_paket'];
        }

        // compute thumb
        foreach ($items as &$po) {
            $thumb = base_url('assets/images/porto_placeholder.png');

            $thumbName = (string)($po['thumbnail'] ?? '');
            if ($thumbName !== '') {
                $thumb = base_url('uploads/portofolio/' . $thumbName);
            } else {
                $url = (string)($po['url_media'] ?? '');
                if (preg_match('~\.(jpg|jpeg|png|webp|gif)(\?.*)?$~i', $url)) $thumb = $url;
                if (preg_match('~youtu\.be/([^/?]+)~', $url, $m)) $thumb = 'https://img.youtube.com/vi/'.$m[1].'/hqdefault.jpg';
                if (preg_match('~v=([^&]+)~', $url, $m)) $thumb = 'https://img.youtube.com/vi/'.$m[1].'/hqdefault.jpg';
            }

            $po['thumb'] = $thumb;
        }
        unset($po);

        return view('public/portofolio/index', [
            'title' => 'Portofolio',
            'items' => $items,
            'paketMap' => $paketMap,
        ]);
    }
}
