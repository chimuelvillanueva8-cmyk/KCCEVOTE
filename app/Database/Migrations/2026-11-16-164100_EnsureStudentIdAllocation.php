<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureStudentIdAllocation extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('students')) {
            throw new \RuntimeException('Cannot initialize student ID allocation because the students table does not exist.');
        }

        $idColumn = $this->db->query("SHOW COLUMNS FROM `students` LIKE 'id'")->getRowArray();
        if (! $idColumn) {
            throw new \RuntimeException('Cannot initialize student ID allocation because students.id does not exist.');
        }

        $extra = strtolower((string) ($idColumn['Extra'] ?? $idColumn['extra'] ?? ''));
        $columnType = strtolower((string) ($idColumn['Type'] ?? $idColumn['type'] ?? ''));
        if (! preg_match('/\Aint(?:\(\d+\))? unsigned\z/', $columnType)) {
            throw new \RuntimeException('Unexpected students.id type; refusing to alter the column automatically.');
        }

        if (! str_contains($extra, 'auto_increment')) {
            $this->db->query(
                'ALTER TABLE `students` MODIFY COLUMN `id` ' . $columnType . ' NOT NULL AUTO_INCREMENT'
            );
        }

        $maxStudentId = (int) $this->db->table('students')
            ->selectMax('id', 'max_id')
            ->get()
            ->getRow()
            ->max_id;
        $nextStudentId = max(1, $maxStudentId + 1);
        $this->db->query('ALTER TABLE `students` AUTO_INCREMENT = ' . $nextStudentId);

        if (! $this->db->tableExists('student_id_allocator')) {
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
            $this->forge->createTable('student_id_allocator');
        }

        if ($this->db->table('student_id_allocator')->ignore(true)->insert([
            'allocator_id' => 1,
            'next_id' => $nextStudentId,
        ]) === false) {
            throw new \RuntimeException('Unable to initialize the student ID allocator.');
        }
    }

    public function down()
    {
        // Keep the allocator beyond existing student IDs to prevent primary-key collisions.
    }
}
