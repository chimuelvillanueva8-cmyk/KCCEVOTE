<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddManualOpenElectionOverride extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('manual_open', 'elections')) {
            $this->forge->addColumn('elections', [
                'manual_open' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'null' => false,
                    'after' => 'status',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('manual_open', 'elections')) {
            $this->forge->dropColumn('elections', 'manual_open');
        }
    }
}
