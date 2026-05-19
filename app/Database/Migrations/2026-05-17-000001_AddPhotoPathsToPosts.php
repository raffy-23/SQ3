<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoPathsToPosts extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('photo_paths', 'posts')) {
            return;
        }

        $this->forge->addColumn('posts', [
            'photo_paths' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'photo_path',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->fieldExists('photo_paths', 'posts')) {
            $this->forge->dropColumn('posts', 'photo_paths');
        }
    }
}
