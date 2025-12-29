<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    private function redirectByRole(string $role): string
    {
        return match ($role) {
            'admin'    => '/admin',
            'editor'   => '/editor',
            default    => '/pelanggan',
        };
    }

    public function loginForm()
    {
        helper(['form', 'url']);
        return view('auth/login', [
            'title'      => 'Login',
            'validation' => service('validation'),
        ]);
    }

    public function login()
    {
        helper(['form', 'url']);

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return view('auth/login', [
                'title'      => 'Login',
                'validation' => $this->validator,
            ]);
        }

        $email = $this->request->getPost('email');
        $pass  = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (! $user || ! password_verify($pass, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Email atau kata sandi salah.');
        }

        session()->set([
            'logged_in'    => true,
            'id_user'      => $user['id_user'],
            'nama_lengkap' => $user['nama_lengkap'],
            'email'        => $user['email'],
            'role'         => $user['role'],
        ]);

        return redirect()->to($this->redirectByRole($user['role']));
    }

    public function registerForm()
    {
        helper(['form', 'url']);
        return view('auth/register', [
            'title'      => 'Daftar Akun',
            'validation' => service('validation'),
        ]);
    }

    public function register()
    {
        helper(['form', 'url']);

        $rules = [
            'nama_lengkap'       => 'required|min_length[3]|max_length[100]',
            'email'              => 'required|valid_email|max_length[100]|is_unique[user.email]',
            'no_telepon'         => 'required|min_length[8]|max_length[20]',
            'password'           => 'required|min_length[6]|max_length[255]',
            'password_confirm'   => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return view('auth/register', [
                'title'      => 'Daftar Akun',
                'validation' => $this->validator,
            ]);
        }

        $userModel = new UserModel();
        $userModel->insert([
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'email'        => $this->request->getPost('email'),
            'no_telepon'   => $this->request->getPost('no_telepon'),
            'password'     => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'         => 'pelanggan',
        ]);

        return redirect()->to('/login')->with('success', 'Registrasi berhasil. Silakan login.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
