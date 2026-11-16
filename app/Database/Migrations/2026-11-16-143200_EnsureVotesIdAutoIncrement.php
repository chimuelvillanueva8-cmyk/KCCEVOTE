<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureVotesIdAutoIncrement extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('votes')) {
            throw new \RuntimeException('Cannot repair vote ID allocation because the votes table does not exist.');
        }

        $idColumn = $this->db->query("SHOW COLUMNS FROM `votes` LIKE 'id'")->getRowArray();
        if (! $idColumn) {
            throw new \RuntimeException('Cannot repair vote ID allocation because votes.id does not exist.');
        }

        $extra = strtolower((string) ($idColumn['Extra'] ?? $idColumn['extra'] ?? ''));
        if (! str_contains($extra, 'auto_increment')) {
            $columnType = strtolower((string) ($idColumn['Type'] ?? $idColumn['type'] ?? ''));
            if (! preg_match('/\Aint(?:\(\d+\))? unsigned\z/', $columnType)) {
                throw new \RuntimeException('Unexpected votes.id type; refusing to alter the column automatically.');
            }

            $this->db->query(
                'ALTER TABLE `votes` MODIFY COLUMN `id` ' . $columnType . ' NOT NULL AUTO_INCREMENT'
            );
        }

        $maxVoteId = (int) $this->db->table('votes')
            ->selectMax('id', 'max_id')
            ->get()
            ->getRow()
            ->max_id;

        $this->db->query('ALTER TABLE `votes` AUTO_INCREMENT = ' . max(1, $maxVoteId + 1));
    }

    public function down()
    {
        // Keep the allocator beyond existing vote IDs to prevent primary-key collisions.
    }
}
