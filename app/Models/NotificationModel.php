<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = false;
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = ['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at'];

    public function format(array $notification): array
    {
        $data = json_decode($notification['data'] ?? '{}', true);
        $type = $notification['type'] ?? '';
        $type = str_contains($type, '\\') ? substr($type, (int) strrpos($type, '\\') + 1) : $type;

        return [
            'id'         => $notification['id'],
            'type'       => $type,
            'data'       => is_array($data) ? $data : [],
            'read_at'    => $notification['read_at'],
            'created_at' => empty($notification['created_at']) ? '' : Time::parse($notification['created_at'])->humanize(),
        ];
    }
}
