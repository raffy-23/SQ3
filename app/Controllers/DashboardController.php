<?php

namespace App\Controllers;

use App\Models\PostModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $page       = model(PostModel::class)->feedPage((int) $this->authUser['id'], $this->request->getGet('cursor'));
        $nextPageUrl = $page['next_cursor'] ? site_url('dashboard?cursor=' . urlencode($page['next_cursor'])) : null;

        if ($this->request->getGet('partial') === 'posts') {
            return $this->response->setJSON([
                'html'        => view('partials/post_cards', ['posts' => $page['data'], 'authUser' => $this->authUser]),
                'nextPageUrl' => $nextPageUrl,
            ]);
        }

        return $this->render('dashboard', [
            'pageTitle'    => 'Feed',
            'topbarLabel'  => 'Social feed',
            'mainClass'    => 'sq-main-dashboard',
            'contentClass' => 'sq-content-dashboard',
            'posts'        => $page['data'],
            'nextPageUrl'  => $nextPageUrl,
        ]);


    }
}
