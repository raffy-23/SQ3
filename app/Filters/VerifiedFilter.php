<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class VerifiedFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! auth_check()) {
            return redirect()->to(site_url('login'));
        }

        $user = current_user();
        if (($user['email_verified_at'] ?? null) === null) {
            $currentPath = trim($request->getUri()->getPath(), '/');
            if (! in_array($currentPath, ['email/verify', 'email/verification-notification'], true)) {
                return redirect()->to(site_url('email/verify'));
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
