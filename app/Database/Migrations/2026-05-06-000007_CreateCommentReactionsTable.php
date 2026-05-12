<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCommentReactionsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('comment_reactions')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'comment_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'user_id' => [
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
        $this->forge->addKey('comment_id');
        $this->forge->addKey('user_id');
        $this->forge->addUniqueKey(['comment_id', 'user_id']);
        $this->forge->createTable('comment_reactions');
    }

    public function down()
    {
        if ($this->db->tableExists('comment_reactions')) {
            $this->forge->dropTable('comment_reactions');
        }
    }
}
