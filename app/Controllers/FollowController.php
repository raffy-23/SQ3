<?php

namespace App\Controllers;

use App\Models\FollowModel;
use App\Models\NotificationModel;
use App\Models\UserModel;

class FollowController extends BaseController
{
    public function store(int $userId)
    {
        if ($userId === (int) $this->authUser['id']) {
            return $this->responseForFollow(false, 'You cannot follow yourself.', $userId, false);
        }

        $followModel = model(FollowModel::class);
        $existing    = $followModel
            ->where('follower_id', (int) $this->authUser['id'])
            ->where('following_id', $userId)
            ->first();

        if (! $existing) {
            $followModel->insert([
                'follower_id'  => (int) $this->authUser['id'],
                'following_id' => $userId,
            ]);

            model(NotificationModel::class)->insert([
                'id'              => $this->uuid(),
                'type'            => 'App\\Notifications\\NewFollowerNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id'   => $userId,
                'data'            => json_encode([
                    'follower_id'       => (int) $this->authUser['id'],
                    'follower_name'     => $this->authUser['full_name'],
                    'follower_username' => $this->authUser['username'],
                    'message'           => $this->authUser['full_name'] . ' started following you.',
                ], JSON_UNESCAPED_SLASHES),
                'read_at'         => null,
            ]);
        }

        return $this->responseForFollow(true, 'Followed user.', $userId, true);
    }

    public function destroy(int $userId)
    {
        model(FollowModel::class)
            ->where('follower_id', (int) $this->authUser['id'])
            ->where('following_id', $userId)
            ->delete();

        return $this->responseForFollow(true, 'Unfollowed user.', $userId, false);
    }

    private function responseForFollow(bool $success, string $message, int $userId = 0, bool $isFollowing = false)
    {
        if ($this->request->isAJAX() || str_contains($this->request->getHeaderLine('Accept'), 'application/json')) {
            $payload = ['success' => $success, 'message' => $message];

            if ($success && $userId > 0) {
                $userModel = model(UserModel::class);
                $targetUser = $userModel->find($userId);
                $followModel = model(FollowModel::class);

                $followersCount = $followModel->where('following_id', $userId)->countAllResults(false);
                $followingCount = $followModel->where('follower_id', $userId)->countAllResults(false);

                $payload['is_following']    = $isFollowing;
                $payload['user_id']         = $userId;
                $payload['followers_count'] = $followersCount;
                $payload['following_count'] = $followingCount;
            }

            return $this->response->setJSON($payload);
        }

        return redirect()->back()->with($success ? 'success' : 'error', $message);
    }

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
