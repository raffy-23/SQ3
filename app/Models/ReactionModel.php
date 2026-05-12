<?php

namespace App\Models;

use CodeIgniter\Model;

class ReactionModel extends Model
{
    public const TYPES = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];

    protected $table            = 'reactions';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = ['post_id', 'user_id', 'type'];
}
