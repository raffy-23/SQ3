<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMediaTypesToPosts extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('media_types', 'posts')) {
            $this->forge->addColumn('posts', [
                'media_types' => [
                    'type'  => 'TEXT',
                    'null'  => true,
                    'after' => 'photo_paths',
                ],
            ]);
        }

        // Backfill existing multi-media posts: repeat the single media_type for each file in photo_paths
        $posts = $this->db->query(
            "SELECT id, media_type, photo_paths FROM posts WHERE photo_paths IS NOT NULL AND photo_paths <> '' AND media_types IS NULL"
        )->getResultArray();

        foreach ($posts as $post) {
            $paths = json_decode($post['photo_paths'], true);
            if (! is_array($paths) || $paths === []) {
                continue;
            }
            $type  = $post['media_type'] ?? 'image';
            $types = json_encode(array_fill(0, count($paths), $type));
            $this->db->query(
                "UPDATE posts SET media_types = ? WHERE id = ?",
                [$types, $post['id']]
            );
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('media_types', 'posts')) {
            $this->forge->dropColumn('posts', 'media_types');
        }
    }
}
