<?php

namespace App\Controllers;

use App\Models\HiddenPostModel;
use App\Models\PostModel;

class PostHideController extends BaseController
{
    public function store(int $postId)
    {
        $post = model(PostModel::class)->find($postId);
        if (! $post) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ((int) $post['user_id'] === (int) $this->authUser['id']) {
            return $this->jsonOrRedirectError(['error' => 'You cannot hide your own post.']);
        }

        $hiddenPostModel = model(HiddenPostModel::class);
        $existing        = $hiddenPostModel
            ->where('user_id', (int) $this->authUser['id'])
            ->where('post_id', $postId)
            ->first();

        if (! $existing) {
            $hiddenPostModel->insert([
                'user_id' => (int) $this->authUser['id'],
                'post_id' => $postId,
            ]);
        }

        if ($this->wantsJson()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Post hidden from your feed.',
            ]);
        }

        return redirect()->back()->with('success', 'Post hidden from your feed.');
    }

    private function wantsJson(): bool
    {
        return $this->request->isAJAX() || str_contains($this->request->getHeaderLine('Accept'), 'application/json');
    }

    private function jsonOrRedirectError(array $errors)
    {
        if ($this->wantsJson()) {
            return $this->response->setStatusCode(422)->setJSON(['errors' => $errors]);
        }

        return redirect()->back()->withInput()->with('errors', $errors);
    }
}
