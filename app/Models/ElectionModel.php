<?php

namespace App\Models;

use CodeIgniter\Model;

class ElectionModel extends Model
{
    protected $table      = 'elections';
    protected $primaryKey = 'id';

    protected $allowedFields = ['title', 'description', 'academic_year', 'status', 'manual_open', 'start_time', 'end_time', 'access_type', 'allowed_departments', 'allowed_students'];

    protected $validationRules = [
        'title' => 'required|min_length[3]',
        'description' => 'permit_empty|max_length[2000]',
        'academic_year' => 'permit_empty|max_length[32]',
        'status' => 'required|in_list[pending,open,closed,draft,active,completed,inactive]',
        'start_time' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'end_time' => 'permit_empty|valid_date[Y-m-d H:i:s]',
    ];

    protected $validationMessages = [
        'title' => ['required' => 'Title is required.', 'min_length' => 'Title must be at least 3 characters.'],
        'description' => ['max_length' => 'Description must be 2000 characters or less.'],
        'academic_year' => ['max_length' => 'Academic year must be 32 characters or less.'],
        'status' => ['required' => 'Status is required.', 'in_list' => 'Status must be draft, active, completed, inactive, pending, open, or closed.'],
        'start_time' => ['valid_date' => 'Start time must be a valid datetime.'],
        'end_time' => ['valid_date' => 'End time must be a valid datetime.'],
    ];

    public function hasDuplicateTitleForAcademicYear(string $title, ?string $academicYear, ?int $excludeId = null): bool
    {
        $title = strtolower(trim($title));
        $academicYear = strtolower(trim((string) $academicYear));
        if ($title === '') {
            return false;
        }

        $builder = $this->db->table($this->table)
            ->where('LOWER(TRIM(title)) = ' . $this->db->escape($title), null, false)
            ->where("LOWER(TRIM(COALESCE(academic_year, ''))) = " . $this->db->escape($academicYear), null, false);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    public function getActiveElection()
    {
        $this->syncScheduledStatuses();

        return $this->where('status', 'open')
                    ->orderBy('id', 'DESC')
                    ->first();
    }

    public function getOpenElections()
    {
        $this->syncScheduledStatuses();

        return $this->where('status', 'open')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function syncScheduledStatuses(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table($this->table)
            ->whereIn('status', ['pending', 'open'])
            ->where('manual_open', 0)
            ->where('end_time IS NOT NULL', null, false)
            ->where('end_time <=', $now)
            ->update(['status' => 'closed']);

        $this->db->table($this->table)
            ->where('status', 'pending')
            ->where('start_time IS NOT NULL', null, false)
            ->where('start_time <=', $now)
            ->groupStart()
                ->where('end_time IS NULL', null, false)
                ->orWhere('end_time >', $now)
            ->groupEnd()
            ->update(['status' => 'open']);
    }

    public function getOverviewSummary(): array
    {
        $this->syncScheduledStatuses();

        $voterCounts = [];
        foreach ($this->db->table('votes')
            ->select('election_id, COUNT(DISTINCT student_id) AS voters', false)
            ->groupBy('election_id')
            ->get()
            ->getResultArray() as $row) {
            $voterCounts[(int) $row['election_id']] = (int) $row['voters'];
        }

        $overview = [];
        foreach ($this->db->table($this->table)
            ->orderBy('start_time', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray() as $election) {
            $electionId = (int) $election['id'];
            $voters = $voterCounts[$electionId] ?? 0;
            $eligibleVoters = $this->countEligibleVoters($election);
            $overview[] = [
                'id' => $electionId,
                'title' => (string) $election['title'],
                'status' => (string) $election['status'],
                'voters' => $voters,
                'eligible_voters' => $eligibleVoters,
                'turnout_rate' => $eligibleVoters > 0
                    ? round($voters / $eligibleVoters * 100, 2)
                    : 0,
            ];
        }

        return $overview;
    }

    public function getCandidatesWithDetails($electionId = null, bool $includeInactive = true)
    {
        $db = \Config\Database::connect();
        $hasPositionElectionId = $db->fieldExists('election_id', 'positions');
        $builder = $db->table('candidates c');
        $builder->select('c.*, p.name as party_name, pos.title as position_title, e.title as election_title, e.status as election_status, pos.sort_order, pos.votes_required');
        $builder->join('positions pos', 'pos.id = c.position_id', 'left');
        $partyElectionCondition = $hasPositionElectionId
            ? 'p.id = c.party_id AND (p.election_id = c.election_id OR p.election_id IS NULL OR (c.election_id IS NULL AND p.election_id = pos.election_id))'
            : 'p.id = c.party_id AND (p.election_id = c.election_id OR p.election_id IS NULL)';
        $builder->join('parties p', $partyElectionCondition, 'left');
        $electionJoin = $hasPositionElectionId
            ? 'e.id = COALESCE(c.election_id, pos.election_id)'
            : 'e.id = c.election_id';
        $builder->join('elections e', $electionJoin, 'left');

        if ($electionId !== null) {
            $builder->groupStart()
                ->where('c.election_id', $electionId);
            if ($hasPositionElectionId) {
                $builder->orGroupStart()
                    ->where('c.election_id', null)
                    ->where('pos.election_id', $electionId)
                ->groupEnd();
            }
            $builder->groupEnd();
        }
        if (! $includeInactive) {
            $builder->where('c.status', 'active');
        }

        $builder->orderBy('pos.sort_order', 'ASC');
        $builder->orderBy('c.id', 'ASC');
        return $builder->get()->getResultArray();
    }

    public function canStudentAccess($electionId, $studentDepartment = null, $studentId = null)
    {
        $election = $this->find($electionId);
        
        if (!$election) {
            return false;
        }

        // If access type is 'all', everyone can access
        if ($election['access_type'] === 'all') {
            return true;
        }

        // If access type is 'specific_departments', check department
        if ($election['access_type'] === 'specific_departments') {
            if ($studentDepartment === null) {
                return false;
            }
            $allowed = json_decode($election['allowed_departments'], true) ?? [];
            return in_array($studentDepartment, $allowed, true);
        }

        // If access type is 'specific_students', check student ID
        if ($election['access_type'] === 'specific_students') {
            if ($studentId === null) {
                return false;
            }
            $allowed = json_decode($election['allowed_students'], true) ?? [];
            return in_array($studentId, $allowed, true);
        }

        return false;
    }

    public function getAccessibleElections($studentDepartment = null, $studentId = null)
    {
        $this->syncScheduledStatuses();
        $elections = $this->where('status', 'open')->findAll();
        
        $accessible = [];
        foreach ($elections as $election) {
            if ($this->canStudentAccess($election['id'], $studentDepartment, $studentId)) {
                $accessible[] = $election;
            }
        }

        return $accessible;
    }

    public function countEligibleVoters(array $election): int
    {
        $electionId = (int) ($election['id'] ?? 0);
        $votedStudents = 'students.id IN (SELECT v.student_id FROM votes v WHERE v.election_id = '
            . $this->db->escape($electionId) . ')';
        $builder = $this->db->table('students')
            ->groupStart()
                ->where('students.is_active', 1)
                ->orWhere('students.status', 'Active')
                ->orWhere($votedStudents, null, false)
            ->groupEnd();
        $accessType = $election['access_type'] ?? 'all';

        if ($accessType === 'specific_students') {
            $allowedStudents = json_decode($election['allowed_students'] ?? '', true) ?: [];
            if ($allowedStudents === []) {
                return 0;
            }
            $builder->whereIn('students.id', $allowedStudents);
        } elseif ($accessType === 'specific_departments') {
            $departments = json_decode($election['allowed_departments'] ?? '', true) ?: [];
            if ($departments === []) {
                return 0;
            }
            $builder->join('departments', 'departments.id = students.department_id', 'left')
                ->groupStart()
                    ->whereIn('students.department', $departments)
                    ->orWhereIn('departments.department_name', $departments)
                ->groupEnd();
        } elseif ($accessType !== 'all') {
            return 0;
        }

        return (int) $builder->countAllResults();
    }
}
