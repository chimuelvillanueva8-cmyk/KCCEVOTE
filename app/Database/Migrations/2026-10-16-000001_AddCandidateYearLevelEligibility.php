<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCandidateYearLevelEligibility extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('eligible_year_levels', 'candidates')) {
            $this->forge->addColumn('candidates', [
                'eligible_year_levels' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'status',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('eligible_year_levels', 'candidates')) {
            $this->forge->dropColumn('candidates', 'eligible_year_levels');
        }
    }
}
