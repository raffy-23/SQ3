<?php

namespace App\Controllers;

use App\Models\CommentModel;
use App\Models\PostModel;

class CommentController extends BaseController
{
    public function store(int $postId)
    {
        $rules = [
            'content'   => 'required|string|min_length[1]|max_length[1000]',
            'parent_id' => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return $this->jsonOrRedirectError($this->validator->getErrors());
        }

        if (! model(PostModel::class)->find($postId)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $commentModel       = model(CommentModel::class);
        $requestedParentId  = (int) ($this->request->getPost('parent_id') ?: 0);
        $parentComment      = null;
        $parentId           = null;

        if ($requestedParentId > 0) {
            $parentComment = $commentModel->find($requestedParentId);
            if (! $parentComment || (int) ($parentComment['post_id'] ?? 0) !== $postId) {
                return $this->jsonOrRedirectError(['parent_id' => 'The selected comment cannot be replied to.']);
            }

            $parentId = ! empty($parentComment['parent_id'])
                ? (int) $parentComment['parent_id']
                : (int) $parentComment['id'];
        }

        $commentModel->insert([
            'post_id'   => $postId,
            'parent_id' => $parentId,
            'user_id'   => (int) $this->authUser['id'],
            'content'   => trim((string) $this->request->getPost('content')),
        ]);

        $commentId     = (int) $commentModel->getInsertID();
        $comment       = $commentModel->find($commentId);
        $commentsCount = $commentModel->where('post_id', $postId)->countAllResults();
        $author        = $this->authUser ? [
            'id'                  => (int) $this->authUser['id'],
            'username'            => $this->authUser['username'],
            'full_name'           => $this->authUser['full_name'],
            'profile_picture_url' => $this->authUser['profile_picture_url'],
        ] : null;

        if ($this->wantsJson()) {
            $commentData = [
                'id'                    => $commentId,
                'post_id'               => $postId,
                'parent_id'             => $parentId,
                'content'               => $comment['content'] ?? '',
                'author'                => $author,
                'created_at'            => $comment['created_at'] ?? '',
                'created_at_human'      => compact_comment_time($comment['created_at'] ?? null),
                'reactions_count'       => 0,
                'current_user_reaction' => null,
                'can_reply'             => $parentId === null,
                'can_edit'              => true,
                'can_delete'            => true,
                'can_hide'              => false,
                'replies'               => [],
            ];

            $html = view('partials/comment_row', [
                'comment'  => $commentData,
                'authUser' => $this->authUser,
                'depth'    => $parentId === null ? 0 : 1,
            ]);

            return $this->response->setJSON([
                'success'        => true,
                'html'           => $html,
                'comments_count' => $commentsCount,
                'parent_id'      => $parentId,
            ]);
        }

        return redirect()->back()->with('success', $parentId === null ? 'Comment added.' : 'Reply added.');
    }

    public function update(int $commentId)
    {
        $rules = [
            'content' => 'required|string|min_length[1]|max_length[1000]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return $this->jsonOrRedirectError($this->validator->getErrors());
        }

        $commentModel = model(CommentModel::class);
        $comment      = $commentModel->find($commentId);
        if (! $comment) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ((int) $comment['user_id'] !== (int) $this->authUser['id']) {
            return $this->jsonOrRedirectError(['error' => 'You may only edit your own comments.']);
        }

        $content = trim((string) $this->request->getPost('content'));
        $commentModel->update($commentId, ['content' => $content]);

        if ($this->wantsJson()) {
            return $this->response->setJSON([
                'success'    => true,
                'comment_id' => $commentId,
                'content'    => $content,
            ]);
        }

        return redirect()->back()->with('success', 'Comment updated.');
    }

    public function destroy(int $commentId)
    {
        $commentModel = model(CommentModel::class);
        $comment      = $commentModel->find($commentId);
        if (! $comment) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ((int) $comment['user_id'] !== (int) $this->authUser['id']) {
            return $this->jsonOrRedirectError(['error' => 'You may only delete your own comments.']);
        }

        $postId    = (int) $comment['post_id'];
        $deleteIds = [$commentId];

        $replyRows = $commentModel->where('parent_id', $commentId)->findAll();
        foreach ($replyRows as $reply) {
            $deleteIds[] = (int) $reply['id'];
        }

        $db = db_connect();

        if ($db->tableExists('comment_reactions')) {
            $db->table('comment_reactions')
                ->whereIn('comment_id', $deleteIds)
                ->delete();
        }

        if ($db->tableExists('hidden_comments')) {
            $db->table('hidden_comments')
                ->whereIn('comment_id', $deleteIds)
                ->delete();
        }

        $db->table('comments')
            ->whereIn('id', $deleteIds)
            ->delete();

        $commentsCount = $commentModel->where('post_id', $postId)->countAllResults();

        if ($this->wantsJson()) {
            return $this->response->setJSON([
                'success'        => true,
                'comments_count' => $commentsCount,
                'removed_ids'    => $deleteIds,
            ]);
        }

        return redirect()->back()->with('success', 'Comment deleted.');
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
