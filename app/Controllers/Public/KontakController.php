<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

class KontakController extends BaseController
{
    public function index()
    {
        return view('public/kontak/index', ['title' => 'Kontak']);
    }
}
