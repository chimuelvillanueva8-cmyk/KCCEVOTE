<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDescriptionToElections extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('description', 'elections')) {
            $this->forge->addColumn('elections', [
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'title',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('description', 'elections')) {
            $this->forge->dropColumn('elections', 'description');
        }
    }
}
