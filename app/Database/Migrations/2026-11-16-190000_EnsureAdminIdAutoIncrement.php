<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureAdminIdAutoIncrement extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('admins')) {
            throw new \RuntimeException('Cannot repair admin ID allocation because the admins table does not exist.');
        }

        $idColumn = $this->db->query("SHOW COLUMNS FROM `admins` LIKE 'id'")->getRowArray();
        if (! $idColumn) {
            throw new \RuntimeException('Cannot repair ID allocation because admins.id does not exist.');
        }

        $extra = strtolower((string) ($idColumn['Extra'] ?? $idColumn['extra'] ?? ''));
        $columnType = strtolower((string) ($idColumn['Type'] ?? $idColumn['type'] ?? ''));
        if (! preg_match('/\Aint(?:\(\d+\))? unsigned\z/', $columnType)) {
            throw new \RuntimeException('Unexpected admins.id type; refusing to alter the column automatically.');
        }

        if (! str_contains($extra, 'auto_increment')) {
            $this->db->query(
                'ALTER TABLE `admins` MODIFY COLUMN `id` ' . $columnType . ' NOT NULL AUTO_INCREMENT'
            );
        }

        $maxId = (int) $this->db->table('admins')
            ->selectMax('id', 'max_id')
            ->get()
            ->getRow()
            ->max_id;

        $this->db->query('ALTER TABLE `admins` AUTO_INCREMENT = ' . max(1, $maxId + 1));
    }

    public function down()
    {
        // Keep the repaired counters beyond existing IDs to prevent primary-key collisions.
    }
}
