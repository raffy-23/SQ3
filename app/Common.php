<?php

use App\Services\AuthService;
use CodeIgniter\I18n\Time;

if (! function_exists('auth_service')) {
    function auth_service(): AuthService
    {
        return service('authService');
    }
}

if (! function_exists('current_user')) {
    function current_user(): ?array
    {
        return auth_service()->user();
    }
}

if (! function_exists('auth_check')) {
    function auth_check(): bool
    {
        return auth_service()->check();
    }
}

if (! function_exists('appearance_mode')) {
    function appearance_mode(): string
    {
        return service('request')->getCookie('appearance') ?? 'system';
    }
}

if (! function_exists('reaction_icons')) {
    function reaction_icons(): array
    {
        return [
            'like'  => base_url('reactions/like.svg'),
            'love'  => base_url('reactions/love.svg'),
            'haha'  => base_url('reactions/haha.svg'),
            'wow'   => base_url('reactions/wow.svg'),
            'sad'   => base_url('reactions/sad.svg'),
            'angry' => base_url('reactions/angry.svg'),
        ];
    }
}

if (! function_exists('user_initials')) {
    function user_initials(?array $user): string
    {
        if ($user === null) {
            return 'SQ';
        }

        $first = strtoupper(substr((string) ($user['first_name'] ?? ''), 0, 1));
        $last  = strtoupper(substr((string) ($user['last_name'] ?? ''), 0, 1));

        if (trim($first . $last) !== '') {
            return $first . $last;
        }

        $fullName = trim((string) ($user['full_name'] ?? $user['name'] ?? ''));
        if ($fullName !== '') {
            $parts = preg_split('/\s+/', $fullName) ?: [];
            $parts = array_values(array_filter($parts, static fn ($part): bool => $part !== ''));

            if (count($parts) >= 2) {
                return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
            }

            if (isset($parts[0])) {
                return strtoupper(substr($parts[0], 0, 2));
            }
        }

        return strtoupper(substr((string) ($user['username'] ?? 'SQ'), 0, 2));
    }
}

if (! function_exists('human_time')) {
    function human_time(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return Time::parse($value)->humanize();
    }
}

if (! function_exists('compact_comment_time')) {
    function compact_comment_time(?string $value): string
    {
        if ($value === null || $value === '') {
            return 'now';
        }

        $createdAt = Time::parse($value);
        $seconds = max(0, Time::now($createdAt->getTimezoneName())->getTimestamp() - $createdAt->getTimestamp());

        if ($seconds < 60) {
            return 'now';
        }

        if ($seconds < 3600) {
            return max(1, (int) floor($seconds / 60)) . 'm';
        }

        if ($seconds < 86400) {
            return (int) floor($seconds / 3600) . 'h';
        }

        $days = (int) floor($seconds / 86400);
        if ($days < 7) {
            return $days . 'd';
        }

        if ($days < 365) {
            return (int) floor($days / 7) . 'w';
        }

        return (int) floor($days / 365) . 'y';
    }
}

if (! function_exists('selected_appearance_class')) {
    function selected_appearance_class(string $mode, string $current): string
    {
        return $mode === $current
            ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
            : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60';
    }
}

if (! function_exists('notification_type_name')) {
    function notification_type_name(string $type): string
    {
        return str_contains($type, '\\') ? substr($type, (int) strrpos($type, '\\') + 1) : $type;
    }
}
