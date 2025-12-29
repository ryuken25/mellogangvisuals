<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PaketModel;

class PaketController extends BaseController
{
    public function index()
    {
        $model = new PaketModel();
        return view('admin/paket/index', [
            'title' => 'Admin - Paket',
            'paket' => $model->orderBy('id_paket','DESC')->findAll(),
        ]);
    }

    public function create()
    {
        helper(['form']);
        return view('admin/paket/form', [
            'title' => 'Tambah Paket',
            'mode'  => 'create',
            'data'  => [],
            'validation' => service('validation'),
        ]);
    }

    public function store()
    {
        helper(['form']);
        $rules = [
            'nama_paket' => 'required|max_length[100]',
            'kategori'   => 'required|max_length[50]',
            'durasi_jam' => 'required|integer',
            'harga'      => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return view('admin/paket/form', [
                'title' => 'Tambah Paket',
                'mode'  => 'create',
                'data'  => $this->request->getPost(),
                'validation' => $this->validator,
            ]);
        }

        (new PaketModel())->insert([
            'nama_paket' => $this->request->getPost('nama_paket'),
            'kategori'   => $this->request->getPost('kategori'),
            'deskripsi'  => $this->request->getPost('deskripsi'),
            'durasi_jam' => (int)$this->request->getPost('durasi_jam'),
            'harga'      => (int)$this->request->getPost('harga'),
            'is_active'  => (int)($this->request->getPost('is_active') ?? 1),
        ]);

        return redirect()->to('/admin/paket')->with('success', 'Paket berhasil ditambah.');
    }

    public function edit($id)
    {
        helper(['form']);
        $model = new PaketModel();
        $row = $model->find($id);

        if (! $row) return redirect()->to('/admin/paket');

        return view('admin/paket/form', [
            'title' => 'Edit Paket',
            'mode'  => 'edit',
            'data'  => $row,
            'validation' => service('validation'),
        ]);
    }

    public function update($id)
    {
        helper(['form']);
        $rules = [
            'nama_paket' => 'required|max_length[100]',
            'kategori'   => 'required|max_length[50]',
            'durasi_jam' => 'required|integer',
            'harga'      => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return view('admin/paket/form', [
                'title' => 'Edit Paket',
                'mode'  => 'edit',
                'data'  => array_merge(['id_paket'=>$id], $this->request->getPost()),
                'validation' => $this->validator,
            ]);
        }

        (new PaketModel())->update($id, [
            'nama_paket' => $this->request->getPost('nama_paket'),
            'kategori'   => $this->request->getPost('kategori'),
            'deskripsi'  => $this->request->getPost('deskripsi'),
            'durasi_jam' => (int)$this->request->getPost('durasi_jam'),
            'harga'      => (int)$this->request->getPost('harga'),
            'is_active'  => (int)($this->request->getPost('is_active') ?? 1),
        ]);

        return redirect()->to('/admin/paket')->with('success', 'Paket berhasil diupdate.');
    }

    public function delete($id)
    {
        (new PaketModel())->delete($id);
        return redirect()->to('/admin/paket')->with('success', 'Paket berhasil dihapus.');
    }
}
