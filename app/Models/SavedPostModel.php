<?php

namespace App\Models;

use CodeIgniter\Model;

class SavedPostModel extends Model
{
    protected $table            = 'saved_posts';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = ['user_id', 'post_id'];

    /**
     * @param list<int> $postIds
     * @return list<int>
     */
    public function savedPostIds(int $userId, array $postIds): array
    {
        if ($userId < 1 || $postIds === []) {
            return [];
        }

        return array_map(
            'intval',
            array_column(
                $this->where('user_id', $userId)
                    ->whereIn('post_id', $postIds)
                    ->findAll(),
                'post_id'
            )
        );
    }

    public function countForUser(int $userId): int
    {
        if ($userId < 1) {
            return 0;
        }

        return $this->where('user_id', $userId)->countAllResults();
    }
}
