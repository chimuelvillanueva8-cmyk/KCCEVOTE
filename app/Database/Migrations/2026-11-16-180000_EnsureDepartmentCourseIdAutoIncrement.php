<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureDepartmentCourseIdAutoIncrement extends Migration
{
    public function up()
    {
        foreach (['departments', 'courses'] as $table) {
            $this->ensureIdAutoIncrement($table);
        }
    }

    private function ensureIdAutoIncrement(string $table): void
    {
        if (! $this->db->tableExists($table)) {
            throw new \RuntimeException('Cannot repair ID allocation because the ' . $table . ' table does not exist.');
        }

        $idColumn = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE 'id'")->getRowArray();
        if (! $idColumn) {
            throw new \RuntimeException('Cannot repair ID allocation because ' . $table . '.id does not exist.');
        }

        $extra = strtolower((string) ($idColumn['Extra'] ?? $idColumn['extra'] ?? ''));
        $columnType = strtolower((string) ($idColumn['Type'] ?? $idColumn['type'] ?? ''));
        if (! preg_match('/\Aint(?:\(\d+\))? unsigned\z/', $columnType)) {
            throw new \RuntimeException('Unexpected ' . $table . '.id type; refusing to alter the column automatically.');
        }

        if (! str_contains($extra, 'auto_increment')) {
            $this->db->query(
                'ALTER TABLE `' . $table . '` MODIFY COLUMN `id` ' . $columnType . ' NOT NULL AUTO_INCREMENT'
            );
        }

        $maxId = (int) $this->db->table($table)
            ->selectMax('id', 'max_id')
            ->get()
            ->getRow()
            ->max_id;

        $this->db->query('ALTER TABLE `' . $table . '` AUTO_INCREMENT = ' . max(1, $maxId + 1));
    }

    public function down()
    {
        // Keep the repaired counters beyond existing IDs to prevent primary-key collisions.
    }
}
