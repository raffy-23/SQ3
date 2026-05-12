<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMediaTypeToPosts extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('media_type', 'posts')) {
            $this->forge->addColumn('posts', [
                'media_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'photo_path',
                ],
            ]);
        }

        $this->db->query("UPDATE posts SET media_type = 'image' WHERE photo_path IS NOT NULL AND photo_path <> '' AND (media_type IS NULL OR media_type = '')");
    }

    public function down()
    {
        if ($this->db->fieldExists('media_type', 'posts')) {
            $this->forge->dropColumn('posts', 'media_type');
        }
    }
}
