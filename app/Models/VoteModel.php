<?php

namespace App\Models;

use App\Libraries\VotingRules;
use CodeIgniter\Model;

class VoteModel extends Model
{
    protected $table      = 'votes';
    protected $primaryKey = 'id';

    protected $allowedFields = ['student_id', 'candidate_id', 'position_id', 'election_id', 'created_at'];

    public function hasVoted($student_id, $election_id)
    {
        return $this->where('student_id', $student_id)
                    ->where('election_id', $election_id)
                    ->countAllResults() > 0;
    }

    public function hasCompletedVote($student_id, $election_id)
    {
        $db = \Config\Database::connect();
        $positions = $db->table('positions p')
            ->select('p.id, p.votes_required')
            ->join('candidates c', 'c.position_id = p.id AND c.election_id = ' . $db->escape($election_id), 'inner')
            ->groupBy(['p.id', 'p.votes_required'])
            ->get()
            ->getResultArray();

        if (empty($positions)) {
            return false;
        }

        $votes = $this->select('position_id, COUNT(*) as vote_count')
            ->where('student_id', $student_id)
            ->where('election_id', $election_id)
            ->groupBy('position_id')
            ->findAll();
        $votesByPosition = array_column($votes, 'vote_count', 'position_id');

        foreach ($positions as $position) {
            if (! VotingRules::hasValidSelectionCount(
                (int) ($votesByPosition[$position['id']] ?? 0),
                (int) $position['votes_required']
            )) {
                return false;
            }
        }

        return true;
    }

    public function getResults($election_id)
    {
        $db = \Config\Database::connect();
        $hasPositionElectionId = $db->fieldExists('election_id', 'positions');
        $builder = $db->table('candidates c');
        $builder->select('c.id as candidate_id, c.position_id, c.full_name, c.photo, pos.title as position, COALESCE(COUNT(v.id), 0) as vote_count, pos.sort_order');
        $builder->join('positions pos', 'pos.id = c.position_id');
        $builder->join('votes v', 'v.candidate_id = c.id AND v.election_id = ' . $db->escape($election_id), 'left');
        $builder->groupStart()
            ->where('c.election_id', $election_id);
        if ($hasPositionElectionId) {
            $builder->orGroupStart()
                ->where('c.election_id', null)
                ->where('pos.election_id', $election_id)
            ->groupEnd();
        }
        $builder->groupEnd();
        $builder->groupStart()
            ->where('c.status', 'active')
            ->orWhere('v.id IS NOT NULL', null, false)
        ->groupEnd();
        $builder->groupBy(['c.id', 'c.position_id', 'c.full_name', 'c.photo', 'pos.title', 'pos.sort_order']);
        $builder->orderBy('pos.sort_order', 'ASC');
        $builder->orderBy('vote_count', 'DESC');
        $builder->orderBy('c.id', 'ASC');
        return $builder->get()->getResultArray();
    }

    public function getLeaderboard($electionId): array
    {
        $leaderboard = [];
        foreach ($this->getResults($electionId) as $candidate) {
            $positionId = (int) $candidate['position_id'];
            if (! isset($leaderboard[$positionId])) {
                $leaderboard[$positionId] = [
                    'title' => $candidate['position'],
                    'candidates' => [],
                    'highest_votes' => (int) $candidate['vote_count'],
                    'leader_count' => 0,
                ];
            }

            $candidate['vote_count'] = (int) $candidate['vote_count'];
            $candidates = $leaderboard[$positionId]['candidates'];
            $previous = empty($candidates) ? null : $candidates[count($candidates) - 1];
            $candidate['rank'] = $previous && $previous['vote_count'] === $candidate['vote_count']
                ? $previous['rank']
                : count($candidates) + 1;
            if ($candidate['vote_count'] === $leaderboard[$positionId]['highest_votes']) {
                $leaderboard[$positionId]['leader_count']++;
            }
            $leaderboard[$positionId]['candidates'][] = $candidate;
        }

        return $leaderboard;
    }

    public function getReceipt($student_id, $election_id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('votes v');
        $builder->select('pos.title as position, c.full_name as candidate, COALESCE(p.name, "Independent") as party, v.created_at');
        $builder->join('candidates c', 'c.id = v.candidate_id');
        $builder->join('positions pos', 'pos.id = v.position_id');
        $builder->join('parties p', 'p.id = c.party_id', 'left');
        $builder->where('v.student_id', $student_id);
        $builder->where('v.election_id', $election_id);
        $builder->orderBy('pos.sort_order', 'ASC');
        return $builder->get()->getResultArray();
    }

    public function getVotedStudentsWithDetails($election_id, $date = null)
    {
        $db = \Config\Database::connect();
        $date = $date ?? date('Y-m-d');
        
        $sql = "SELECT DISTINCT 
                    v.student_id, 
                    s.full_name, 
                    s.student_id as student_number, 
                    s.department, 
                    s.course, 
                    s.year_level, 
                    s.section, 
                    s.school_year, 
                    a.date, 
                    a.status, 
                    v.created_at as vote_time,
                    e.title as election_title,
                    e.id as election_id
                FROM votes v
                INNER JOIN students s ON s.id = v.student_id
                LEFT JOIN attendance a ON a.student_id = v.student_id AND a.date = ?
                INNER JOIN elections e ON e.id = v.election_id
                WHERE v.election_id = ?
                ORDER BY s.department ASC, s.course ASC, s.school_year ASC, s.year_level ASC, s.section ASC, s.full_name ASC";
        
        $query = $db->query($sql, [$date, $election_id]);
        return $query->getResultArray();
    }

    public function getVoteTimesByStudent($electionId): array
    {
        $rows = $this->db->table($this->table)
            ->select('student_id, MIN(created_at) as vote_time')
            ->where('election_id', $electionId)
            ->groupBy('student_id')
            ->get()
            ->getResultArray();

        $voteTimes = [];
        foreach ($rows as $row) {
            $voteTimes[(int) $row['student_id']] = $row['vote_time'];
        }

        return $voteTimes;
    }
}
