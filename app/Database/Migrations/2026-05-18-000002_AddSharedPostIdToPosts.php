<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSharedPostIdToPosts extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('shared_post_id', 'posts')) {
            $this->forge->addColumn('posts', [
                'shared_post_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'media_types',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('shared_post_id', 'posts')) {
            $this->forge->dropColumn('posts', 'shared_post_id');
        }
    }
}
