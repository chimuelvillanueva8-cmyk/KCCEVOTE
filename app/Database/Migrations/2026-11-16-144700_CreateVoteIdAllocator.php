<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVoteIdAllocator extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('votes')) {
            throw new \RuntimeException('Cannot create a vote ID allocator because the votes table does not exist.');
        }

        if (! $this->db->tableExists('vote_id_allocator')) {
            $this->forge->addField([
                'allocator_id' => [
                    'type' => 'TINYINT',
                    'constraint' => 3,
                    'unsigned' => true,
                ],
                'next_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => false,
                ],
            ]);
            $this->forge->addKey('allocator_id', true);
            $this->forge->createTable('vote_id_allocator');
        }

        $maxVoteId = (int) $this->db->table('votes')
            ->selectMax('id', 'max_id')
            ->get()
            ->getRow()
            ->max_id;

        $this->db->table('vote_id_allocator')->ignore(true)->insert([
            'allocator_id' => 1,
            'next_id' => max(1, $maxVoteId + 1),
        ]);
    }

    public function down()
    {
        // Keep the allocator table so ballots remain writable after a migration rollback.
    }
}
