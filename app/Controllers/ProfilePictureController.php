<?php

namespace App\Controllers;

use App\Models\UserModel;

class ProfilePictureController extends BaseController
{
    public function store()
    {
        $rules = [
            'profile_picture' => 'uploaded[profile_picture]|max_size[profile_picture,10240]|is_image[profile_picture]|mime_in[profile_picture,image/jpg,image/jpeg,image/png,image/gif,image/webp]',
        ];

        if (! $this->validate($rules)) {
            if ($this->request->hasHeader('X-Requested-With')) {
                return $this->response->setStatusCode(422)->setJSON(['errors' => $this->validator->getErrors()]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file   = $this->request->getFile('profile_picture');
        $userId = (int) $this->authUser['id'];
        $dir    = ROOTPATH . 'public/storage/avatars/' . $userId;

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $this->deleteDirectoryFiles($dir);

        $extension = $file->getExtension() ?: 'jpg';
        $fileName  = 'profile.' . $extension;
        $file->move($dir, $fileName, true);

        model(UserModel::class)->update($userId, [
            'profile_picture_path' => 'avatars/' . $userId . '/' . $fileName,
        ]);

        if ($this->request->hasHeader('X-Requested-With')) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->back()->with('success', 'Profile picture updated.');
    }

    public function destroy()
    {
        $userId = (int) $this->authUser['id'];
        $dir    = ROOTPATH . 'public/storage/avatars/' . $userId;

        if (is_dir($dir)) {
            $this->deleteDirectoryFiles($dir);
            @rmdir($dir);
        }

        model(UserModel::class)->update($userId, ['profile_picture_path' => null]);

        return redirect()->back()->with('success', 'Profile picture removed.');
    }

    private function deleteDirectoryFiles(string $directory): void
    {
        foreach (glob($directory . '/*') ?: [] as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
