<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHiddenPostsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('hidden_posts')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'post_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'post_id']);
        $this->forge->addKey('post_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('post_id', 'posts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('hidden_posts');
    }

    public function down()
    {
        if ($this->db->tableExists('hidden_posts')) {
            $this->forge->dropTable('hidden_posts', true);
        }
    }
}
