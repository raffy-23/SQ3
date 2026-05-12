<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTypeToCommentReactions extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('comment_reactions')) {
            return;
        }

        if (! $this->db->fieldExists('type', 'comment_reactions')) {
            $this->forge->addColumn('comment_reactions', [
                'type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => false,
                    'default'    => 'like',
                    'after'      => 'user_id',
                ],
            ]);

            $this->db->table('comment_reactions')->set(['type' => 'like'])->update();
        }
    }

    public function down()
    {
        if ($this->db->tableExists('comment_reactions') && $this->db->fieldExists('type', 'comment_reactions')) {
            $this->forge->dropColumn('comment_reactions', 'type');
        }
    }
}
