<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = [
        'username',
        'first_name',
        'last_name',
        'date_of_birth',
        'sex',
        'email',
        'email_verified_at',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'profile_picture_path',
        'cover_photo_path',
        'bio',
        'remember_token',
    ];

    public function findByEmail(string $email): ?array
    {
        $user = $this->where('email', trim(strtolower($email)))->first();

        return $this->decorate($user);
    }

    public function findByUsername(string $username): ?array
    {
        $user = $this->where('username', trim($username))->first();

        return $this->decorate($user);
    }

    public function decorate(?array $user): ?array
    {
        if ($user === null) {
            return null;
        }

        $user['full_name']           = $this->fullName($user);
        $user['name']                = $user['full_name'];
        $user['profile_picture_url'] = $this->profilePictureUrl($user['profile_picture_path'] ?? null);
        $user['cover_photo_url']     = $this->coverPhotoUrl($user['cover_photo_path'] ?? null);
        $user['avatar']              = $user['profile_picture_url'];
        $user['two_factor_enabled']  = ! empty($user['two_factor_secret']) && ! empty($user['two_factor_confirmed_at']);

        return $user;
    }

    /**
     * @param list<array<string, mixed>> $users
     * @return list<array<string, mixed>>
     */
    public function decorateMany(array $users): array
    {
        return array_map(fn (array $user): array => $this->decorate($user) ?? $user, $users);
    }

    /**
     * @return list<int>
     */
    public function followingIds(int $userId): array
    {
        return array_map(
            'intval',
            array_column(
                $this->db->table('follows')
                    ->select('following_id')
                    ->where('follower_id', $userId)
                    ->get()
                    ->getResultArray(),
                'following_id'
            )
        );
    }

    /**
     * @return list<int>
     */
    public function followerIds(int $userId): array
    {
        return array_map(
            'intval',
            array_column(
                $this->db->table('follows')
                    ->select('follower_id')
                    ->where('following_id', $userId)
                    ->get()
                    ->getResultArray(),
                'follower_id'
            )
        );
    }

    public function isFollowing(int $followerId, int $targetId): bool
    {
        return $this->db->table('follows')
            ->where('follower_id', $followerId)
            ->where('following_id', $targetId)
            ->countAllResults() > 0;
    }

    public function fullName(array $user): string
    {
        return trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    }

    public function profilePictureUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return base_url('storage/' . ltrim($path, '/'));
    }

    public function coverPhotoUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return base_url('storage/' . ltrim($path, '/'));
    }

    /**
     * @return array{posts_count:int,followers_count:int,following_count:int,saved_count:int}
     */
    public function counts(int $userId): array
    {
        return [
            'posts_count'     => $this->db->table('posts')->where('user_id', $userId)->countAllResults(),
            'followers_count' => $this->db->table('follows')->where('following_id', $userId)->countAllResults(),
            'following_count' => $this->db->table('follows')->where('follower_id', $userId)->countAllResults(),
            'saved_count'     => model(SavedPostModel::class)->countForUser($userId),
        ];
    }

    public function publicProfile(array $user): array
    {
        $counts = $this->counts((int) $user['id']);

        return [
            'id'                  => (int) $user['id'],
            'username'            => $user['username'],
            'first_name'          => $user['first_name'],
            'last_name'           => $user['last_name'],
            'full_name'           => $this->fullName($user),
            'bio'                 => $user['bio'],
            'profile_picture_url' => $this->profilePictureUrl($user['profile_picture_path'] ?? null),
            'cover_photo_url'     => $this->coverPhotoUrl($user['cover_photo_path'] ?? null),
            'created_at'          => $user['created_at'],
            'posts_count'         => $counts['posts_count'],
            'followers_count'     => $counts['followers_count'],
            'following_count'     => $counts['following_count'],
            'saved_count'         => $counts['saved_count'],
        ];
    }

    public function applySearch(object $builder, string $term): object
    {
        $term = trim($term);

        // Let CI4's like() handle quoting, escaping, and % wildcard placement.
        // Default side='both' produces: column LIKE '%term%'
        return $builder
            ->groupStart()
            ->like('username',     $term)
            ->orLike('first_name', $term)
            ->orLike('last_name',  $term)
            ->orLike('email',      $term)
            ->groupEnd();
    }
}
