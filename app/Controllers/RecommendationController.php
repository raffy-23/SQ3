<?php

namespace App\Controllers;

use App\Models\UserModel;

class RecommendationController extends BaseController
{
    public function index()
    {
        $userModel     = model(UserModel::class);
        $currentUserId = (int) $this->authUser['id'];
        $followingIds  = $userModel->followingIds($currentUserId);

        if ($followingIds === []) {
            $users = $userModel
                ->where('id !=', $currentUserId)
                ->orderBy('RAND()')
                ->findAll(5);

            return $this->response->setJSON([
                'recommendations' => array_map(fn (array $user): array => [
                    'id'                  => (int) $user['id'],
                    'username'            => $user['username'],
                    'full_name'           => $userModel->fullName($user),
                    'profile_picture_url' => $userModel->profilePictureUrl($user['profile_picture_path'] ?? null),
                    'mutual_count'        => 0,
                ], $users),
            ]);
        }

        $builder = $userModel->builder();
        $builder->select('users.*, COUNT(f2.follower_id) AS mutual_count');
        $builder->join('follows f1', 'f1.following_id = users.id', 'inner');
        $builder->join('follows f2', 'f2.following_id = users.id', 'inner');
        $builder->whereIn('f1.follower_id', $followingIds);
        $builder->whereIn('f2.follower_id', $followingIds);
        $builder->where('users.id !=', $currentUserId);
        $builder->whereNotIn('users.id', $followingIds);
        $builder->groupBy('users.id');
        $builder->orderBy('mutual_count', 'DESC');
        $builder->limit(5);

        $rows = $builder->get()->getResultArray();
        if ($rows === []) {
            $rows = $userModel
                ->where('id !=', $currentUserId)
                ->whereNotIn('id', $followingIds)
                ->orderBy('RAND()')
                ->findAll(5);
        }

        return $this->response->setJSON([
            'recommendations' => array_map(function (array $user) use ($userModel): array {
                $decorated = $userModel->decorate($user);

                return [
                    'id'                  => (int) $decorated['id'],
                    'username'            => $decorated['username'],
                    'full_name'           => $decorated['full_name'],
                    'profile_picture_url' => $decorated['profile_picture_url'],
                    'mutual_count'        => (int) ($user['mutual_count'] ?? 0),
                ];
            }, $rows),
        ]);
    }
}
