<?php

namespace App\Models;

use CodeIgniter\Model;

class HiddenCommentModel extends Model
{
    protected $table            = 'hidden_comments';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = ['user_id', 'comment_id'];

    /**
     * @return list<int>
     */
    public function hiddenCommentIds(int $userId): array
    {
        if ($userId < 1) {
            return [];
        }

        return array_map(
            'intval',
            array_column(
                $this->where('user_id', $userId)->findAll(),
                'comment_id'
            )
        );
    }
}
