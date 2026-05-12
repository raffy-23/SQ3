<?php

namespace App\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Model;

class PostModel extends Model
{
    protected $table            = 'posts';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = ['user_id', 'content', 'photo_path', 'media_type'];

    /**
     * @return array{data:list<array<string,mixed>>,next_cursor:?string,per_page:int}
     */
    public function feedPage(int $viewerId, ?string $cursor = null, int $perPage = 15): array
    {
        $followModel = model(FollowModel::class);
        $ids         = array_values(array_unique([...$followModel->followingIds($viewerId), $viewerId]));

        if ($ids === []) {
            return ['data' => [], 'next_cursor' => null, 'per_page' => $perPage];
        }

        $builder = $this->builder();
        $builder->whereIn('user_id', $ids);
        $this->applyHiddenFilter($builder, $viewerId);

        if ($cursor !== null && $cursor !== '') {
            $builder->where('id <', (int) $cursor);
        }

        $rows = $builder
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get($perPage + 1)
            ->getResultArray();

        return $this->buildPage($rows, $viewerId, $perPage);
    }

    /**
     * @return array{data:list<array<string,mixed>>,next_cursor:?string,per_page:int}
     */
    public function userPage(int $profileUserId, int $viewerId, ?string $cursor = null, int $perPage = 15): array
    {
        $builder = $this->builder();
        $builder->where('user_id', $profileUserId);
        $this->applyHiddenFilter($builder, $viewerId);

        if ($cursor !== null && $cursor !== '') {
            $builder->where('id <', (int) $cursor);
        }

        $rows = $builder
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get($perPage + 1)
            ->getResultArray();

        return $this->buildPage($rows, $viewerId, $perPage);
    }

    /**
     * @return array{data:list<array<string,mixed>>,next_cursor:?string,per_page:int}
     */
    public function savedPage(int $ownerId, int $viewerId, ?string $cursor = null, int $perPage = 15): array
    {
        if (! $this->db->tableExists('saved_posts')) {
            return ['data' => [], 'next_cursor' => null, 'per_page' => $perPage];
        }

        $builder = $this->builder();
        $builder
            ->select('posts.*, saved_posts.id AS saved_cursor, saved_posts.created_at AS saved_at')
            ->join('saved_posts', 'saved_posts.post_id = posts.id')
            ->where('saved_posts.user_id', $ownerId);

        if ($cursor !== null && $cursor !== '') {
            $builder->where('saved_posts.id <', (int) $cursor);
        }

        $rows = $builder
            ->orderBy('saved_posts.created_at', 'DESC')
            ->orderBy('saved_posts.id', 'DESC')
            ->get($perPage + 1)
            ->getResultArray();

        return $this->buildPage($rows, $viewerId, $perPage, 'saved_cursor');
    }

    public function hydratedPost(int $postId, int $viewerId): ?array
    {
        $post = $this->find($postId);

        if ($post === null) {
            return null;
        }

        return $this->hydratePosts([$post], $viewerId)[0] ?? null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function hiddenCommentsForPost(int $postId, int $viewerId): array
    {
        if ($postId < 1 || $viewerId < 1 || ! $this->db->tableExists('hidden_comments')) {
            return [];
        }

        $hiddenCommentIds = array_fill_keys(model(HiddenCommentModel::class)->hiddenCommentIds($viewerId), true);
        if ($hiddenCommentIds === []) {
            return [];
        }

        $commentModel         = model(CommentModel::class);
        $userModel            = model(UserModel::class);
        $commentReactionModel = $this->db->tableExists('comment_reactions') ? model(CommentReactionModel::class) : null;

        $commentRows = $commentModel
            ->where('post_id', $postId)
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        if ($commentRows === []) {
            return [];
        }

        $commentRows = array_values(array_filter(
            $commentRows,
            static function (array $comment) use ($hiddenCommentIds): bool {
                $commentId = (int) ($comment['id'] ?? 0);
                $parentId  = isset($comment['parent_id']) ? (int) $comment['parent_id'] : 0;

                return isset($hiddenCommentIds[$commentId]) || ($parentId > 0 && isset($hiddenCommentIds[$parentId]));
            }
        ));

        if ($commentRows === []) {
            return [];
        }

        $authors = [];
        $commentAuthorIds = array_values(array_unique(array_map(static fn (array $comment): int => (int) $comment['user_id'], $commentRows)));
        if ($commentAuthorIds !== []) {
            foreach ($userModel->whereIn('id', $commentAuthorIds)->findAll() as $commentAuthor) {
                $authors[(int) $commentAuthor['id']] = $userModel->decorate($commentAuthor);
            }
        }

        $commentReactionCounts       = [];
        $commentReactionLookup       = [];
        $commentReactionBreakdowns   = [];
        $commentReactionDisplayTypes = [];
        if ($commentRows !== [] && $commentReactionModel instanceof CommentReactionModel) {
            $commentIds = array_map(static fn (array $comment): int => (int) $comment['id'], $commentRows);
            $commentReactionBreakdowns   = $commentReactionModel->breakdownsByCommentIds($commentIds);
            $commentReactionDisplayTypes = $commentReactionModel->displayTypesByCommentIds($commentIds);

            foreach ($commentReactionBreakdowns as $reactionCommentId => $breakdown) {
                $commentReactionCounts[(int) $reactionCommentId] = array_sum($breakdown);
            }

            $commentReactionLookup = $commentReactionModel->reactedCommentTypes($viewerId, $commentIds);
        }

        $commentNodes = [];
        $commentOrder = [];
        foreach ($commentRows as $comment) {
            $commentId = (int) $comment['id'];
            $author    = $authors[(int) $comment['user_id']] ?? null;
            $parentId  = isset($comment['parent_id']) && (int) $comment['parent_id'] > 0
                ? (int) $comment['parent_id']
                : null;

            $commentNodes[$commentId] = [
                'id'                    => $commentId,
                'post_id'               => (int) $comment['post_id'],
                'parent_id'             => $parentId,
                'content'               => $comment['content'],
                'author'                => $author ? [
                    'id'                  => (int) $author['id'],
                    'username'            => $author['username'],
                    'full_name'           => $author['full_name'],
                    'profile_picture_url' => $author['profile_picture_url'],
                ] : null,
                'created_at'            => $comment['created_at'],
                'created_at_human'      => compact_comment_time($comment['created_at'] ?? null),
                'reactions_count'       => (int) ($commentReactionCounts[$commentId] ?? 0),
                'reactions_breakdown'   => $commentReactionBreakdowns[$commentId] ?? [],
                'reaction_display_type' => $commentReactionDisplayTypes[$commentId] ?? null,
                'current_user_reaction' => $commentReactionLookup[$commentId] ?? null,
                'can_reply'             => $viewerId > 0 && $parentId === null,
                'can_edit'              => (int) $comment['user_id'] === $viewerId,
                'can_delete'            => (int) $comment['user_id'] === $viewerId,
                'can_hide'              => false,
                'replies'               => [],
            ];
            $commentOrder[] = $commentId;
        }

        foreach ($commentOrder as $commentId) {
            $parentId = $commentNodes[$commentId]['parent_id'];
            if ($parentId === null) {
                continue;
            }

            if (! isset($commentNodes[$parentId])) {
                $commentNodes[$commentId]['parent_id'] = null;
                $commentNodes[$commentId]['can_reply'] = $viewerId > 0;
                continue;
            }

            $commentNodes[$parentId]['replies'][] = $commentNodes[$commentId];
        }

        $hiddenComments = [];
        foreach ($commentOrder as $commentId) {
            $commentNode = $commentNodes[$commentId];
            if ($commentNode['parent_id'] !== null) {
                continue;
            }

            $hiddenComments[] = $commentNode;
        }

        return $hiddenComments;
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return array{data:list<array<string,mixed>>,next_cursor:?string,per_page:int}
     */
    private function buildPage(array $rows, int $viewerId, int $perPage, string $cursorKey = 'id'): array
    {
        $hasMore = count($rows) > $perPage;
        $slice   = array_slice($rows, 0, $perPage);
        $last    = $slice === [] ? null : end($slice);

        return [
            'data'        => $this->hydratePosts($slice, $viewerId),
            'next_cursor' => $hasMore && is_array($last) ? (string) ($last[$cursorKey] ?? '') : null,
            'per_page'    => $perPage,
        ];
    }

    /**
     * @param list<array<string,mixed>> $posts
     * @return list<array<string,mixed>>
     */
    public function hydratePosts(array $posts, int $viewerId): array
    {
        if ($posts === []) {
            return [];
        }

        $userModel            = model(UserModel::class);
        $reactionModel        = model(ReactionModel::class);
        $commentModel         = model(CommentModel::class);
        $savedPostModel       = $this->db->tableExists('saved_posts') ? model(SavedPostModel::class) : null;
        $commentReactionModel = $this->db->tableExists('comment_reactions') ? model(CommentReactionModel::class) : null;

        $postIds = array_map(static fn (array $post): int => (int) $post['id'], $posts);
        $userIds = array_values(array_unique(array_map(static fn (array $post): int => (int) $post['user_id'], $posts)));

        $authors = [];
        foreach ($userModel->whereIn('id', $userIds)->findAll() as $author) {
            $authors[(int) $author['id']] = $userModel->decorate($author);
        }

        $savedLookup = [];
        if ($viewerId > 0 && $savedPostModel instanceof SavedPostModel) {
            $savedLookup = array_fill_keys($savedPostModel->savedPostIds($viewerId, $postIds), true);
        }

        $reactionRows = $reactionModel->whereIn('post_id', $postIds)->findAll();
        $reactionsByPost = [];
        foreach ($reactionRows as $reaction) {
            $postId = (int) $reaction['post_id'];
            $reactionsByPost[$postId][] = $reaction;
        }

        $commentRows = $commentModel
            ->whereIn('post_id', $postIds)
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        if ($viewerId > 0 && $commentRows !== [] && $this->db->tableExists('hidden_comments')) {
            $hiddenCommentIds = array_fill_keys(model(HiddenCommentModel::class)->hiddenCommentIds($viewerId), true);
            if ($hiddenCommentIds !== []) {
                $commentRows = array_values(array_filter(
                    $commentRows,
                    static function (array $comment) use ($hiddenCommentIds): bool {
                        $commentId = (int) ($comment['id'] ?? 0);
                        $parentId  = isset($comment['parent_id']) ? (int) $comment['parent_id'] : 0;

                        if (isset($hiddenCommentIds[$commentId])) {
                            return false;
                        }

                        if ($parentId > 0 && isset($hiddenCommentIds[$parentId])) {
                            return false;
                        }

                        return true;
                    }
                ));
            }
        }

        $commentCountsByPost = [];
        foreach ($commentRows as $comment) {
            $commentCountsByPost[(int) $comment['post_id']] = ($commentCountsByPost[(int) $comment['post_id']] ?? 0) + 1;
        }

        $commentAuthorIds = array_values(array_unique(array_map(static fn (array $comment): int => (int) $comment['user_id'], $commentRows)));
        if ($commentAuthorIds !== []) {
            foreach ($userModel->whereIn('id', $commentAuthorIds)->findAll() as $commentAuthor) {
                $authors[(int) $commentAuthor['id']] = $userModel->decorate($commentAuthor);
            }
        }

        $commentReactionCounts       = [];
        $commentReactionLookup       = [];
        $commentReactionBreakdowns   = [];
        $commentReactionDisplayTypes = [];
        if ($commentRows !== [] && $commentReactionModel instanceof CommentReactionModel) {
            $commentIds = array_map(static fn (array $comment): int => (int) $comment['id'], $commentRows);
            $commentReactionBreakdowns   = $commentReactionModel->breakdownsByCommentIds($commentIds);
            $commentReactionDisplayTypes = $commentReactionModel->displayTypesByCommentIds($commentIds);

            foreach ($commentReactionBreakdowns as $reactionCommentId => $breakdown) {
                $commentReactionCounts[(int) $reactionCommentId] = array_sum($breakdown);
            }

            if ($viewerId > 0) {
                $commentReactionLookup = $commentReactionModel->reactedCommentTypes($viewerId, $commentIds);
            }
        }



        $commentNodes = [];
        $commentOrder = [];
        foreach ($commentRows as $comment) {
            $commentId = (int) $comment['id'];
            $author    = $authors[(int) $comment['user_id']] ?? null;
            $parentId  = isset($comment['parent_id']) && (int) $comment['parent_id'] > 0
                ? (int) $comment['parent_id']
                : null;

            $commentNodes[$commentId] = [
                'id'                    => $commentId,
                'post_id'               => (int) $comment['post_id'],
                'parent_id'             => $parentId,
                'content'               => $comment['content'],
                'author'                => $author ? [
                    'id'                  => (int) $author['id'],
                    'username'            => $author['username'],
                    'full_name'           => $author['full_name'],
                    'profile_picture_url' => $author['profile_picture_url'],
                ] : null,
                'created_at'            => $comment['created_at'],
                'created_at_human'      => compact_comment_time($comment['created_at'] ?? null),
                'reactions_count'       => (int) ($commentReactionCounts[$commentId] ?? 0),
                'reactions_breakdown'   => $commentReactionBreakdowns[$commentId] ?? [],
                'reaction_display_type' => $commentReactionDisplayTypes[$commentId] ?? null,

                'current_user_reaction' => $commentReactionLookup[$commentId] ?? null,

                'can_reply'             => $viewerId > 0 && $parentId === null,
                'can_edit'              => (int) $comment['user_id'] === $viewerId,
                'can_delete'            => (int) $comment['user_id'] === $viewerId,
                'can_hide'              => $viewerId > 0 && (int) $comment['user_id'] !== $viewerId,
                'replies'               => [],
            ];
            $commentOrder[] = $commentId;
        }

        foreach ($commentOrder as $commentId) {
            $parentId = $commentNodes[$commentId]['parent_id'];
            if ($parentId === null) {
                continue;
            }

            if (! isset($commentNodes[$parentId])) {
                $commentNodes[$commentId]['parent_id'] = null;
                $commentNodes[$commentId]['can_reply'] = $viewerId > 0;
                continue;
            }

            $commentNodes[$parentId]['replies'][] = $commentNodes[$commentId];
        }

        $commentsByPost = [];
        foreach ($commentOrder as $commentId) {
            $commentNode = $commentNodes[$commentId];
            if ($commentNode['parent_id'] !== null) {
                continue;
            }

            $commentsByPost[$commentNode['post_id']][] = $commentNode;
        }


        $hydrated = [];
        foreach ($posts as $post) {
            $postId    = (int) $post['id'];
            $author    = $authors[(int) $post['user_id']] ?? null;
            $reactions = $reactionsByPost[$postId] ?? [];
            $comments  = $commentsByPost[$postId] ?? [];

            $breakdown = [];
            $current   = null;
            foreach ($reactions as $reaction) {
                $type = $reaction['type'];
                $breakdown[$type] = ($breakdown[$type] ?? 0) + 1;
                if ((int) $reaction['user_id'] === $viewerId) {
                    $current = $type;
                }
            }

            $mediaPath = $post['photo_path'] ?? null;
            $mediaType = $post['media_type'] ?? ($mediaPath ? 'image' : null);
            $isOwner   = (int) $post['user_id'] === $viewerId;
            $isSaved   = isset($savedLookup[$postId]);

            $hydrated[] = [
                'id'                    => $postId,
                'content'               => $post['content'],
                'photo_path'            => $mediaPath,
                'photo_url'             => $this->mediaUrl($mediaPath),
                'media_path'            => $mediaPath,
                'media_url'             => $this->mediaUrl($mediaPath),
                'media_type'            => $mediaType,
                'author'                => $author ? [
                    'id'                  => (int) $author['id'],
                    'username'            => $author['username'],
                    'full_name'           => $author['full_name'],
                    'profile_picture_url' => $author['profile_picture_url'],
                ] : null,
                'created_at'            => $post['created_at'],
                'created_at_human'      => $this->humanize($post['created_at'] ?? null),
                'reactions_count'       => count($reactions),
                'reactions_breakdown'   => $breakdown,
                'current_user_reaction' => $current,
                'comments_count'        => (int) ($commentCountsByPost[$postId] ?? 0),
                'comments'              => $comments,
                'is_owner'              => $isOwner,
                'is_saved'              => $isSaved,
                'can_edit'              => $isOwner,
                'can_delete'            => $isOwner,
                'can_save'              => ! $isOwner,
                'can_hide'              => ! $isOwner,
            ];
        }

        return $hydrated;
    }

    private function applyHiddenFilter(object $builder, int $viewerId): void
    {
        if ($viewerId < 1 || ! $this->db->tableExists('hidden_posts')) {
            return;
        }

        $hiddenIds = model(HiddenPostModel::class)->hiddenPostIds($viewerId);
        if ($hiddenIds !== []) {
            $builder->whereNotIn('posts.id', $hiddenIds);
        }
    }

    private function mediaUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return base_url('storage/' . ltrim($path, '/'));
    }

    private function humanize(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return Time::parse($value)->humanize();
    }
}
