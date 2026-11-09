<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'actor_type',
        'actor_id',
        'actor_name',
        'action',
        'description',
        'created_at',
    ];
    protected $useTimestamps = false;

    public function record(string $action, string $description, ?string $createdAt = null): bool
    {
        $session = session();
        $actorType = (string) ($session->get('role') ?? 'system');
        $actorId = $actorType === 'admin'
            ? $session->get('admin_id')
            : ($actorType === 'student' ? $session->get('user_id') : null);
        $actorName = $actorType === 'admin'
            ? (string) ($session->get('admin_name') ?? $session->get('username') ?? 'Administrator')
            : ($actorType === 'student'
                ? (string) ($session->get('full_name') ?? $session->get('student_id') ?? 'Student')
                : 'System');

        $saved = $this->insert([
            'actor_type' => $actorType,
            'actor_id' => $actorId ? (int) $actorId : null,
            'actor_name' => $actorName,
            'action' => $action,
            'description' => $description,
            'created_at' => $createdAt ?? date('Y-m-d H:i:s'),
        ]);

        if ($saved === false) {
            log_message('error', 'Unable to save audit log for action: ' . $action);
            return false;
        }

        return true;
    }
}