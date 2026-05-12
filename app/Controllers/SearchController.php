<?php

namespace App\Controllers;

use App\Models\UserModel;

class SearchController extends BaseController
{
    public function index()
    {
        $query   = trim((string) $this->request->getGet('q'));
        $filter  = (string) ($this->request->getGet('filter') ?: 'all');
        $page    = max(1, (int) $this->request->getGet('page'));
        $perPage = 20;

        $userModel = model(UserModel::class);
        $builder   = $userModel->builder();

        if ($query !== '') {
            $userModel->applySearch($builder, $query);
        }

        // Apply relationship filter based on user selection
        if ($filter === 'followers') {
            // Show only users who follow the current user
            $ids = $userModel->followerIds((int) $this->authUser['id']);
            $builder->whereIn('id', $ids === [] ? [0] : $ids);
        } elseif ($filter === 'following') {
            // Show only users the current user is following
            $ids = $userModel->followingIds((int) $this->authUser['id']);
            $builder->whereIn('id', $ids === [] ? [0] : $ids);
        }
        // When filter is 'all' or empty: intentionally show all users matching the search query
        // No additional filtering is applied - this is the default behavior

        // Exclude the current user from search results (you can't follow yourself!)
        if ($this->authUser !== null) {
            $builder->where('id !=', (int) $this->authUser['id']);
        }

        $builder->orderBy('first_name', 'ASC')->orderBy('last_name', 'ASC');
        $countBuilder = clone $builder;
        $total        = $countBuilder->countAllResults();
        $rows         = $builder->get($perPage, ($page - 1) * $perPage)->getResultArray();
        $users        = $userModel->decorateMany($rows);
        
        // Add is_following and mutual_count fields for each user
        if ($this->authUser !== null) {
            $currentUserId = (int) $this->authUser['id'];
            $currentUserFollowingIds = $userModel->followingIds($currentUserId);
            
            foreach ($users as &$user) {
                $userId = (int) $user['id'];
                $user['is_following'] = $userModel->isFollowing($currentUserId, $userId);
                
                // Calculate mutual followers: intersection of current user's following and this user's followers
                $userFollowerIds = $userModel->followerIds($userId);
                $mutualFollowerIds = array_intersect($currentUserFollowingIds, $userFollowerIds);
                $user['mutual_count'] = count($mutualFollowerIds);
            }
            unset($user); // Break the reference
        } else {
            // If no authenticated user, set default values
            foreach ($users as &$user) {
                $user['is_following'] = false;
                $user['mutual_count'] = 0;
            }
            unset($user); // Break the reference
        }
        
        $lastPage     = max(1, (int) ceil($total / $perPage));

        return $this->render('search/index', [
            'users' => [
                'data'          => $users,
                'current_page'  => $page,
                'last_page'     => $lastPage,
                'next_page_url' => $page < $lastPage ? site_url('search?' . http_build_query(['q' => $query, 'filter' => $filter, 'page' => $page + 1])) : null,
                'prev_page_url' => $page > 1 ? site_url('search?' . http_build_query(['q' => $query, 'filter' => $filter, 'page' => $page - 1])) : null,
            ],
            'query'  => $query,
            'filter' => $filter,
        ]);
    }

    public function live()
    {
        $query = trim((string) $this->request->getGet('q'));
        if (mb_strlen($query) < 2) {
            return $this->response->setJSON([]);
        }

        $userModel = model(UserModel::class);
        $builder   = $userModel->builder()->select('id, username, first_name, last_name, profile_picture_path');
        $userModel->applySearch($builder, $query);

        // Exclude the current user from live search results too
        if ($this->authUser !== null) {
            $builder->where('id !=', (int) $this->authUser['id']);
        }

        $users = array_map(static function (array $user) use ($userModel): array {
            $user = $userModel->decorate($user);

            return [
                'id'                  => (int) $user['id'],
                'username'            => $user['username'],
                'full_name'           => $user['full_name'],
                'profile_picture_url' => $user['profile_picture_url'],
            ];
        }, $builder->get(8)->getResultArray());

        return $this->response->setJSON($users);
    }
}
