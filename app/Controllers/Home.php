<?php

namespace App\Controllers;

class Home extends BaseController
{
        public function index()
        {
            $paketModel = new \App\Models\PaketModel();
            return $this->response->setJSON($paketModel->findAll())->setHeader('Content-Type','application/json');

        }

}
