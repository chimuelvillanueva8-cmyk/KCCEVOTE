<?php

namespace App\Controllers;

use App\Models\StudentModel;
use App\Models\VoteModel;
use App\Models\CandidateModel;
use App\Models\ElectionModel;

class ApiController extends BaseController
{
    protected $studentModel;
    protected $voteModel;
    protected $candidateModel;
    protected $electionModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        if (session()->get('role') !== 'admin') {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $this->studentModel = new StudentModel();
        $this->voteModel = new VoteModel();
        $this->candidateModel = new CandidateModel();
        $this->electionModel = new ElectionModel();
    }

    public function getStats()
    {
        if (strtoupper($this->request->getMethod()) !== 'GET') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Method not allowed']);
        }

        $electionsOverview = $this->electionModel->getOverviewSummary();
        $studentCount = $this->studentModel->countAllResults();
        $voteCount = $this->voteModel->countAllResults();

        $requestedElectionId = filter_var($this->request->getGet('election_id'), FILTER_VALIDATE_INT);
        $election = $requestedElectionId
            ? $this->electionModel->find($requestedElectionId)
            : $this->electionModel->where('status', 'open')
                ->orderBy('start_time', 'DESC')
                ->orderBy('id', 'DESC')
                ->first();

        if (! $election && ! $requestedElectionId) {
            $election = $this->electionModel->orderBy('id', 'DESC')->first();
        }

        $electionStatus = $election ? strtoupper($election['status']) : 'NONE';
        $candidateCount = $election
            ? count(array_unique(array_column($this->candidateModel->getByElection((int) $election['id'], true), 'id')))
            : 0;
        $voterCount = $election
            ? (int) \Config\Database::connect()->table('votes')
                ->select('student_id')
                ->where('election_id', $election['id'])
                ->distinct()
                ->countAllResults()
            : 0;
        $eligibleVoterCount = $election ? $this->countEligibleVoters($election) : 0;
        $voteProgress = $eligibleVoterCount ? min(100, (int) round($voterCount / $eligibleVoterCount * 100)) : 0;
        $candidateResults = $election
            ? array_map(static fn (array $candidate): array => [
                'candidate_id' => (int) $candidate['candidate_id'],
                'vote_count' => (int) $candidate['vote_count'],
            ], $this->voteModel->getResults((int) $election['id']))
            : [];

        $isBuiltInAdmin = (bool) session()->get('is_builtin_admin');
        $activities = $isBuiltInAdmin ? $this->buildRecentActivity() : $this->buildStudentActivity();
        $systemStatus = $isBuiltInAdmin
            ? $this->buildSystemStatus($electionStatus)
            : $this->buildGeneralSystemStatus();

        return $this->response->setJSON([
            'students' => $studentCount,
            'votes' => $voteCount,
            'candidates' => $candidateCount,
            'election_status' => $electionStatus,
            'election_title' => $election['title'] ?? null,
            'voters' => $voterCount,
            'eligible_voters' => $eligibleVoterCount,
            'vote_progress' => $voteProgress,
            'turnout_rate' => $eligibleVoterCount
                ? round($voterCount / $eligibleVoterCount * 100, 2)
                : 0,
            'candidate_results' => $candidateResults,
            'elections_overview' => $electionsOverview,
            'activities' => $activities,
            'system_status' => $systemStatus,
        ]);
    }

    private function countEligibleVoters(array $election): int
    {
        return $this->electionModel->countEligibleVoters($election);
    }

    private function buildRecentActivity()
    {
        $db = \Config\Database::connect();

        $voteEvents = $db->table('votes v')
            ->select("v.created_at as created_at, CONCAT(s.full_name, ' voted for ', c.full_name) as description")
            ->join('students s', 's.id = v.student_id')
            ->join('candidates c', 'c.id = v.candidate_id')
            ->orderBy('v.created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $studentEvents = $db->table('students')
            ->select("created_at as created_at, CONCAT(full_name, ' joined the system') as description")
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $electionEvents = $db->table('elections')
            ->select("created_at as created_at, CONCAT('Election ', title, ' is ', UPPER(status)) as description")
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $activities = array_merge($voteEvents, $studentEvents, $electionEvents);

        usort($activities, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        return array_slice($activities, 0, 5);
    }

    private function buildStudentActivity(): array
    {
        return \Config\Database::connect()->table('students')
            ->select("created_at as created_at, CONCAT(full_name, ' joined the system') as description")
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
    }

    private function buildGeneralSystemStatus(): array
    {
        $status = [
            'database' => ['label' => 'Database', 'value' => 'OK', 'detail' => 'Connected'],
            'face_recognition' => [
                'label' => 'Face Registration',
                'value' => (string) \Config\Database::connect()->table('students')->where('face_registered', 1)->countAllResults(),
                'detail' => 'Students with a registered face',
            ],
            'backup' => ['label' => 'Backup', 'value' => 'UNKNOWN', 'detail' => $this->getBackupTimestamp()],
        ];

        try {
            \Config\Database::connect()->simpleQuery('SELECT 1');
        } catch (\Throwable $e) {
            $status['database'] = ['label' => 'Database', 'value' => 'DOWN', 'detail' => 'Connection failed'];
        }

        return $status;
    }

    private function buildSystemStatus(string $electionStatus)
    {
        $status = [
            'database' => ['label' => 'Database', 'value' => 'OK', 'detail' => 'Connected'],
            'voting_system' => ['label' => 'Voting System', 'value' => $electionStatus === 'OPEN' ? 'OPEN' : 'CLOSED', 'detail' => $electionStatus === 'OPEN' ? 'Election open' : 'No active election'],
            'face_recognition' => [
                'label' => 'Face Registration',
                'value' => (string) \Config\Database::connect()->table('students')->where('face_registered', 1)->countAllResults(),
                'detail' => 'Students with a registered face',
            ],
            'backup' => ['label' => 'Backup', 'value' => 'UNKNOWN', 'detail' => $this->getBackupTimestamp()],
        ];

        try {
            $db = \Config\Database::connect();
            $db->simpleQuery('SELECT 1');
        } catch (\Throwable $e) {
            $status['database'] = ['label' => 'Database', 'value' => 'DOWN', 'detail' => 'Connection failed'];
        }

        return $status;
    }

    private function getBackupTimestamp()
    {
        $backupPath = WRITEPATH . 'backup';

        if (! is_dir($backupPath)) {
            return 'No backup directory';
        }

        $files = glob($backupPath . DIRECTORY_SEPARATOR . '*');
        if (empty($files)) {
            return 'No backup files';
        }

        $latest = array_reduce($files, function ($carry, $item) {
            if (! is_file($item)) {
                return $carry;
            }
            return $carry === null || filemtime($item) > filemtime($carry) ? $item : $carry;
        });

        return $latest ? date('M d, Y h:i:s A', filemtime($latest)) : 'Unavailable';
    }
}
