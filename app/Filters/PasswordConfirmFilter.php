<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PasswordConfirmFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! auth_check()) {
            return redirect()->to(site_url('login'));
        }

        if (! auth_service()->passwordConfirmedRecently()) {
            $target = current_url(true)->__toString();
            return redirect()->to(site_url('user/confirm-password?redirect=' . rawurlencode($target)));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
