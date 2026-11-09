<?php

namespace App\Controllers;

class PublicStatsController extends BaseController
{
    public function getStats()
    {
        $db = \Config\Database::connect();
        $election = $db->table('elections')
            ->where('status', 'open')
            ->orderBy('start_time', 'DESC')
            ->get(1)
            ->getRowArray();

        if (! $election) {
            $election = $db->table('elections')
                ->orderBy('id', 'DESC')
                ->get(1)
                ->getRowArray();
        }

        return $this->response->setJSON([
            'students' => (int) $db->table('students')->countAllResults(),
            'votes' => (int) $db->table('votes')->countAllResults(),
            'candidates' => (int) $db->table('candidates')->countAllResults(),
            'election_status' => strtoupper((string) ($election['status'] ?? 'NONE')),
        ]);
    }
}
