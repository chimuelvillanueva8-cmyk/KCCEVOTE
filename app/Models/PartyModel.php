<?php

namespace App\Models;

use CodeIgniter\Model;

class PartyModel extends Model
{
    protected $table      = 'parties';
    protected $primaryKey = 'id';

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'name',
        'election_id',
        'acronym',
        'description',
        'logo',
        'status',
    ];

    protected $validationRules = [
        'name'        => 'required',
        'election_id' => 'permit_empty|is_natural_no_zero',
        'acronym'     => 'permit_empty|max_length[32]',
        'description' => 'permit_empty',
        'logo'        => 'permit_empty|max_length[255]',
        'status'      => 'permit_empty|in_list[active,inactive]',
    ];

    protected $validationMessages = [
        'name' => ['required' => 'A party name is required.'],
        'acronym' => ['max_length' => 'Acronym must be 32 characters or less.'],
        'status' => ['in_list' => 'Status must be active or inactive.'],
    ];

    public function getPartiesWithElections()
    {
        $db = \Config\Database::connect();
        $sql = "SELECT p.*, e.title as election_title
                FROM parties p
                LEFT JOIN elections e ON e.id = p.election_id
                ORDER BY p.name ASC";

        $query = $db->query($sql);
        return $query->getResultArray();
    }

    public function getByElection(int $electionId, bool $includeInactive = false)
    {
        $db = \Config\Database::connect();
        $hasPositionElectionId = $db->fieldExists('election_id', 'positions');
        $candidateElectionCondition = $hasPositionElectionId
            ? '(c.election_id = ' . $db->escape($electionId)
                . ' OR (c.election_id IS NULL AND pos.election_id = ' . $db->escape($electionId) . '))'
            : '(c.election_id = ' . $db->escape($electionId) . ')';
        $builder = $db->table('parties p')
            ->select('p.*')
            ->groupStart()
                ->where('p.election_id', $electionId)
                ->orWhere(
                    'EXISTS (
                        SELECT 1
                        FROM candidates c
                        LEFT JOIN positions pos ON pos.id = c.position_id
                        WHERE c.party_id = p.id AND ' . $candidateElectionCondition . '
                    )',
                    null,
                    false
                )
            ->groupEnd();
        if (! $includeInactive) {
            $builder->where('p.status', 'active');
        }
        return $builder->orderBy('p.name', 'ASC')
                       ->get()
                       ->getResultArray();
    }

    public function findForElection(int $partyId, int $electionId)
    {
        foreach ($this->getByElection($electionId, true) as $party) {
            if ((int) $party['id'] === $partyId) {
                return $party;
            }
        }

        return null;
    }

    public function countByElection(int $electionId): int
    {
        return (int) $this->groupStart()
                              ->where('election_id', $electionId)
                              ->orWhere('election_id IS NULL')
                          ->groupEnd()
                          ->countAllResults();
    }

    public function countCandidates(int $partyId, ?int $electionId = null): int
    {
        $builder = $this->db->table('candidates')
                            ->where('party_id', $partyId);
        if ($electionId !== null) {
            $builder->where('election_id', $electionId);
        }
        return (int) $builder->countAllResults();
    }

    public function hasCandidates(int $partyId, ?int $electionId = null): bool
    {
        return $this->countCandidates($partyId, $electionId) > 0;
    }
}
