<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('logged_in')) {
            return redirect()->to(site_url('login'))->with('error', 'Silakan login dulu.');
        }

        if (! empty($arguments) && isset($arguments[0])) {
            $need = $arguments[0];
            if ($session->get('role') !== $need) {
                return redirect()->to(site_url('/'))->with('error', 'Akses ditolak.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
