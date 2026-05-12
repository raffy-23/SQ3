<?php

namespace App\Controllers;

use App\Models\CommentModel;
use App\Models\HiddenCommentModel;
use App\Models\PostModel;

class CommentHideController extends BaseController
{
    public function index(int $postId)
    {
        if (! model(PostModel::class)->find($postId)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $comments = model(PostModel::class)->hiddenCommentsForPost($postId, (int) $this->authUser['id']);
        $html     = view('partials/hidden_comments_panel', [
            'comments' => $comments,
            'authUser' => $this->authUser,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'count'   => count($comments),
            'html'    => $html,
        ]);
    }

    public function store(int $commentId)
    {
        $comment = model(CommentModel::class)->find($commentId);
        if (! $comment) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ((int) $comment['user_id'] === (int) $this->authUser['id']) {
            return $this->jsonOrRedirectError(['error' => 'You cannot hide your own comment.']);
        }

        $hiddenCommentModel = model(HiddenCommentModel::class);
        $existing           = $hiddenCommentModel
            ->where('user_id', (int) $this->authUser['id'])
            ->where('comment_id', $commentId)
            ->first();

        if (! $existing) {
            $hiddenCommentModel->insert([
                'user_id'    => (int) $this->authUser['id'],
                'comment_id' => $commentId,
            ]);
        }

        $postId        = (int) $comment['post_id'];
        $hydratedPost  = model(PostModel::class)->hydratedPost($postId, (int) $this->authUser['id']);
        $commentsCount = (int) ($hydratedPost['comments_count'] ?? 0);

        if ($this->wantsJson()) {
            return $this->response->setJSON([
                'success'        => true,
                'message'        => 'Comment hidden from your view.',
                'comments_count' => $commentsCount,
            ]);
        }

        return redirect()->back()->with('success', 'Comment hidden from your view.');
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
