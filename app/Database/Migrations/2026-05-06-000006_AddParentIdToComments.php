<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddParentIdToComments extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('parent_id', 'comments')) {
            $this->forge->addColumn('comments', [
                'parent_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'post_id',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('parent_id', 'comments')) {
            $this->forge->dropColumn('comments', 'parent_id');
        }
    }
}
