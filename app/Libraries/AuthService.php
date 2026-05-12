<?php

namespace App\Libraries;

use App\Models\UserModel;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Session\Session;

class AuthService
{
    private ?array $user = null;

    public function __construct(
        private readonly Session $session,
        private readonly IncomingRequest $request,
        private readonly UserModel $users,
    ) {}

    public function user(): ?array
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $userId = $this->session->get('auth_user_id');
        if ($userId) {
            return $this->user = $this->users->decorate($this->users->find((int) $userId));
        }

        $rememberToken = $this->request->getCookie('sq_remember');
        if ($rememberToken) {
            $user = $this->users->where('remember_token', $rememberToken)->first();
            if ($user) {
                $this->completeLogin($user, true);
                return $this->user = $this->users->decorate($user);
            }
        }

        return null;
    }

    public function id(): ?int
    {
        return $this->user()['id'] ?? null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    /**
     * @return array{success:bool,two_factor:bool,error:?string}
     */
    public function attempt(string $email, string $password, bool $remember = false): array
    {
        $user = $this->users->where('email', trim(strtolower($email)))->first();
        if (! $user || ! password_verify($password, $user['password'])) {
            return ['success' => false, 'two_factor' => false, 'error' => 'These credentials do not match our records.'];
        }

        if (! empty($user['two_factor_secret']) && ! empty($user['two_factor_confirmed_at'])) {
            $this->session->set('pending_two_factor_user_id', (int) $user['id']);
            $this->session->set('pending_two_factor_remember', $remember);

            return ['success' => true, 'two_factor' => true, 'error' => null];
        }

        $this->completeLogin($user, $remember);

        return ['success' => true, 'two_factor' => false, 'error' => null];
    }

    public function loginById(int $userId, bool $remember = false): void
    {
        $user = $this->users->find($userId);
        if ($user) {
            $this->completeLogin($user, $remember);
        }
    }

    public function loginPendingTwoFactor(): void
    {
        $userId   = (int) $this->session->get('pending_two_factor_user_id');
        $remember = (bool) $this->session->get('pending_two_factor_remember');

        $this->clearPendingTwoFactor();

        if ($userId > 0) {
            $this->loginById($userId, $remember);
        }
    }

    public function pendingTwoFactorUserId(): ?int
    {
        $userId = $this->session->get('pending_two_factor_user_id');

        return $userId ? (int) $userId : null;
    }

    public function clearPendingTwoFactor(): void
    {
        $this->session->remove(['pending_two_factor_user_id', 'pending_two_factor_remember']);
    }

    public function logout(): void
    {
        $user = $this->user();
        if ($user) {
            $this->users->update((int) $user['id'], ['remember_token' => null]);
        }

        service('response')->deleteCookie('sq_remember');
        $this->session->remove(['auth_user_id', 'password_confirmed_at']);
        $this->session->regenerate(true);
        $this->user = null;
    }

    public function confirmPassword(string $password): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        if (! password_verify($password, $user['password'])) {
            return false;
        }

        $this->session->set('password_confirmed_at', time());

        return true;
    }

    public function passwordConfirmedRecently(int $seconds = 10800): bool
    {
        $confirmedAt = (int) ($this->session->get('password_confirmed_at') ?? 0);

        return $confirmedAt > 0 && (time() - $confirmedAt) < $seconds;
    }

    private function completeLogin(array $user, bool $remember): void
    {
        $this->session->regenerate(true);
        $this->session->set('auth_user_id', (int) $user['id']);
        $this->session->set('password_confirmed_at', time());

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->users->update((int) $user['id'], ['remember_token' => $token]);
            service('response')->setCookie('sq_remember', $token, YEAR, '', '/', '', false, true, 'Lax');
            $user['remember_token'] = $token;
        }

        $this->user = $this->users->decorate($user);
    }
}
