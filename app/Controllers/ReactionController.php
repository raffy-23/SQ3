<?php

namespace App\Controllers;

use App\Models\PostModel;
use App\Models\ReactionModel;
use App\Models\UserModel;
use App\Services\NotificationService;

class ReactionController extends BaseController
{
    public function index(int $postId)
    {
        $reactions = model(ReactionModel::class)
            ->where('post_id', $postId)
            ->findAll();

        $userIds    = array_values(array_unique(array_map(static fn (array $reaction): int => (int) $reaction['user_id'], $reactions)));
        $users      = [];
        $userModel  = model(UserModel::class);
        foreach ($userModel->whereIn('id', $userIds === [] ? [0] : $userIds)->findAll() as $user) {
            $users[(int) $user['id']] = $userModel->decorate($user);
        }

        $payload = array_map(function (array $reaction) use ($users): array {
            $user = $users[(int) $reaction['user_id']] ?? null;

            return [
                'type' => $reaction['type'],
                'user' => $user ? [
                    'id'                  => (int) $user['id'],
                    'username'            => $user['username'],
                    'full_name'           => $user['full_name'],
                    'profile_picture_url' => $user['profile_picture_url'],
                ] : null,
            ];
        }, $reactions);

        return $this->response->setJSON(['reactions' => $payload]);
    }

    public function store(int $postId)
    {
        $rules = [
            'type' => 'required|in_list[' . implode(',', ReactionModel::TYPES) . ']',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return $this->jsonOrRedirectError($this->validator->getErrors());
        }

        $reactionModel = model(ReactionModel::class);
        $existing      = $reactionModel
            ->where('post_id', $postId)
            ->where('user_id', (int) $this->authUser['id'])
            ->first();
        $type          = (string) $this->request->getPost('type');

        if ($existing) {
            if (($existing['type'] ?? null) === $type) {
                $reactionModel->delete((int) $existing['id']);
            } else {
                $reactionModel->update((int) $existing['id'], ['type' => $type]);
            }
        } else {
            $reactionModel->insert([
                'post_id' => $postId,
                'user_id' => (int) $this->authUser['id'],
                'type'    => $type,
            ]);

            // Notify the post owner — only on a brand-new reaction
            $post = model(PostModel::class)->find($postId);
            if ($post) {
                $actorName = trim((string) ($this->authUser['full_name'] ?? $this->authUser['username'] ?? 'Someone'));
                $emoji     = match ($type) {
                    'like'    => '👍',
                    'love'    => '❤️',
                    'haha'    => '😂',
                    'wow'     => '😮',
                    'sad'     => '😢',
                    'angry'   => '😡',
                    default   => '👍',
                };
                NotificationService::notify(
                    (int) $post['user_id'],
                    (int) $this->authUser['id'],
                    'PostReactionNotification',
                    [
                        'sender_id'        => (int) $this->authUser['id'],
                        'message'          => "{$actorName} reacted {$emoji} to your post.",
                        'actor_name'       => $actorName,
                        'actor_username'   => $this->authUser['username'] ?? '',
                        'post_id'          => $postId,
                        'reaction_type'    => $type,
                    ]
                );
            }
        }

        $post = model(PostModel::class)->hydratedPost($postId, (int) $this->authUser['id']);

        if ($this->wantsJson()) {
            return $this->response->setJSON([
                'success'               => true,
                'reactions_breakdown'   => $post['reactions_breakdown'] ?? [],
                'reactions_count'       => $post['reactions_count'] ?? 0,
                'current_user_reaction' => $post['current_user_reaction'] ?? null,
            ]);
        }

        return redirect()->back();
    }
}
