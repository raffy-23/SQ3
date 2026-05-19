<?php

namespace App\Controllers;

use App\Models\PostModel;
use App\Models\SavedPostModel;

class PostSaveController extends BaseController
{
    public function store(int $postId)
    {
        $post = model(PostModel::class)->find($postId);
        if (! $post) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ((int) $post['user_id'] === (int) $this->authUser['id']) {
            return $this->jsonOrRedirectError(['error' => 'You cannot save your own post.']);
        }

        $savedPostModel = model(SavedPostModel::class);
        $existing       = $savedPostModel
            ->where('user_id', (int) $this->authUser['id'])
            ->where('post_id', $postId)
            ->first();

        if (! $existing) {
            $savedPostModel->insert([
                'user_id' => (int) $this->authUser['id'],
                'post_id' => $postId,
            ]);
        }

        if ($this->wantsJson()) {
            return $this->response->setJSON([
                'success'  => true,
                'is_saved' => true,
                'message'  => 'Post saved.',
            ]);
        }

        return redirect()->back()->with('success', 'Post saved.');
    }

    public function destroy(int $postId)
    {
        $savedPostModel = model(SavedPostModel::class);
        $existing       = $savedPostModel
            ->where('user_id', (int) $this->authUser['id'])
            ->where('post_id', $postId)
            ->first();

        if ($existing) {
            $savedPostModel->delete((int) $existing['id']);
        }

        if ($this->wantsJson()) {

            return $this->response->setJSON([
                'success'  => true,
                'is_saved' => false,
                'message'  => 'Post removed from saved.',
            ]);
        }

        return redirect()->back()->with('success', 'Post removed from saved.');
    }


}
