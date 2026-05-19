<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends BaseController
{
    public function show()
    {
        if (auth_service()->pendingTwoFactorUserId() === null) {
            return redirect()->to(site_url('login'));
        }

        return $this->render('auth/two-factor-challenge', [
            'pageTitle' => 'Two-factor challenge',
        ], 'guest');
    }

    public function store()
    {
        $pendingUserId = auth_service()->pendingTwoFactorUserId();
        if ($pendingUserId === null) {
            return redirect()->to(site_url('login'));
        }

        $user = model(UserModel::class)->find($pendingUserId);
        if (! $user) {
            auth_service()->clearPendingTwoFactor();

            return redirect()->to(site_url('login'))->with('error', 'The two-factor session is no longer valid.');
        }

        $recoveryCode = trim((string) $this->request->getPost('recovery_code'));
        if ($recoveryCode !== '') {
            $codes = json_decode((string) ($user['two_factor_recovery_codes'] ?? '[]'), true);
            $codes = is_array($codes) ? array_values($codes) : [];

            if (! in_array($recoveryCode, $codes, true)) {
                return redirect()->back()->withInput()->with('error', 'That recovery code is invalid.');
            }

            $remainingCodes = array_values(array_filter($codes, static fn (string $code): bool => $code !== $recoveryCode));
            model(UserModel::class)->update($pendingUserId, [
                'two_factor_recovery_codes' => json_encode($remainingCodes, JSON_UNESCAPED_SLASHES),
            ]);

            auth_service()->loginPendingTwoFactor();
            $user = current_user();

            return redirect()->to(site_url(($user['email_verified_at'] ?? null) ? 'feed' : 'email/verify'));
        }

        $rules = ['code' => 'required|exact_length[6]|numeric'];
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $valid = false;
        try {
            $valid = (new Google2FA())->verifyKey((string) ($user['two_factor_secret'] ?? ''), (string) $this->request->getPost('code'));
        } catch (\Throwable) {
            $valid = false;
        }

        if (! $valid) {
            return redirect()->back()->withInput()->with('error', 'The provided authentication code was invalid.');
        }

        auth_service()->loginPendingTwoFactor();
        $loggedInUser = current_user();

        return redirect()->to(site_url(($loggedInUser['email_verified_at'] ?? null) ? 'dashboard' : 'email/verify'));
    }
}
