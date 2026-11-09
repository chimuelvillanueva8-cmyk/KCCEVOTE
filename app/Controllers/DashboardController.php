<?php

namespace App\Controllers;

use App\Models\StudentModel;
use App\Models\VoteModel;
use App\Models\CandidateModel;
use App\Models\ElectionModel;

class DashboardController extends BaseController
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
            header('Location: ' . base_url('/login?action=admin'));
            exit;
        }

        $this->studentModel = new StudentModel();
        $this->voteModel = new VoteModel();
        $this->candidateModel = new CandidateModel();
        $this->electionModel = new ElectionModel();
    }

    public function index()
    {
        $isBuiltInAdmin = (bool) session()->get('is_builtin_admin');
        $studentCount = $this->studentModel->countAllResults();

        $voteCount = 0;
        $candidateCount = 0;
        $electionStatus = 'NONE';
        $electionTitle = null;
        $voterCount = 0;
        $eligibleVoterCount = 0;
        $progress = 0;
        $electionsOverview = $this->electionModel->getOverviewSummary();

        $voteCount = $this->voteModel->countAllResults();

        $election = $this->electionModel->where('status', 'open')
            ->orderBy('start_time', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();

        if (! $election) {
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
        $progress = $eligibleVoterCount ? min(100, (int) round($voterCount / $eligibleVoterCount * 100)) : 0;
        $electionTitle = $election['title'] ?? null;

        $data = [
            'student_count' => $studentCount,
            'vote_count' => $voteCount,
            'candidate_count' => $candidateCount,
            'election_status' => $electionStatus,
            'election_title' => $electionTitle,
            'voter_count' => $voterCount,
            'eligible_voter_count' => $eligibleVoterCount,
            'vote_progress' => $progress,
            'elections_overview' => $electionsOverview,
            'is_builtin_admin' => $isBuiltInAdmin,
            'admin_name' => session()->get('admin_name')
        ];

        return view('admin/dashboard', $data);
    }

    private function countEligibleVoters(array $election): int
    {
        return $this->electionModel->countEligibleVoters($election);
    }
}
