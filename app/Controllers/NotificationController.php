<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class NotificationController extends BaseController
{
    public function index()
    {
        $model         = model(NotificationModel::class);
        $notifications = $model
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', (int) $this->authUser['id'])
            ->orderBy('created_at', 'DESC')
            ->findAll(50);

        $db = \Config\Database::connect();

        $formatted = array_map(function (array $notification) use ($model, $db): array {
            $row  = $model->format($notification);
            $data = $row['data'];

            // Always include these keys so the JS never reads undefined
            $row['sender_name']   = null;
            $row['sender_avatar'] = null;

            // Enrich with sender profile picture if sender_id (or follower_id for follows) is present
            $senderId = (int) ($data['sender_id'] ?? $data['follower_id'] ?? 0);
            if ($senderId > 0) {
                $sender = $db->table('users')
                    ->select('profile_picture_path, username, first_name, last_name')
                    ->where('id', $senderId)
                    ->get()->getRowArray();
                if ($sender) {
                    $path = $sender['profile_picture_path'] ?? '';
                    $row['sender_avatar'] = $path
                        ? base_url('storage/' . ltrim($path, '/'))
                        : null;
                    $row['sender_name'] = trim(($sender['first_name'] ?? '') . ' ' . ($sender['last_name'] ?? ''))
                        ?: ($sender['username'] ?? null);
                }
            }

            return $row;
        }, $notifications);

        return $this->response->setJSON([
            'notifications' => $formatted,
            'unread_count'  => $model
                ->where('notifiable_type', 'App\\Models\\User')
                ->where('notifiable_id', (int) $this->authUser['id'])
                ->where('read_at', null)
                ->countAllResults(),
        ]);
    }

    public function markAsRead(string $id)
    {
        model(NotificationModel::class)
            ->where('id', $id)
            ->where('notifiable_id', (int) $this->authUser['id'])
            ->set(['read_at' => date('Y-m-d H:i:s')])
            ->update();

        return $this->response->setJSON(['success' => true]);
    }

    public function markAllAsRead()
    {
        model(NotificationModel::class)
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', (int) $this->authUser['id'])
            ->where('read_at', null)
            ->set(['read_at' => date('Y-m-d H:i:s')])
            ->update();

        return $this->response->setJSON(['success' => true]);
    }
}
