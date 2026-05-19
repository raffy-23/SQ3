<?php

namespace Config;

use App\Services\AuthService;
use App\Models\UserModel;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    public static function authService(bool $getShared = true): AuthService
    {
        if ($getShared) {
            return static::getSharedInstance('authService');
        }

        return new AuthService(session(), service('request'), model(UserModel::class));
    }
}
