<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\PasswordResetTokenModel;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;

class PasswordController extends BaseController
{
    public function showForgotPassword()
    {
        return $this->render('auth/forgot-password', [
            'pageTitle' => 'Forgot password',
        ], 'guest');
    }

    public function sendResetLink()
    {
        $rules = ['email' => 'required|valid_email'];
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email     = strtolower(trim((string) $this->request->getPost('email')));
        $user      = model(UserModel::class)->findByEmail($email);
        $resetLink = null;

        if ($user) {
            $plainToken = bin2hex(random_bytes(32));
            $tokenModel = model(PasswordResetTokenModel::class);
            $tokenModel->where('email', $email)->delete();
            $tokenModel->insert([
                'email'      => $email,
                'token'      => hash('sha256', $plainToken),
                'created_at' => Time::now()->toDateTimeString(),
            ]);

            $resetLink = site_url('reset-password/' . $plainToken . '?email=' . rawurlencode($email));
        }

        return redirect()->back()
            ->with('status', 'If an account exists for that email, a password reset link is ready.')
            ->with('resetLink', $resetLink);
    }

    public function showResetPassword(string $token)
    {
        return $this->render('auth/reset-password', [
            'token'     => $token,
            'email'     => (string) $this->request->getGet('email'),
            'pageTitle' => 'Reset password',
        ], 'guest');
    }

    public function resetPassword()
    {
        $rules = [
            'email'                 => 'required|valid_email',
            'token'                 => 'required|string',
            'password'              => 'required|min_length[8]',
            'password_confirmation' => 'required|matches[password]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email      = strtolower(trim((string) $this->request->getPost('email')));
        $plainToken = (string) $this->request->getPost('token');
        $row        = model(PasswordResetTokenModel::class)->find($email);

        if (! $row || ! hash_equals((string) $row['token'], hash('sha256', $plainToken))) {
            return redirect()->back()->withInput()->with('error', 'This password reset token is invalid.');
        }

        $createdAt = strtotime((string) ($row['created_at'] ?? '')) ?: 0;
        if ($createdAt === 0 || (time() - $createdAt) > 3600) {
            model(PasswordResetTokenModel::class)->delete($email);

            return redirect()->to(site_url('forgot-password'))->with('error', 'This password reset link has expired.');
        }

        $user = model(UserModel::class)->findByEmail($email);
        if (! $user) {
            return redirect()->to(site_url('forgot-password'))->with('error', 'We could not find a matching account.');
        }

        model(UserModel::class)->update((int) $user['id'], [
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);
        model(PasswordResetTokenModel::class)->delete($email);
        auth_service()->clearPendingTwoFactor();

        return redirect()->to(site_url('login'))->with('success', 'Your password has been reset. You can now sign in.');
    }
}
