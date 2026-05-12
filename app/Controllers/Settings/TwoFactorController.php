<?php

namespace App\Controllers\Settings;

use App\Controllers\BaseController;
use App\Models\UserModel;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends BaseController
{
    public function enable()
    {
        if (! empty($this->authUser['two_factor_enabled'])) {
            return redirect()->back()->with('status', 'Two-factor authentication is already enabled.');
        }

        $google2fa = new Google2FA();
        $secret    = $google2fa->generateSecretKey();
        $codes     = $this->generateRecoveryCodes();

        session()->set('two_factor_setup_secret', $secret);
        session()->set('two_factor_setup_recovery_codes', $codes);

        return redirect()->to(site_url('settings/security'))->with('success', 'Scan the QR code below and confirm a generated code to finish setup.');
    }

    public function confirm()
    {
        $secret = (string) session()->get('two_factor_setup_secret');
        $codes  = session()->get('two_factor_setup_recovery_codes');
        $codes  = is_array($codes) ? $codes : [];
        $code   = trim((string) $this->request->getPost('code'));

        if ($secret === '') {
            return redirect()->back()->with('error', 'There is no pending two-factor setup to confirm.');
        }

        if (! preg_match('/^\d{6}$/', $code)) {
            return redirect()->back()->with('error', 'Enter a valid 6-digit authentication code.');
        }

        $valid = false;
        try {
            $valid = (new Google2FA())->verifyKey($secret, $code);
        } catch (\Throwable) {
            $valid = false;
        }

        if (! $valid) {
            return redirect()->back()->with('error', 'The authentication code was invalid.');
        }

        model(UserModel::class)->update((int) $this->authUser['id'], [
            'two_factor_secret'         => $secret,
            'two_factor_recovery_codes' => json_encode($codes, JSON_UNESCAPED_SLASHES),
            'two_factor_confirmed_at'   => date('Y-m-d H:i:s'),
        ]);

        session()->remove(['two_factor_setup_secret', 'two_factor_setup_recovery_codes']);
        auth_service()->loginById((int) $this->authUser['id']);

        return redirect()->to(site_url('settings/security'))->with('success', 'Two-factor authentication is now enabled.');
    }

    public function cancel()
    {
        session()->remove(['two_factor_setup_secret', 'two_factor_setup_recovery_codes']);

        return redirect()->to(site_url('settings/security'))->with('status', 'Two-factor setup was cancelled.');
    }

    public function disable()
    {
        model(UserModel::class)->update((int) $this->authUser['id'], [
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ]);
        session()->remove(['two_factor_setup_secret', 'two_factor_setup_recovery_codes']);
        auth_service()->loginById((int) $this->authUser['id']);

        return redirect()->to(site_url('settings/security'))->with('success', 'Two-factor authentication has been disabled.');
    }

    private function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($index = 0; $index < 8; $index++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        }

        return $codes;
    }
}
