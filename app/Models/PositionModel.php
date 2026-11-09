<?php

namespace App\Models;

use CodeIgniter\Model;

class PositionModel extends Model
{
    protected $table      = 'positions';
    protected $primaryKey = 'id';

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'election_id',
        'title',
        'sort_order',
        'description',
        'votes_required',
        'status',
    ];

    protected $validationRules = [
        'election_id'   => 'permit_empty|is_natural_no_zero',
        'title'         => 'required|min_length[2]',
        'sort_order'    => 'required|is_natural',
        'description'   => 'permit_empty',
        'votes_required' => 'required|is_natural',
        'status'        => 'permit_empty|in_list[active,inactive]',
    ];

    protected $validationMessages = [
        'title' => ['required' => 'A position title is required.', 'min_length' => 'The position title must be at least 2 characters.'],
        'sort_order' => ['required' => 'Sort order is required.', 'is_natural' => 'Sort order must be a valid number.'],
        'votes_required' => ['required' => 'Votes required is required.', 'is_natural' => 'Votes required must be a valid number.'],
        'status' => ['in_list' => 'Status must be active or inactive.'],
    ];

    public function getByElection(int $electionId, bool $includeInactive = false)
    {
        $builder = $this->db->table($this->table);
        if ($this->db->fieldExists('election_id', $this->table)) {
            $builder->groupStart()
                ->where('positions.election_id', $electionId)
                ->orWhere(
                    'EXISTS (SELECT 1 FROM candidates c WHERE c.position_id = positions.id AND c.election_id = ' . $this->db->escape($electionId) . ')',
                    null,
                    false
                )
            ->groupEnd();
        } else {
            $builder->join('candidates c', 'c.position_id = positions.id', 'inner')
                ->where('c.election_id', $electionId)
                ->distinct();
        }
        if (! $includeInactive) {
            $builder->where('status', 'active');
        }
        return $builder->orderBy('sort_order', 'ASC')
                       ->orderBy('id', 'ASC')
                       ->get()
                       ->getResultArray();
    }

    public function findForElection(int $positionId, int $electionId)
    {
        $builder = $this->where('id', $positionId);
        if ($this->db->fieldExists('election_id', $this->table)) {
            $builder->groupStart()
                ->where('election_id', $electionId)
                ->orWhere('election_id', null)
            ->groupEnd();
        } else {
            $builder->join('candidates c', 'c.position_id = positions.id', 'inner')
                ->where('c.election_id', $electionId);
        }
        return $builder->first();
    }

    public function countByElection(int $electionId): int
    {
        if ($this->db->fieldExists('election_id', $this->table)) {
            return (int) $this->db->table('positions')
                ->groupStart()
                ->where('positions.election_id', $electionId)
                ->orWhere(
                    'EXISTS (SELECT 1 FROM candidates c WHERE c.position_id = positions.id AND c.election_id = ' . $this->db->escape($electionId) . ')',
                    null,
                    false
                )
                ->groupEnd()
                ->countAllResults();
        }

        return (int) $this->db->table('positions p')
                ->join('candidates c', 'c.position_id = p.id', 'inner')
                ->where('c.election_id', $electionId)
                ->distinct()
                ->countAllResults();
    }

    public function countCandidates(int $positionId, ?int $electionId = null): int
    {
        $builder = $this->db->table('candidates')
                            ->where('position_id', $positionId);
        if ($electionId !== null) {
            $builder->where('election_id', $electionId);
        }
        return (int) $builder->countAllResults();
    }

    public function hasCandidates(int $positionId, ?int $electionId = null): bool
    {
        return $this->countCandidates($positionId, $electionId) > 0;
    }
}
