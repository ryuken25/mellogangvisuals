<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PengeluaranOperasionalModel;

class PengeluaranController extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $start = $this->request->getGet('start') ?: date('Y-m-01');
        $end   = $this->request->getGet('end') ?: date('Y-m-d');

        $rows = $db->table('pengeluaran_operasional po')
            ->select('po.*, p.kode_pemesanan')
            ->join('pemesanan p', 'p.id_pemesanan = po.id_pemesanan', 'left')
            ->where('po.tanggal_pengeluaran >=', $start)
            ->where('po.tanggal_pengeluaran <=', $end)
            ->orderBy('po.id_pengeluaran', 'DESC')
            ->get()->getResultArray();

        $total = array_sum(array_map(fn($r) => (int)$r['nominal'], $rows));

        return view('admin/pengeluaran/index', [
            'title' => 'Pengeluaran Operasional',
            'rows'  => $rows,
            'start' => $start,
            'end'   => $end,
            'total' => $total,
        ]);
    }

    public function create()
    {
        $orders = db_connect()->table('pemesanan')
            ->select('id_pemesanan, kode_pemesanan')
            ->orderBy('id_pemesanan', 'DESC')
            ->get()->getResultArray();

        return view('admin/pengeluaran/form', [
            'title' => 'Tambah Pengeluaran',
            'mode' => 'create',
            'orders' => $orders,
            'data' => [
                'tanggal_pengeluaran' => date('Y-m-d')
            ],
        ]);
    }

    public function store()
    {
        $rules = [
            'nama_pengeluaran' => 'required|max_length[100]',
            'nominal' => 'required|integer',
            'tanggal_pengeluaran' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal.');
        }

        $model = new PengeluaranOperasionalModel();
        $idPemesanan = $this->request->getPost('id_pemesanan');

        $model->insert([
            'id_pemesanan' => $idPemesanan !== '' ? (int)$idPemesanan : null,
            'nama_pengeluaran' => $this->request->getPost('nama_pengeluaran'),
            'nominal' => (int)$this->request->getPost('nominal'),
            'tanggal_pengeluaran' => $this->request->getPost('tanggal_pengeluaran'),
        ]);

        return redirect()->to(site_url('admin/pengeluaran'))->with('success', 'Pengeluaran ditambahkan.');
    }

    public function edit($id)
    {
        $model = new PengeluaranOperasionalModel();
        $row = $model->find((int)$id);
        if (! $row) return redirect()->to(site_url('admin/pengeluaran'));

        $orders = db_connect()->table('pemesanan')
            ->select('id_pemesanan, kode_pemesanan')
            ->orderBy('id_pemesanan', 'DESC')
            ->get()->getResultArray();

        return view('admin/pengeluaran/form', [
            'title' => 'Edit Pengeluaran',
            'mode' => 'edit',
            'orders' => $orders,
            'data' => $row,
        ]);
    }

    public function update($id)
    {
        $model = new PengeluaranOperasionalModel();

        $rules = [
            'nama_pengeluaran' => 'required|max_length[100]',
            'nominal' => 'required|integer',
            'tanggal_pengeluaran' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal.');
        }

        $idPemesanan = $this->request->getPost('id_pemesanan');

        $model->update((int)$id, [
            'id_pemesanan' => $idPemesanan !== '' ? (int)$idPemesanan : null,
            'nama_pengeluaran' => $this->request->getPost('nama_pengeluaran'),
            'nominal' => (int)$this->request->getPost('nominal'),
            'tanggal_pengeluaran' => $this->request->getPost('tanggal_pengeluaran'),
        ]);

        return redirect()->to(site_url('admin/pengeluaran'))->with('success', 'Pengeluaran diupdate.');
    }

    public function delete($id)
    {
        $model = new PengeluaranOperasionalModel();
        $model->delete((int)$id);

        return redirect()->to(site_url('admin/pengeluaran'))->with('success', 'Pengeluaran dihapus.');
    }
}
