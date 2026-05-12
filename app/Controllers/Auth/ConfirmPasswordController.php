<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;

class ConfirmPasswordController extends BaseController
{
    public function show()
    {
        return $this->render('auth/confirm-password', [
            'redirectTo' => (string) ($this->request->getGet('redirect') ?: site_url('dashboard')),
            'pageTitle'  => 'Confirm password',
        ], 'guest');
    }

    public function store()
    {
        $rules = ['password' => 'required|string'];
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (! auth_service()->confirmPassword((string) $this->request->getPost('password'))) {
            return redirect()->back()->withInput()->with('error', 'The password you entered was incorrect.');
        }

        return redirect()->to((string) ($this->request->getPost('redirect') ?: site_url('dashboard')))
            ->with('success', 'Password confirmed.');
    }
}
