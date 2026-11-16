<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureBuiltInAdminExists extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('admin_id_number', 'admins')) {
            $this->forge->addColumn('admins', [
                'admin_id_number' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'after' => 'id',
                ],
            ]);
        }

        if (! array_key_exists('uidx_admins_admin_id_number', $this->db->getIndexData('admins'))) {
            $this->forge->addKey('admin_id_number', false, true, 'uidx_admins_admin_id_number');
            $this->forge->processIndexes('admins');
        }

        if (! $this->db->fieldExists('is_builtin_admin', 'admins')) {
            $this->forge->addColumn('admins', [
                'is_builtin_admin' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'null' => false,
                    'after' => 'admin_id_number',
                ],
            ]);
        }

        if ($this->db->table('admins')
            ->where('is_builtin_admin', 1)
            ->countAllResults() > 0) {
            return;
        }

        $builtInAdmin = $this->db->table('admins')
            ->where('username', 'admin')
            ->orderBy('id', 'ASC')
            ->get(1)
            ->getRowArray();

        if (! $builtInAdmin) {
            $builtInAdmin = $this->db->table('admins')
                ->orderBy('id', 'ASC')
                ->get(1)
                ->getRowArray();
        }

        if ($builtInAdmin) {
            $this->db->table('admins')
                ->where('id', $builtInAdmin['id'])
                ->update(['is_builtin_admin' => 1]);
        }
    }

    public function down()
    {
        // Keep the built-in administrator and its schema to avoid locking out account management.
    }
}
