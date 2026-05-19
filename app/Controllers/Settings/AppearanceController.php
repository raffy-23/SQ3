<?php

namespace App\Controllers\Settings;

use App\Controllers\BaseController;

class AppearanceController extends BaseController
{
    public function edit()
    {
        return $this->render('settings/layout', [
            'settingsView' => 'settings/appearance',
            'pageTitle'    => 'Settings',
            'topbarLabel'  => 'Manage your account settings',
        ]);
    }
}
