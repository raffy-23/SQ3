<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHiddenCommentsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('hidden_comments')) {
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
            'comment_id' => [
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
        $this->forge->addUniqueKey(['user_id', 'comment_id']);
        $this->forge->addKey('comment_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('comment_id', 'comments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('hidden_comments');
    }

    public function down()
    {
        if ($this->db->tableExists('hidden_comments')) {
            $this->forge->dropTable('hidden_comments', true);
        }
    }
}
