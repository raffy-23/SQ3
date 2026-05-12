<?php

namespace App\Controllers;

use App\Models\CommentModel;
use App\Models\CommentReactionModel;

class CommentReactionController extends BaseController
{
    public function store(int $commentId)
    {
        $rules = [
            'type' => 'required|in_list[' . implode(',', CommentReactionModel::TYPES) . ']',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return $this->jsonOrRedirectError($this->validator->getErrors());
        }

        $comment = model(CommentModel::class)->find($commentId);
        if (! $comment) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $reactionModel = model(CommentReactionModel::class);
        $existing = $reactionModel
            ->where('comment_id', $commentId)
            ->where('user_id', (int) $this->authUser['id'])
            ->first();
        $type = (string) $this->request->getPost('type');

        if ($existing) {
            if (($existing['type'] ?? null) === $type) {
                $reactionModel->delete((int) $existing['id']);
            } else {
                $reactionModel->update((int) $existing['id'], ['type' => $type]);
            }
        } else {
            $reactionModel->insert([
                'comment_id' => $commentId,
                'user_id'    => (int) $this->authUser['id'],
                'type'       => $type,
            ]);
        }

        $current = $reactionModel
            ->select('type')
            ->where('comment_id', $commentId)
            ->where('user_id', (int) $this->authUser['id'])
            ->first();

        $breakdowns = $reactionModel->breakdownsByCommentIds([$commentId]);
        $displayTypes = $reactionModel->displayTypesByCommentIds([$commentId]);
        $breakdown = $breakdowns[$commentId] ?? [];
        $reactionsCount = array_sum($breakdown);

        if ($this->wantsJson()) {
            return $this->response->setJSON([
                'success'               => true,
                'comment_id'            => $commentId,
                'reactions_breakdown'   => $breakdown,
                'reactions_count'       => $reactionsCount,
                'reaction_display_type' => $displayTypes[$commentId] ?? null,
                'current_user_reaction' => $current['type'] ?? null,
            ]);
        }



        return redirect()->back();
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
