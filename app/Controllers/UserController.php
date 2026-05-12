<?php

namespace App\Controllers;

use App\Models\PostModel;
use App\Models\UserModel;

class UserController extends BaseController
{
    public function show(string $username)
    {
        $userModel   = model(UserModel::class);
        $profileUser = $userModel->findByUsername($username);

        if (! $profileUser) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $isOwn      = (int) $profileUser['id'] === (int) $this->authUser['id'];
        $requestedTab = strtolower((string) $this->request->getGet('tab'));
        $activeTab  = $isOwn && $requestedTab === 'saved' ? 'saved' : 'posts';
        $profile    = $userModel->publicProfile($profileUser);
        $postModel  = model(PostModel::class);
        $cursor     = $this->request->getGet('cursor');

        $page = $activeTab === 'saved'
            ? $postModel->savedPage((int) $profileUser['id'], (int) $this->authUser['id'], $cursor)
            : $postModel->userPage((int) $profileUser['id'], (int) $this->authUser['id'], $cursor);

        $query = [];
        if ($activeTab === 'saved') {
            $query['tab'] = 'saved';
        }
        if (! empty($page['next_cursor'])) {
            $query['cursor'] = (string) $page['next_cursor'];
        }

        $baseProfileUrl = site_url('u/' . rawurlencode($profileUser['username']));
        $nextPageUrl    = ! empty($page['next_cursor'])
            ? $baseProfileUrl . ($query !== [] ? '?' . http_build_query($query) : '')
            : null;

        $emptyMessage = $activeTab === 'saved'
            ? 'No saved posts yet.'
            : ($isOwn ? 'No posts yet.' : 'No posts to show.');

        if ($this->request->getGet('partial') === 'posts') {
            return $this->response->setJSON([
                'html'        => view('partials/profile_post_cards', [
                    'posts'        => $page['data'],
                    'authUser'     => $this->authUser,
                    'emptyMessage' => $emptyMessage,
                ]),
                'nextPageUrl' => $nextPageUrl,
            ]);
        }

        return $this->render('profile/show', [
            'profileUser'   => $profile,
            'isFollowing'   => $userModel->isFollowing((int) $this->authUser['id'], (int) $profileUser['id']),
            'isOwn'         => $isOwn,
            'activeTab'     => $activeTab,
            'feedCount'     => $activeTab === 'saved'
                ? (int) ($profile['saved_count'] ?? 0)
                : (int) ($profile['posts_count'] ?? 0),
            'posts'         => $page['data'],
            'emptyMessage'  => $emptyMessage,
            'nextPageUrl'   => $nextPageUrl,
        ]);
    }
}
