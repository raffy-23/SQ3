<?php

namespace App\Controllers;

use App\Models\PostModel;

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
        $content  = trim((string) $this->request->getPost('content'));
        $media    = $this->request->getFile('media');
        $hasMedia = $media !== null && $media->getError() !== UPLOAD_ERR_NO_FILE;

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

        if ($hasMedia && ! $this->validateData([], [
            'media' => 'uploaded[media]|mime_in[media,image/jpg,image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $mediaPath = null;
        $mediaType = null;
        if ($hasMedia && $media !== null) {
            $mediaType = $this->detectMediaType($media->getMimeType());
            if ($mediaType === null) {
                return redirect()->back()->withInput()->with('errors', [
                    'media' => 'Unsupported media type.',
                ]);
            }

            $mediaPath = $this->storeMedia($media, (int) $this->authUser['id']);
        }

        model(PostModel::class)->insert([
            'user_id'    => (int) $this->authUser['id'],
            'content'    => $content,
            'photo_path' => $mediaPath,
            'media_type' => $mediaType,
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
        if ($content === '' && empty($post['photo_path'])) {
            return $this->jsonOrRedirectError([
                'content' => 'A text-only post cannot be empty.',
            ]);
        }

        model(PostModel::class)->update($postId, [
            'content' => $content,
        ]);

        if ($this->wantsJson()) {
            return $this->response->setJSON([
                'success' => true,
                'content' => $content,
                'message' => 'Post updated.',
            ]);
        }

        return redirect()->back()->with('success', 'Post updated.');
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

    private function wantsJson(): bool
    {
        return $this->request->isAJAX() || str_contains($this->request->getHeaderLine('Accept'), 'application/json');
    }

    private function jsonOrRedirectError(array $errors)
    {
        if ($this->wantsJson()) {
            return $this->response->setStatusCode(422)->setJSON(['errors' => $errors]);
        }

        return redirect()->back()->withInput()->with('errors', $errors);
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
