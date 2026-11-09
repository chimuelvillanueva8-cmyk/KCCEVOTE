<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogsAndElectionTieWinners extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('audit_logs')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'actor_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'system',
                ],
                'actor_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                ],
                'actor_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'default' => 'System',
                ],
                'action' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'description' => [
                    'type' => 'TEXT',
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['action', 'created_at']);
            $this->forge->createTable('audit_logs');
        }

        if (! $this->db->tableExists('election_tie_winners')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'election_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'position_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'candidate_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'selected_by' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                ],
                'selected_at' => [
                    'type' => 'DATETIME',
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['election_id', 'position_id']);
            $this->forge->createTable('election_tie_winners');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('election_tie_winners')) {
            $this->forge->dropTable('election_tie_winners');
        }
        if ($this->db->tableExists('audit_logs')) {
            $this->forge->dropTable('audit_logs');
        }
    }
}
