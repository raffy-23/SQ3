<?php

namespace App\Services;

use App\Models\NotificationModel;

/**
 * Thin helper for writing rows to the `notifications` table.
 * Controllers call NotificationService::notify() after user-triggered events.
 */
class NotificationService
{
    /**
     * Insert a notification for $recipientId.
     * Silently skips when sender === recipient (no self-notifications).
     *
     * @param int    $recipientId  The user who should receive the notification
     * @param int    $senderId     The user who triggered the event
     * @param string $type         Short class-name style type, e.g. 'PostReactionNotification'
     * @param array  $data         JSON-serialisable payload stored in the `data` column
     */
    public static function notify(int $recipientId, int $senderId, string $type, array $data): void
    {
        // Never notify yourself
        if ($recipientId === $senderId || $recipientId <= 0) {
            return;
        }

        try {
            model(NotificationModel::class)->insert([
                'id'              => bin2hex(random_bytes(16)),
                'type'            => $type,
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id'   => $recipientId,
                'data'            => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'read_at'         => null,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[NotificationService] Failed to insert notification: ' . $e->getMessage());
        }
    }
}
