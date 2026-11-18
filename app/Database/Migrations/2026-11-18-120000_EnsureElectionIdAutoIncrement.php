<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureElectionIdAutoIncrement extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('elections')) {
            throw new \RuntimeException('Cannot repair election ID allocation because the elections table does not exist.');
        }

        $idColumn = $this->db->query("SHOW COLUMNS FROM `elections` LIKE 'id'")->getRowArray();
        if (! $idColumn) {
            throw new \RuntimeException('Cannot repair ID allocation because elections.id does not exist.');
        }

        $extra = strtolower((string) ($idColumn['Extra'] ?? $idColumn['extra'] ?? ''));
        $columnType = strtolower((string) ($idColumn['Type'] ?? $idColumn['type'] ?? ''));
        if (! preg_match('/\Aint(?:\(\d+\))? unsigned\z/', $columnType)) {
            throw new \RuntimeException('Unexpected elections.id type; refusing to alter the column automatically.');
        }

        if (! str_contains($extra, 'auto_increment')) {
            $this->db->query(
                'ALTER TABLE `elections` MODIFY COLUMN `id` ' . $columnType . ' NOT NULL AUTO_INCREMENT'
            );
        }

        $maxId = (int) $this->db->table('elections')
            ->selectMax('id', 'max_id')
            ->get()
            ->getRow()
            ->max_id;

        $this->db->query('ALTER TABLE `elections` AUTO_INCREMENT = ' . max(1, $maxId + 1));
    }

    public function down()
    {
        // Keep the repaired counter beyond existing IDs to prevent primary-key collisions.
    }
}
