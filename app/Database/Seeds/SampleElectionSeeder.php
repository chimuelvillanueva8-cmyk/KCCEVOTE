<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SampleElectionSeeder extends Seeder
{
    public function run()
    {
        if (ENVIRONMENT === 'production') {
            throw new \RuntimeException('SampleElectionSeeder may not be run in production.');
        }

        $elections = $this->db->table('elections');
        $existingElection = $elections
            ->where('title', 'Student Council Demo Election (Sample)')
            ->where('academic_year', '2026-2027')
            ->get()
            ->getRowArray();

        if ($existingElection !== null) {
            return;
        }

        $electionData = [
            'title' => 'Student Council Demo Election (Sample)',
            'description' => 'Sample election data for reviewing positions, candidates, rankings, and eligibility.',
            'status' => 'pending',
            'start_time' => null,
            'end_time' => null,
        ];
        if ($this->db->fieldExists('academic_year', 'elections')) {
            $electionData['academic_year'] = '2026-2027';
        }
        if ($this->db->fieldExists('access_type', 'elections')) {
            $electionData['access_type'] = 'all';
        }

        $this->db->transStart();
        $this->db->table('elections')->insert($electionData);
        $electionId = (int) $this->db->insertID();

        $positions = [
            ['title' => 'President', 'description' => 'Student Council President', 'votes_required' => 1],
            ['title' => 'Vice President', 'description' => 'Student Council Vice President', 'votes_required' => 1],
            ['title' => 'Secretary', 'description' => 'Student Council Secretary', 'votes_required' => 1],
            ['title' => 'Treasurer', 'description' => 'Student Council Treasurer', 'votes_required' => 1],
            ['title' => 'First Year Representative', 'description' => 'Representative for first-year students', 'votes_required' => 1],
        ];

        $candidateNames = [
            'President' => ['Alex Rivera', 'Jordan Cruz'],
            'Vice President' => ['Morgan Santos', 'Taylor Reyes'],
            'Secretary' => ['Casey Flores', 'Riley Mendoza'],
            'Treasurer' => ['Jamie Garcia', 'Avery Lim'],
            'First Year Representative' => ['Sam Villanueva', 'Drew Navarro'],
        ];

        foreach ($positions as $index => $position) {
            $positionData = $position + [
                'sort_order' => $index + 1,
                'status' => 'active',
            ];
            if ($this->db->fieldExists('election_id', 'positions')) {
                $positionData['election_id'] = $electionId;
            }

            $this->db->table('positions')->insert($positionData);
            $positionId = (int) $this->db->insertID();

            foreach ($candidateNames[$position['title']] as $name) {
                $candidateData = [
                    'full_name' => $name,
                    'position_id' => $positionId,
                    'election_id' => $electionId,
                    'photo' => null,
                    'status' => 'active',
                ];

                if ($position['title'] === 'First Year Representative'
                    && $this->db->fieldExists('eligible_year_levels', 'candidates')) {
                    $candidateData['eligible_year_levels'] = json_encode(['1st Year']);
                }

                $this->db->table('candidates')->insert($candidateData);
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Failed to create the sample election and its positions and candidates.');
        }
    }
}
