<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdminIdNumber extends Migration
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
    }

    public function down()
    {
        if (array_key_exists('uidx_admins_admin_id_number', $this->db->getIndexData('admins'))) {
            $this->forge->dropKey('admins', 'uidx_admins_admin_id_number');
        }

        if ($this->db->fieldExists('admin_id_number', 'admins')) {
            $this->forge->dropColumn('admins', 'admin_id_number');
        }
    }
}
