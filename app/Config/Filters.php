<?php

namespace Config;

use App\Filters\AuthFilter;
use App\Filters\GuestFilter;
use App\Filters\PasswordConfirmFilter;
use App\Filters\VerifiedFilter;
use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf'            => CSRF::class,
        'toolbar'         => DebugToolbar::class,
        'honeypot'        => Honeypot::class,
        'invalidchars'    => InvalidChars::class,
        'secureheaders'   => SecureHeaders::class,
        'cors'            => Cors::class,
        'performance'     => PerformanceMetrics::class,
        'auth'            => AuthFilter::class,
        'guest'           => GuestFilter::class,
        'verified'        => VerifiedFilter::class,
        'passwordconfirm' => PasswordConfirmFilter::class,
    ];

    public array $required = [
        'before' => [],
        'after'  => [
            'performance',
            'toolbar',
        ],
    ];

    public array $globals = [
        'before' => [
            'csrf',
        ],
        'after' => [],
    ];

    public array $methods = [];

    public array $filters = [];
}
