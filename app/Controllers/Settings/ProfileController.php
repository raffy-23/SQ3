<?php

namespace App\Controllers\Settings;

use App\Controllers\BaseController;
use App\Models\UserModel;

class ProfileController extends BaseController
{
    public function index()
    {
        return redirect()->to(site_url('settings/profile'));
    }

    public function edit()
    {
        return $this->render('settings/layout', [
            'settingsView'    => 'settings/profile',
            'mustVerifyEmail' => true,
            'status'          => session()->getFlashdata('status'),
            'pageTitle'       => 'Settings',
            'topbarLabel'     => 'Manage your profile and account settings',
        ]);
    }

    public function update()
    {
        $rules = [
            'first_name'    => 'required|string|max_length[100]',
            'last_name'     => 'required|string|max_length[100]',
            'email'         => 'required|valid_email|max_length[255]',
            'date_of_birth' => 'required|valid_date[Y-m-d]',
            'sex'           => 'required|in_list[male,female,other]',
            'bio'           => 'permit_empty|string|max_length[500]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId    = (int) $this->authUser['id'];
        $userModel = model(UserModel::class);
        $email     = strtolower(trim((string) $this->request->getPost('email')));

        $existing = $userModel->where('email', $email)->where('id !=', $userId)->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('errors', ['email' => 'That email address is already in use.']);
        }

        $emailChanged = strtolower((string) ($this->authUser['email'] ?? '')) !== $email;
        $userModel->update($userId, [
            'first_name'        => trim((string) $this->request->getPost('first_name')),
            'last_name'         => trim((string) $this->request->getPost('last_name')),
            'email'             => $email,
            'date_of_birth'     => (string) $this->request->getPost('date_of_birth'),
            'sex'               => (string) $this->request->getPost('sex'),
            'bio'               => trim((string) $this->request->getPost('bio')) ?: null,
            'email_verified_at' => $emailChanged ? null : ($this->authUser['email_verified_at'] ?? null),
        ]);

        auth_service()->loginById($userId);

        $redirect = redirect()->back()->with('success', 'Profile updated successfully.');
        if ($emailChanged) {
            $redirect = $redirect->with('status', 'Your email changed, so please verify it again.');
        }

        return $redirect;
    }

    public function destroy()
    {
        $password = (string) $this->request->getPost('password');
        if ($password === '' || ! password_verify($password, (string) ($this->authUser['password'] ?? ''))) {
            return redirect()->back()->with('error', 'Please provide your current password to delete the account.');
        }

        model(UserModel::class)->delete((int) $this->authUser['id']);
        auth_service()->logout();

        return redirect()->to(site_url('/'))->with('success', 'Your account has been deleted.');
    }
}
