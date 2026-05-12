<?php

namespace App\Models;

use CodeIgniter\Model;

class FollowModel extends Model
{
    protected $table            = 'follows';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = ['follower_id', 'following_id'];

    /**
     * @return list<int>
     */
    public function followingIds(int $userId): array
    {
        return array_map(
            'intval',
            array_column(
                $this->select('following_id')->where('follower_id', $userId)->findAll(),
                'following_id'
            )
        );
    }
}
