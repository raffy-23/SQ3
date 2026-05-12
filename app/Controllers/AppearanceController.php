<?php

namespace App\Controllers;

class AppearanceController extends BaseController
{
    public function update()
    {
        $mode = (string) $this->request->getPost('appearance');
        if (! in_array($mode, ['light', 'dark', 'system'], true)) {
            return redirect()->back()->with('error', 'Invalid appearance selection.');
        }

        return redirect()->back()->setCookie('appearance', $mode, YEAR, '', '/', '', false, true, 'Lax');
    }
}
