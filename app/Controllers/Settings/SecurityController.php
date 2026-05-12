<?php

namespace App\Controllers\Settings;

use App\Controllers\BaseController;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use PragmaRX\Google2FA\Google2FA;

class SecurityController extends BaseController
{
    public function edit()
    {
        $setupSecret        = session()->get('two_factor_setup_secret');
        $setupRecoveryCodes = session()->get('two_factor_setup_recovery_codes');

        return $this->render('settings/layout', [
            'settingsView'           => 'settings/security',
            'twoFactorEnabled'       => ! empty($this->authUser['two_factor_enabled']),
            'twoFactorRecoveryCodes' => $this->decodeCodes((string) ($this->authUser['two_factor_recovery_codes'] ?? '')),
            'pendingTwoFactorSetup'  => $setupSecret ? [
                'secret'         => (string) $setupSecret,
                'qrSvg'          => $this->qrSvg((string) $setupSecret),
                'recovery_codes' => is_array($setupRecoveryCodes) ? $setupRecoveryCodes : [],
            ] : null,
            'pageTitle'              => 'Settings',
            'topbarLabel'            => 'Manage your profile and account settings',
        ]);
    }

    public function update()
    {
        $rules = [
            'current_password'      => 'required|string',
            'password'              => 'required|min_length[8]',
            'password_confirmation' => 'required|matches[password]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (! password_verify((string) $this->request->getPost('current_password'), (string) ($this->authUser['password'] ?? ''))) {
            return redirect()->back()->with('error', 'Your current password was incorrect.');
        }

        model(\App\Models\UserModel::class)->update((int) $this->authUser['id'], [
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);
        auth_service()->loginById((int) $this->authUser['id']);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    private function decodeCodes(string $value): array
    {
        $codes = json_decode($value, true);

        return is_array($codes) ? array_values(array_filter($codes, 'is_string')) : [];
    }

    private function qrSvg(string $secret): string
    {
        $google2fa = new Google2FA();
        $otpauth   = $google2fa->getQRCodeUrl('SideQuest', (string) ($this->authUser['email'] ?? 'user@example.com'), $secret);

        return (new SvgWriter())->write(new QrCode(data: $otpauth))->getString();
    }
}
