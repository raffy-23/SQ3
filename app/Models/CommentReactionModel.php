<?php

namespace App\Models;

use CodeIgniter\Model;

class CommentReactionModel extends Model
{
    public const TYPES = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];

    protected $table            = 'comment_reactions';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = ['comment_id', 'user_id', 'type'];

    /**
     * @param list<int> $commentIds
     * @return array<int,string>
     */
    public function reactedCommentTypes(int $userId, array $commentIds): array
    {
        if ($userId < 1 || $commentIds === []) {
            return [];
        }

        $rows = $this->select('comment_id, type')
            ->where('user_id', $userId)
            ->whereIn('comment_id', $commentIds)
            ->findAll();

        $types = [];
        foreach ($rows as $row) {
            $types[(int) $row['comment_id']] = (string) ($row['type'] ?? 'like');
        }

        return $types;
    }

    /**
     * @param list<int> $commentIds
     * @return array<int,int>
     */
    public function countsByCommentIds(array $commentIds): array
    {
        if ($commentIds === []) {
            return [];
        }

        $rows = $this->select('comment_id, COUNT(*) AS total')
            ->whereIn('comment_id', $commentIds)
            ->groupBy('comment_id')
            ->findAll();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['comment_id']] = (int) ($row['total'] ?? 0);
        }

        return $counts;
    }

    /**
     * @param list<int> $commentIds
     * @return array<int,array<string,int>>
     */
    public function breakdownsByCommentIds(array $commentIds): array
    {
        if ($commentIds === []) {
            return [];
        }

        $rows = $this->builder()
            ->select('comment_id, type, COUNT(*) AS total')
            ->whereIn('comment_id', $commentIds)
            ->groupBy('comment_id')
            ->groupBy('type')
            ->get()
            ->getResultArray();

        $breakdowns = [];
        foreach ($rows as $row) {
            $commentId = (int) $row['comment_id'];
            $type      = in_array((string) ($row['type'] ?? ''), self::TYPES, true) ? (string) $row['type'] : 'like';

            $breakdowns[$commentId][$type] = (int) ($row['total'] ?? 0);
        }

        return $breakdowns;
    }

    /**
     * @param list<int> $commentIds
     * @return array<int,string>
     */
    public function displayTypesByCommentIds(array $commentIds): array
    {
        $breakdowns = $this->breakdownsByCommentIds($commentIds);
        $types      = [];

        foreach ($breakdowns as $commentId => $breakdown) {
            $bestType  = null;
            $bestTotal = 0;

            foreach (self::TYPES as $type) {
                $total = (int) ($breakdown[$type] ?? 0);
                if ($total > $bestTotal) {
                    $bestType  = $type;
                    $bestTotal = $total;
                }
            }

            if ($bestType !== null) {
                $types[(int) $commentId] = $bestType;
            }
        }

        return $types;
    }


}
