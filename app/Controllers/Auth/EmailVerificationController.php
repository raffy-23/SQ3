<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;

class EmailVerificationController extends BaseController
{
    public function notice()
    {
        if (($this->authUser['email_verified_at'] ?? null) !== null) {
            return redirect()->to(site_url('dashboard'));
        }

        return $this->render('auth/verify-email', [
            'verificationLink' => session()->getFlashdata('verificationLink'),
            'status'           => session()->getFlashdata('status'),
            'pageTitle'        => 'Verify email',
        ], 'guest');
    }

    public function send()
    {
        if (($this->authUser['email_verified_at'] ?? null) !== null) {
            return redirect()->to(site_url('dashboard'));
        }

        $verificationLink = site_url('email/verify/' . $this->authUser['id'] . '/' . sha1((string) $this->authUser['email']));

        return redirect()->to(site_url('email/verify'))
            ->with('status', 'verification-link-sent')
            ->with('verificationLink', $verificationLink);
    }

    public function verify(int $userId, string $hash)
    {
        if (! auth_check() || (int) ($this->authUser['id'] ?? 0) !== $userId) {
            return redirect()->to(site_url('login'));
        }

        if (! hash_equals(sha1((string) ($this->authUser['email'] ?? '')), $hash)) {
            return redirect()->to(site_url('email/verify'))->with('error', 'The verification link is invalid.');
        }

        model(UserModel::class)->update($userId, ['email_verified_at' => date('Y-m-d H:i:s')]);
        auth_service()->loginById($userId);

        return redirect()->to(site_url('dashboard'))->with('success', 'Your email address has been verified.');
    }
}
