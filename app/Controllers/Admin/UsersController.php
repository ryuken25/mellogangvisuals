<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class UsersController extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $q    = trim((string) $this->request->getGet('q'));
        $role = trim((string) $this->request->getGet('role'));

        $builder = $db->table('user')
            ->select('id_user, nama_lengkap, email, no_telepon, role')
            ->orderBy('id_user', 'DESC');

        if ($q !== '') {
            $builder->groupStart()
                ->like('nama_lengkap', $q)
                ->orLike('email', $q)
                ->orLike('no_telepon', $q)
            ->groupEnd();
        }

        if ($role !== '') {
            $builder->where('role', $role);
        }

        $rows = $builder->get()->getResultArray();

        return view('admin/users/index', [
            'title' => 'Admin - Users',
            'rows'  => $rows,
            'q'     => $q,
            'role'  => $role,
        ]);
    }

    public function edit($id)
    {
        $db = db_connect();

        $row = $db->table('user')
            ->where('id_user', (int) $id)
            ->get()->getRowArray();

        if (! $row) {
            return redirect()->to(site_url('admin/users'))->with('error', 'User tidak ditemukan.');
        }

        return view('admin/users/form', [
            'title' => 'Admin - Edit User',
            'row'   => $row,
        ]);
    }

    public function update($id)
    {
        $db = db_connect();

        $row = $db->table('user')->where('id_user', (int) $id)->get()->getRowArray();
        if (! $row) return redirect()->to(site_url('admin/users'))->with('error', 'User tidak ditemukan.');

        // proteksi: jangan ubah role admin (biar aman)
        if (($row['role'] ?? '') === 'admin') {
            return redirect()->to(site_url('admin/users'))->with('error', 'User admin tidak boleh diubah dari sini.');
        }

        $nama = trim((string) $this->request->getPost('nama_lengkap'));
        $telp = trim((string) $this->request->getPost('no_telepon'));
        $role = trim((string) $this->request->getPost('role'));

        if ($nama === '') {
            return redirect()->back()->withInput()->with('error', 'Nama wajib diisi.');
        }

        // hanya boleh pelanggan/editor dari halaman ini
        if (! in_array($role, ['pelanggan', 'editor'], true)) {
            return redirect()->back()->withInput()->with('error', 'Role tidak valid.');
        }

        $updateData = [
            'nama_lengkap' => $nama,
            'no_telepon'   => $telp,
            'role'         => $role,
        ];

        // ✅ reset password opsional
        $newPass = (string) $this->request->getPost('new_password');
        $newPass2 = (string) $this->request->getPost('new_password_confirm');

        if ($newPass !== '' || $newPass2 !== '') {
            if (strlen($newPass) < 6) {
                return redirect()->back()->withInput()->with('error', 'Password baru minimal 6 karakter.');
            }
            if ($newPass !== $newPass2) {
                return redirect()->back()->withInput()->with('error', 'Konfirmasi password tidak sama.');
            }

            // asumsi kolom password namanya "password"
            $updateData['password'] = password_hash($newPass, PASSWORD_DEFAULT);
        }

        $db->table('user')
            ->where('id_user', (int) $id)
            ->update($updateData);

        return redirect()->to(site_url('admin/users/edit/'.$id))->with('success', 'User berhasil diupdate.');
    }

    public function delete($id)
    {
        $db = db_connect();

        $row = $db->table('user')->where('id_user', (int) $id)->get()->getRowArray();
        if (! $row) return redirect()->to(site_url('admin/users'))->with('error', 'User tidak ditemukan.');

        // proteksi: jangan hapus admin
        if (($row['role'] ?? '') === 'admin') {
            return redirect()->to(site_url('admin/users'))->with('error', 'User admin tidak boleh dihapus.');
        }

        // proteksi: jangan hapus diri sendiri
        $myId = (int) session()->get('id_user');
        if ($myId === (int) $id) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $db->table('user')->where('id_user', (int) $id)->delete();

        return redirect()->to(site_url('admin/users'))->with('success', 'User berhasil dihapus.');
    }
}
