<?php

namespace App\Controllers;

use App\Models\PostModel;
use App\Models\UserModel;
use App\Services\NotificationService;

class PostController extends BaseController
{
    public function show(int $postId)
    {
        $post = model(PostModel::class)->hydratedPost($postId, (int) $this->authUser['id']);

        if (! $post) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->render('posts/show', ['post' => $post]);
    }

    public function store()
    {
        $content    = trim((string) $this->request->getPost('content'));
        $mediaFiles = $this->request->getFileMultiple('media');
        $files      = $mediaFiles !== null ? array_values(array_filter($mediaFiles, static fn ($f) => $f->getError() !== UPLOAD_ERR_NO_FILE)) : [];
        $hasMedia   = $files !== [];

        $rules = [
            'content' => 'permit_empty|string|max_length[1000]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (! $hasMedia && $content === '') {
            return redirect()->back()->withInput()->with('errors', [
                'content' => 'Write something or choose a photo or video to post.',
            ]);
        }

        if ($hasMedia) {
            // Validate each uploaded file manually since getFileMultiple doesn't work with standard validation
            foreach ($files as $file) {
                if (! $file->isValid()) {
                    return redirect()->back()->withInput()->with('errors', [
                        'media' => 'One or more files failed to upload.',
                    ]);
                }
                
                $mimeType = $file->getMimeType();
                $allowedMimes = [
                    'image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                    'video/mp4', 'video/webm', 'video/quicktime'
                ];
                
                if (! in_array($mimeType, $allowedMimes, true)) {
                    return redirect()->back()->withInput()->with('errors', [
                        'media' => 'Invalid file type. Only images (JPG, PNG, GIF, WebP) and videos (MP4, WebM, MOV) are allowed.',
                    ]);
                }
            }
        }

        $mediaPaths      = [];
        $mediaType       = null;
        $mediaTypesArray = [];
        if ($hasMedia) {
            foreach ($files as $file) {
                $fileType = $this->detectMediaType($file->getMimeType());
                if ($fileType === null) {
                    return redirect()->back()->withInput()->with('errors', [
                        'media' => 'Unsupported media type.',
                    ]);
                }
                $mediaTypesArray[] = $fileType;
            }
            $mediaType = $mediaTypesArray[0];

            foreach ($files as $file) {
                $mediaPaths[] = $this->storeMedia($file, (int) $this->authUser['id']);
            }
        }

        model(PostModel::class)->insert([
            'user_id'     => (int) $this->authUser['id'],
            'content'     => $content,
            'photo_path'  => $mediaPaths !== [] ? $mediaPaths[0] : null,
            'photo_paths' => $mediaPaths !== [] ? json_encode($mediaPaths) : null,
            'media_type'  => $mediaType,
            'media_types' => $mediaPaths !== [] ? json_encode($mediaTypesArray) : null,
        ]);

        return redirect()->back()->with('success', 'Post created.');
    }

    public function update(int $postId)
    {
        $post = model(PostModel::class)->find($postId);
        if (! $post) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ((int) $post['user_id'] !== (int) $this->authUser['id']) {
            return $this->jsonOrRedirectError(['error' => 'You may only edit your own posts.']);
        }

        $rules = [
            'content' => 'permit_empty|string|max_length[1000]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return $this->jsonOrRedirectError($this->validator->getErrors());
        }

        $content = trim((string) $this->request->getPost('content'));

        // 1. Get existing media list that user wants to keep
        $keepMedia = $this->request->getPost('keep_media');
        if (! is_array($keepMedia)) {
            $keepMedia = [];
        }

        // 2. Parse original paths and their per-file types, delete orphaned files from disk
        $originalPaths = [];
        $originalTypes = [];
        if (! empty($post['photo_paths'])) {
            $decoded = json_decode($post['photo_paths'], true);
            if (is_array($decoded)) {
                $originalPaths = $decoded;
            }
        } elseif (! empty($post['photo_path'])) {
            $originalPaths = [$post['photo_path']];
        }

        if (! empty($post['media_types'])) {
            $decoded = json_decode($post['media_types'], true);
            if (is_array($decoded)) {
                $originalTypes = $decoded;
            }
        }
        if ($originalTypes === []) {
            $fallback      = $post['media_type'] ?? 'image';
            $originalTypes = array_fill(0, count($originalPaths), $fallback);
        }

        // Map path => type for kept files lookup
        $pathTypeMap = [];
        foreach ($originalPaths as $i => $path) {
            $pathTypeMap[$path] = $originalTypes[$i] ?? 'image';
        }

        foreach ($originalPaths as $origPath) {
            if (! in_array($origPath, $keepMedia, true)) {
                $fullPath = ROOTPATH . 'public/storage/' . ltrim($origPath, '/');
                if (is_file($fullPath)) {
                    unlink($fullPath);
                }
            }
        }

        // 3. Process new file uploads
        $mediaFiles = $this->request->getFileMultiple('media');
        $newFiles = $mediaFiles !== null ? array_values(array_filter($mediaFiles, static fn ($f) => $f->getError() !== UPLOAD_ERR_NO_FILE)) : [];

        if ($newFiles !== []) {
            foreach ($newFiles as $file) {
                if (! $file->isValid()) {
                    return $this->jsonOrRedirectError(['media' => 'One or more files failed to upload.']);
                }

                $mimeType = $file->getMimeType();
                $allowedMimes = [
                    'image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                    'video/mp4', 'video/webm', 'video/quicktime'
                ];

                if (! in_array($mimeType, $allowedMimes, true)) {
                    return $this->jsonOrRedirectError(['media' => 'Invalid file type. Only images (JPG, PNG, GIF, WebP) and videos (MP4, WebM, MOV) are allowed.']);
                }
            }
        }

        $newPaths        = [];
        $newMediaType    = null;
        $newTypesArray   = [];
        if ($newFiles !== []) {
            foreach ($newFiles as $file) {
                $fileType = $this->detectMediaType($file->getMimeType());
                if ($fileType === null) {
                    return $this->jsonOrRedirectError(['media' => 'Unsupported media type.']);
                }
                $newTypesArray[] = $fileType;
            }
            $newMediaType = $newTypesArray[0];

            foreach ($newFiles as $file) {
                $newPaths[] = $this->storeMedia($file, (int) $this->authUser['id']);
            }
        }

        // 4. Merge kept media + types with new uploads
        $keptTypes   = array_map(fn ($p) => $pathTypeMap[$p] ?? 'image', $keepMedia);
        $mergedPaths = array_merge($keepMedia, $newPaths);
        $mergedTypes = array_merge($keptTypes, $newTypesArray);

        if ($content === '' && $mergedPaths === []) {
            return $this->jsonOrRedirectError([
                'content' => 'A post cannot be completely empty.',
            ]);
        }

        // 5. Determine final media type (type of first file for backward compat)
        $finalMediaType = null;
        if ($mergedPaths !== []) {
            $finalMediaType = $mergedTypes[0] ?? ($newMediaType ?? ($post['media_type'] ?? 'image'));
        }

        // 6. Update database record
        model(PostModel::class)->update($postId, [
            'content'     => $content,
            'photo_path'  => $mergedPaths !== [] ? $mergedPaths[0] : null,
            'photo_paths' => $mergedPaths !== [] ? json_encode($mergedPaths) : null,
            'media_type'  => $finalMediaType,
            'media_types' => $mergedPaths !== [] ? json_encode($mergedTypes) : null,
        ]);

        // 7. Re-hydrate the post card view with fresh data and return
        $hydratedPost = model(PostModel::class)->hydratedPost($postId, (int) $this->authUser['id']);
        if (! $hydratedPost) {
            return $this->jsonOrRedirectError(['error' => 'Failed to retrieve updated post data.']);
        }

        if ($this->wantsJson()) {
            return $this->response->setJSON([
                'success' => true,
                'html'    => view('partials/post_card', ['post' => $hydratedPost, 'authUser' => $this->authUser]),
                'message' => 'Post updated.',
            ]);
        }

        return redirect()->back()->with('success', 'Post updated.');
    }

    public function share(int $postId)
    {
        $post = model(PostModel::class)->find($postId);
        if (! $post) {
            return $this->jsonOrRedirectError(['error' => 'Post not found.']);
        }

        // If the target is itself a share, share the original instead
        $targetId = ! empty($post['shared_post_id']) ? (int) $post['shared_post_id'] : $postId;

        $content = trim((string) $this->request->getPost('content'));

        model(PostModel::class)->insert([
            'user_id'        => (int) $this->authUser['id'],
            'content'        => $content,
            'shared_post_id' => $targetId,
        ]);

        $newCount = model(PostModel::class)->where('shared_post_id', $targetId)->countAllResults();

        // Notify the original post author about the share
        $originalPost = $targetId !== $postId ? model(PostModel::class)->find($targetId) : $post;
        if ($originalPost) {
            $actorName = trim((string) ($this->authUser['full_name'] ?? $this->authUser['username'] ?? 'Someone'));
            NotificationService::notify(
                (int) $originalPost['user_id'],
                (int) $this->authUser['id'],
                'PostSharedNotification',
                [
                    'message'        => "{$actorName} shared your post.",
                    'actor_name'     => $actorName,
                    'actor_username' => $this->authUser['username'] ?? '',
                    'post_id'        => $targetId,
                ]
            );
        }

        if ($this->wantsJson()) {
            return $this->response->setJSON([
                'success'      => true,
                'shares_count' => $newCount,
                'message'      => 'Post shared successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Post shared.');
    }

    public function destroy(int $postId)
    {
        $post = model(PostModel::class)->find($postId);
        if (! $post) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ((int) $post['user_id'] !== (int) $this->authUser['id']) {
            return $this->jsonOrRedirectError(['error' => 'You may only delete your own posts.']);
        }

        model(PostModel::class)->delete($postId);

        if ($this->wantsJson()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Post deleted.',
            ]);
        }

        return redirect()->back()->with('success', 'Post deleted.');
    }



    private function detectMediaType(?string $mimeType): ?string
    {
        if ($mimeType === null || $mimeType === '') {
            return null;
        }

        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        return null;
    }

    private function storeMedia(object $media, int $userId): string
    {
        $dir = ROOTPATH . 'public/storage/posts/' . $userId;

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $fileName = $media->getRandomName();
        $media->move($dir, $fileName, true);

        return 'posts/' . $userId . '/' . $fileName;
    }
}
