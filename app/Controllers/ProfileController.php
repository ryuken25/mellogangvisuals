<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Services;

class ProfileController extends BaseController
{
    private function guard()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(site_url('login'))->with('error', 'Login dulu ya.');
        }
        return null;
    }

    private function getPasswordField($db): ?string
    {
        // adaptif: kalau tabel kamu pakai "password" atau "password_hash"
        if ($db->fieldExists('password', 'user')) return 'password';
        if ($db->fieldExists('password_hash', 'user')) return 'password_hash';
        return null;
    }

    public function index()
    {
        if ($resp = $this->guard()) return $resp;

        $db = db_connect();
        $id = (int) session()->get('id_user');

        $user = $db->table('user')
            ->select('id_user, nama_lengkap, email, no_telepon')
            ->where('id_user', $id)
            ->get()->getRowArray();

        if (!$user) {
            return redirect()->to(site_url('/'))->with('error', 'User tidak ditemukan.');
        }

        return view('profile/index', [
            'title' => 'Profil Saya',
            'user'  => $user,
        ]);
    }

    public function update()
    {
        if ($resp = $this->guard()) return $resp;

        $db = db_connect();
        $id = (int) session()->get('id_user');

        $nama = trim((string) $this->request->getPost('nama_lengkap'));
        $telp = trim((string) $this->request->getPost('no_telepon'));

        if ($nama === '') {
            return redirect()->back()->with('error', 'Nama wajib diisi.');
        }
        if (mb_strlen($nama) > 100) {
            return redirect()->back()->with('error', 'Nama terlalu panjang.');
        }
        if ($telp !== '' && mb_strlen($telp) > 30) {
            return redirect()->back()->with('error', 'No telepon terlalu panjang.');
        }

        $data = [
            'nama_lengkap' => $nama,
            'no_telepon'   => $telp,
        ];

        $db->table('user')->where('id_user', $id)->update($data);

        return redirect()->to(site_url('profile'))->with('success', 'Profil berhasil diupdate.');
    }

    public function password()
    {
        if ($resp = $this->guard()) return $resp;

        $db = db_connect();
        $id = (int) session()->get('id_user');

        $old = (string) $this->request->getPost('old_password');
        $new = (string) $this->request->getPost('new_password');
        $con = (string) $this->request->getPost('confirm_password');

        if ($old === '' || $new === '' || $con === '') {
            return redirect()->back()->with('error', 'Semua field password wajib diisi.');
        }
        if ($new !== $con) {
            return redirect()->back()->with('error', 'Konfirmasi password baru tidak sama.');
        }
        if (mb_strlen($new) < 6) {
            return redirect()->back()->with('error', 'Password baru minimal 6 karakter.');
        }

        $pwdField = $this->getPasswordField($db);
        if (!$pwdField) {
            return redirect()->back()->with('error', 'Kolom password di tabel user tidak ditemukan (password / password_hash).');
        }

        $row = $db->table('user')
            ->select("id_user, {$pwdField}")
            ->where('id_user', $id)
            ->get()->getRowArray();

        if (!$row) return redirect()->back()->with('error', 'User tidak ditemukan.');

        $hash = (string)($row[$pwdField] ?? '');
        if ($hash === '' || !password_verify($old, $hash)) {
            return redirect()->back()->with('error', 'Password lama salah.');
        }

        $newHash = password_hash($new, PASSWORD_DEFAULT);

        $db->table('user')->where('id_user', $id)->update([
            $pwdField => $newHash,
        ]);

        return redirect()->to(site_url('profile'))->with('success', 'Password berhasil diganti.');
    }

    public function photo()
    {
        if ($resp = $this->guard()) return $resp;

        $rules = [
            'photo' => 'uploaded[photo]|max_size[photo,20480]|is_image[photo]|mime_in[photo,image/jpg,image/jpeg,image/png,image/webp]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', 'Upload foto gagal. Pastikan file gambar (JPG/PNG/WEBP) dan max 20MB.');
        }

        $id = (int) session()->get('id_user');

        $file = $this->request->getFile('photo');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'File tidak valid.');
        }

        // simpan ke writable/uploads/avatars/{id}.jpg (auto crop 1:1 + kompres)
        $dir = WRITEPATH . 'uploads/avatars';
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $dest = $dir . '/' . $id . '.jpg';

        try {
            $img = Services::image()
                ->withFile($file->getTempName())
                ->fit(512, 512, 'center'); // crop tengah jadi 1:1

            // save sebagai jpg quality 80 (kompres)
            $img->save($dest, 80);

            // cache-bust avatar di browser
            session()->set('avatar_v', time());

            return redirect()->to(site_url('profile'))->with('success', 'Foto profil berhasil diupdate.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal memproses foto: ' . $e->getMessage());
        }
    }

    public function avatar()
    {
        // avatar endpoint aman dipanggil topbar
        $id = (int) (session()->get('id_user') ?? 0);

        $userAvatar = WRITEPATH . 'uploads/avatars/' . $id . '.jpg';
        $defaultAvatar = FCPATH . 'assets/images/default_profile.png';

        $path = is_file($userAvatar) ? $userAvatar : $defaultAvatar;
        if (!is_file($path)) {
            // fallback super terakhir (kalau default file belum ada)
            return $this->response->setStatusCode(404);
        }

        $mime = @mime_content_type($path) ?: 'image/png';

        return $this->response
            ->setHeader('Cache-Control', 'no-store, max-age=0')
            ->setContentType($mime)
            ->setBody(file_get_contents($path));
    }
}
