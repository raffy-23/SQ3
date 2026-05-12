<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoPathToPosts extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('photo_path', 'posts')) {
            $this->forge->addColumn('posts', [
                'photo_path' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'content',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('photo_path', 'posts')) {
            $this->forge->dropColumn('posts', 'photo_path');
        }
    }
}
