<?php

namespace App\Controllers;

use App\Models\AuditLogModel;

class AuditLogs extends BaseController
{
    public function index()
    {
        $filter = (string) ($this->request->getGet('filter') ?? 'all');
        if (! in_array($filter, ['all', 'logins', 'voting', 'logouts'], true)) {
            $filter = 'all';
        }

        $builder = (new AuditLogModel())->orderBy('created_at', 'DESC')->orderBy('id', 'DESC');
        if ($filter === 'logins') {
            $builder->whereIn('action', ['ADMIN LOGIN', 'STUDENT LOGIN']);
        } elseif ($filter === 'voting') {
            $builder->where('action', 'VOTING');
        } elseif ($filter === 'logouts') {
            $builder->whereIn('action', ['ADMIN LOGOUT', 'STUDENT LOGOUT']);
        }

        return view('admin/audit_logs', [
            'logs' => $builder->findAll(500),
            'filter' => $filter,
        ]);
    }
}
