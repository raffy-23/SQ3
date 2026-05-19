<?php

namespace App\Controllers;

use App\Models\UserModel;

class ProfileCoverController extends BaseController
{
    public function store()
    {
        $rules = [
            'cover_photo' => 'uploaded[cover_photo]|max_size[cover_photo,10240]|is_image[cover_photo]|mime_in[cover_photo,image/jpg,image/jpeg,image/png,image/gif,image/webp]',
        ];

        if (! $this->validate($rules)) {
            if ($this->request->hasHeader('X-Requested-With')) {
                return $this->response->setStatusCode(422)->setJSON(['errors' => $this->validator->getErrors()]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file   = $this->request->getFile('cover_photo');
        $userId = (int) $this->authUser['id'];
        $dir    = ROOTPATH . 'public/storage/covers/' . $userId;

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $this->deleteDirectoryFiles($dir);

        $extension = $file->getExtension() ?: 'jpg';
        $fileName  = 'cover.' . $extension;
        $file->move($dir, $fileName, true);

        model(UserModel::class)->update($userId, [
            'cover_photo_path' => 'covers/' . $userId . '/' . $fileName,
        ]);

        if ($this->request->hasHeader('X-Requested-With')) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->back()->with('success', 'Cover photo updated.');
    }

    public function destroy()
    {
        $userId = (int) $this->authUser['id'];
        $dir    = ROOTPATH . 'public/storage/covers/' . $userId;

        if (is_dir($dir)) {
            $this->deleteDirectoryFiles($dir);
            @rmdir($dir);
        }

        model(UserModel::class)->update($userId, ['cover_photo_path' => null]);

        return redirect()->back()->with('success', 'Cover photo removed.');
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
