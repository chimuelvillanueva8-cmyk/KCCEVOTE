<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RepairVotesAutoIncrement extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('votes')) {
            throw new \RuntimeException('Cannot repair vote ID allocation because the votes table does not exist.');
        }

        $maxVoteId = (int) $this->db->table('votes')
            ->selectMax('id', 'max_id')
            ->get()
            ->getRow()
            ->max_id;

        $nextVoteId = max(1, $maxVoteId + 1);
        $this->db->query('ALTER TABLE votes AUTO_INCREMENT = ' . $nextVoteId);
    }

    public function down()
    {
        // Keep the allocator beyond existing vote IDs to prevent primary-key collisions.
    }
}
