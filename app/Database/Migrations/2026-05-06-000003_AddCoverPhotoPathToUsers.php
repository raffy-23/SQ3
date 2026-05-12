<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCoverPhotoPathToUsers extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('cover_photo_path', 'users')) {
            $this->forge->addColumn('users', [
                'cover_photo_path' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'profile_picture_path',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('cover_photo_path', 'users')) {
            $this->forge->dropColumn('users', 'cover_photo_path');
        }
    }
}
