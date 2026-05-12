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

        return $this->response->setJSON([
            'notifications' => array_map(fn (array $notification): array => $model->format($notification), $notifications),
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
