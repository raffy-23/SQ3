<?php

namespace App\Models;

use CodeIgniter\Model;

class HiddenPostModel extends Model
{
    protected $table            = 'hidden_posts';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = ['user_id', 'post_id'];

    /**
     * @return list<int>
     */
    public function hiddenPostIds(int $userId): array
    {
        if ($userId < 1) {
            return [];
        }

        return array_map(
            'intval',
            array_column(
                $this->where('user_id', $userId)->findAll(),
                'post_id'
            )
        );
    }
}
