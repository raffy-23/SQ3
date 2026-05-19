<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function showLogin()
    {
        return $this->render('auth/login', [
            'canRegister'      => true,
            'canResetPassword' => true,
            'pageTitle'        => 'Log in',
        ], 'guest');
    }

    public function login()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|string',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $result = auth_service()->attempt(
            (string) $this->request->getPost('email'),
            (string) $this->request->getPost('password'),
            $this->request->getPost('remember') !== null,
        );

        if (! $result['success']) {
            return redirect()->back()->withInput()->with('error', $result['error'] ?? 'Unable to sign in.');
        }

        if ($result['two_factor']) {
            return redirect()->to(site_url('two-factor-challenge'));
        }

        $user = current_user();

        return redirect()->to(site_url(($user['email_verified_at'] ?? null) ? 'feed' : 'email/verify'));
    }

    public function showRegister()
    {
        return $this->render('auth/register', [
            'pageTitle' => 'Register',
        ], 'guest');
    }

    public function register()
    {
        $rules = [
            'first_name'            => 'required|string|max_length[100]',
            'last_name'             => 'required|string|max_length[100]',
            'username'              => 'required|min_length[3]|max_length[30]|regex_match[/^[A-Za-z0-9_]+$/]|is_unique[users.username]',
            'email'                 => 'required|valid_email|max_length[255]|is_unique[users.email]',
            'date_of_birth'         => 'required|valid_date[Y-m-d]',
            'sex'                   => 'required|in_list[male,female,other]',
            'password'              => 'required|min_length[8]',
            'password_confirmation' => 'required|matches[password]',
        ];

        $messages = [
            'first_name'  => ['required' => 'First name is required.'],
            'last_name'   => ['required' => 'Last name is required.'],
            'username'    => [
                'required'      => 'Username is required.',
                'min_length'    => 'Username must be at least 3 characters.',
                'regex_match'   => 'Username may only contain letters, numbers, and underscores.',
                'is_unique'     => 'That username is already taken. Please choose another.',
            ],
            'email' => [
                'required'    => 'Email address is required.',
                'valid_email' => 'Please enter a valid email address.',
                'is_unique'   => 'That email address is already registered. Try logging in instead.',
            ],
            'date_of_birth'  => ['required' => 'Date of birth is required.', 'valid_date' => 'Please enter a valid date of birth.'],
            'sex'            => ['required' => 'Please select your sex.'],
            'password'       => [
                'required'   => 'Password is required.',
                'min_length' => 'Password must be at least 8 characters.',
            ],
            'password_confirmation' => [
                'required' => 'Please confirm your password.',
                'matches'  => 'Passwords do not match.',
            ],
        ];

        if (! $this->validateData($this->request->getPost(), $rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = model(UserModel::class);
        $userId    = $userModel->insert([
            'first_name'    => trim((string) $this->request->getPost('first_name')),
            'last_name'     => trim((string) $this->request->getPost('last_name')),
            'username'      => trim((string) $this->request->getPost('username')),
            'email'         => strtolower(trim((string) $this->request->getPost('email'))),
            'date_of_birth' => (string) $this->request->getPost('date_of_birth'),
            'sex'           => (string) $this->request->getPost('sex'),
            'password'      => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
        ], true);

        auth_service()->loginById((int) $userId);

        return redirect()->to(site_url('email/verify'))
            ->with('success', 'Account created successfully.')
            ->with('status', 'Please verify your email address to continue.');
    }

    public function logout()
    {
        auth_service()->logout();

        return redirect()->to(site_url('/'))->with('status', 'You have been logged out.');
    }
}
