<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBuiltInAdminPrivileges extends Migration
{
    public function up()
    {
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

        $hasBuiltInAdmin = $this->db->table('admins')
            ->where('is_builtin_admin', 1)
            ->countAllResults() > 0;

        if (! $hasBuiltInAdmin) {
            $builtInAdmin = $this->db->table('admins')
                ->where('username', 'admin')
                ->orderBy('id', 'ASC')
                ->get(1)
                ->getRowArray();

            if ($builtInAdmin) {
                $this->db->table('admins')
                    ->where('id', $builtInAdmin['id'])
                    ->update(['is_builtin_admin' => 1]);
            }
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('is_builtin_admin', 'admins')) {
            $this->forge->dropColumn('admins', 'is_builtin_admin');
        }
    }
}
