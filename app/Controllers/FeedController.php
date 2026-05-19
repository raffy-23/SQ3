<?php

namespace App\Controllers;

use App\Models\PostModel;

class FeedController extends BaseController
{
    public function index()
    {
        $page       = model(PostModel::class)->feedPage((int) $this->authUser['id'], $this->request->getGet('cursor'));
        $nextPageUrl = $page['next_cursor'] ? site_url('feed?cursor=' . urlencode($page['next_cursor'])) : null;

        if ($this->request->getGet('partial') === 'posts') {
            return $this->response->setJSON([
                'html'        => view('partials/post_cards', ['posts' => $page['data'], 'authUser' => $this->authUser]),
                'nextPageUrl' => $nextPageUrl,
            ]);
        }

        return $this->render('feed', [
            'pageTitle'    => 'Feed',
            'topbarLabel'  => 'Social feed',
            'mainClass'    => 'sq-main-feed',
            'contentClass' => 'sq-content-feed',
            'posts'        => $page['data'],
            'nextPageUrl'  => $nextPageUrl,
        ]);


    }
}
