<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PortofolioModel;
use App\Models\PaketModel;

class PortofolioController extends BaseController
{
    private function uploadDir(): string
    {
        // public/uploads/portofolio
        return rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'portofolio';
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
    }

    private function saveThumbnail(?\CodeIgniter\HTTP\Files\UploadedFile $file): ?string
    {
        if (!$file || !$file->isValid() || $file->hasMoved()) return null;

        $ext = strtolower((string)$file->getClientExtension());
        if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) return null;

        $dir = $this->uploadDir();
        $this->ensureDir($dir);

        $name = 'porto_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $file->move($dir, $name);

        return $name;
    }

    public function index()
    {
        $model = new PortofolioModel();
        $items = $model->orderBy('id_portfolio', 'DESC')->findAll();

        return view('admin/portofolio/index', [
            'title' => 'Admin - Portofolio',
            'items' => $items,
        ]);
    }

    public function create()
    {
        helper(['form']);
        $paket = (new PaketModel())->orderBy('id_paket','DESC')->findAll();

        return view('admin/portofolio/form', [
            'title' => 'Tambah Portofolio',
            'mode'  => 'create',
            'data'  => [],
            'paket' => $paket,
            'validation' => service('validation'),
        ]);
    }

    public function store()
    {
        helper(['form']);

        $rules = [
            'id_paket'          => 'required|integer',
            'judul'             => 'required|max_length[100]',
            'kategori'          => 'required|max_length[50]',
            'url_media'         => 'required|max_length[255]',
            'tanggal_publikasi' => 'required',
            'thumbnail'         => 'uploaded[thumbnail]|max_size[thumbnail,3072]|ext_in[thumbnail,jpg,jpeg,png,webp]',
        ];

        if (! $this->validate($rules)) {
            $paket = (new PaketModel())->orderBy('id_paket','DESC')->findAll();
            return view('admin/portofolio/form', [
                'title' => 'Tambah Portofolio',
                'mode'  => 'create',
                'data'  => $this->request->getPost(),
                'paket' => $paket,
                'validation' => $this->validator,
            ]);
        }

        $thumbName = $this->saveThumbnail($this->request->getFile('thumbnail'));
        if (!$thumbName) {
            return redirect()->back()->withInput()->with('error', 'Thumbnail gagal diupload. Pastikan JPG/PNG/WebP dan max 3MB.');
        }

        (new PortofolioModel())->insert([
            'id_paket'          => (int)$this->request->getPost('id_paket'),
            'judul'             => (string)$this->request->getPost('judul'),
            'deskripsi'         => (string)$this->request->getPost('deskripsi'),
            'kategori'          => (string)$this->request->getPost('kategori'),
            'url_media'         => (string)$this->request->getPost('url_media'),
            'tanggal_publikasi' => (string)$this->request->getPost('tanggal_publikasi'),
            'thumbnail'         => $thumbName,
        ]);

        return redirect()->to('admin/portofolio')->with('success', 'Portofolio berhasil ditambah.');
    }

    public function edit($id)
    {
        helper(['form']);
        $model = new PortofolioModel();
        $row = $model->find($id);
        if (! $row) return redirect()->to('admin/portofolio');

        $paket = (new PaketModel())->orderBy('id_paket','DESC')->findAll();

        return view('admin/portofolio/form', [
            'title' => 'Edit Portofolio',
            'mode'  => 'edit',
            'data'  => $row,
            'paket' => $paket,
            'validation' => service('validation'),
        ]);
    }

    public function update($id)
    {
        helper(['form']);

        $rules = [
            'id_paket'          => 'required|integer',
            'judul'             => 'required|max_length[100]',
            'kategori'          => 'required|max_length[50]',
            'url_media'         => 'required|max_length[255]',
            'tanggal_publikasi' => 'required',
            'thumbnail'         => 'permit_empty|max_size[thumbnail,3072]|ext_in[thumbnail,jpg,jpeg,png,webp]',
        ];

        $model = new PortofolioModel();
        $oldRow = $model->find($id);
        if (! $oldRow) return redirect()->to('admin/portofolio');

        if (! $this->validate($rules)) {
            $paket = (new PaketModel())->orderBy('id_paket','DESC')->findAll();
            return view('admin/portofolio/form', [
                'title' => 'Edit Portofolio',
                'mode'  => 'edit',
                'data'  => array_merge($oldRow, $this->request->getPost()),
                'paket' => $paket,
                'validation' => $this->validator,
            ]);
        }

        $thumbFile = $this->request->getFile('thumbnail');
        $newThumb = $this->saveThumbnail($thumbFile);

        $update = [
            'id_paket'          => (int)$this->request->getPost('id_paket'),
            'judul'             => (string)$this->request->getPost('judul'),
            'deskripsi'         => (string)$this->request->getPost('deskripsi'),
            'kategori'          => (string)$this->request->getPost('kategori'),
            'url_media'         => (string)$this->request->getPost('url_media'),
            'tanggal_publikasi' => (string)$this->request->getPost('tanggal_publikasi'),
        ];

        if ($newThumb) {
            $update['thumbnail'] = $newThumb;

            // hapus file lama
            $oldThumb = (string)($oldRow['thumbnail'] ?? '');
            if ($oldThumb !== '') {
                $oldPath = $this->uploadDir() . DIRECTORY_SEPARATOR . $oldThumb;
                if (is_file($oldPath)) @unlink($oldPath);
            }
        }

        $model->update($id, $update);

        return redirect()->to('admin/portofolio')->with('success', 'Portofolio berhasil diupdate.');
    }

    public function delete($id)
    {
        $model = new PortofolioModel();
        $row = $model->find($id);
        if ($row) {
            $thumb = (string)($row['thumbnail'] ?? '');
            if ($thumb !== '') {
                $path = $this->uploadDir() . DIRECTORY_SEPARATOR . $thumb;
                if (is_file($path)) @unlink($path);
            }
            $model->delete($id);
        }
        return redirect()->to('admin/portofolio')->with('success', 'Portofolio berhasil dihapus.');
    }
}
