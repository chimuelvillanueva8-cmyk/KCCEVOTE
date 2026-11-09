<?php

namespace App\Controllers\Admin;

use App\Models\AnnouncementModel;
use App\Models\AuditLogModel;
use App\Models\CandidateModel;
use App\Models\ElectionModel;
use App\Models\PartyModel;
use App\Models\PositionModel;
use App\Models\SettingModel;
use App\Models\VoteModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Admin extends \App\Controllers\BaseController
{
    private function isBuiltInAdmin(): bool
    {
        $adminId = filter_var(session()->get('admin_id'), FILTER_VALIDATE_INT);
        $db = \Config\Database::connect();

        return $adminId !== false
            && $adminId > 0
            && $db->fieldExists('is_builtin_admin', 'admins')
            && $db->table('admins')
                ->where('id', $adminId)
                ->where('is_builtin_admin', 1)
                ->countAllResults() === 1;
    }

    private function adminManagementDenied()
    {
        return $this->response->setStatusCode(403)->setJSON([
            'success' => false,
            'message' => 'Only the built-in administrator can manage admin accounts.',
        ]);
    }

    private function csrfJson(array $data)
    {
        return $this->response->setJSON($data + [
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ]);
    }

    private function electionStartTimeIsInFuture(?string $startTime): bool
    {
        if (empty($startTime)) {
            return false;
        }

        $timezone = new \DateTimeZone(config('App')->appTimezone);
        $start = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $startTime, $timezone);
        if ($start === false) {
            return false;
        }

        return $start > new \DateTimeImmutable('now', $timezone);
    }

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        if (session()->get('role') !== 'admin') {
            header('Location: ' . base_url('/login?action=admin'));
            exit;
        }
    }

    public function index()
    {
        $db = \Config\Database::connect();
        $electionModel = new ElectionModel();

        $data = [
            'student_count'   => $db->table('students')->countAll(),
            'candidate_count' => $db->table('candidates')->countAll(),
            'vote_count'      => $db->table('votes')->countAll(),
            'election'        => $electionModel->orderBy('id', 'DESC')->first(),
            'admin_name'      => session()->get('admin_name')
        ];

        return view('admin/dashboard', $data);
    }

    public function candidates()
    {
        $electionModel = new ElectionModel();
        $data['candidates'] = $electionModel->getCandidatesWithDetails();
        $data['hasOpenElection'] = $electionModel->where('status', 'open')->countAllResults() > 0;
        return view('admin/candidates', $data);
    }

    public function createCandidate()
    {
        $data = [
            'mode' => 'create',
            'positions' => (new PositionModel())->findAll(),
            'parties' => (new PartyModel())->findAll(),
            'elections' => (new ElectionModel())->findAll(),
            'yearLevels' => $this->getAvailableYearLevels(),
        ];

        return view('admin/candidate_form', $data);
    }

    public function storeCandidate()
    {
        $model = new CandidateModel();
        $data = $this->request->getPost(['full_name', 'student_id', 'party_id', 'position_id', 'election_id', 'status']);
        $redirectTo = $this->request->getPost('redirect_to');

        if ($this->candidateElectionIsOpen($this->candidateElectionId($data))) {
            return redirect()->back()->withInput()->with('error', 'Candidate information cannot be modified during an active election.');
        }
        $eligibilityError = $this->candidateEligibilityError();
        if ($eligibilityError !== null) {
            return redirect()->back()->withInput()->with('error', $eligibilityError);
        }

        if (empty($data['full_name']) && ! empty($data['student_id'])) {
            $studentModel = new \App\Models\StudentModel();
            $student = $studentModel->where('student_id', $data['student_id'])->orWhere('id', $data['student_id'])->first();
            if ($student) {
                $data['full_name'] = $student['full_name'] ?? $student['first_name'] . ' ' . ($student['last_name'] ?? '');
            }
        }

        if (! empty($data['election_id']) && ! empty($data['position_id'])) {
            $position = (new PositionModel())->findForElection((int) $data['position_id'], (int) $data['election_id']);
            if (! $position) {
                return $this->candidateRelationError('Selected position does not belong to the current election.');
            }
        }

        if (! empty($data['election_id']) && ! empty($data['party_id'])) {
            $party = (new PartyModel())->findForElection((int) $data['party_id'], (int) $data['election_id']);
            if (! $party) {
                return $this->candidateRelationError('Selected party does not belong to the current election.');
            }
        }

        if (! empty($data['election_id']) && ! empty($data['student_id'])) {
            if ($model->isDuplicateStudent((int) $data['election_id'], $data['student_id'])) {
                if ($this->request->isAJAX()) {
                    $this->response->setStatusCode(422);
                    return $this->csrfJson([
                        'success' => false,
                        'message' => 'This student is already registered as a candidate for this election.',
                    ]);
                }
                return redirect()->back()->withInput()->with('error', 'This student is already registered as a candidate for this election.');
            }
        }

        $data['party_id'] = empty($data['party_id']) ? null : $data['party_id'];
        $data['election_id'] = empty($data['election_id']) ? null : (int) $data['election_id'];
        $data['position_id'] = empty($data['position_id']) ? null : (int) $data['position_id'];
        $data['status'] = $data['status'] ?? 'active';
        $data['eligible_year_levels'] = $this->candidateEligibilityValue();

        $photoUpload = $this->storeCandidatePhoto();
        if ($photoUpload['error'] !== null) {
            return $this->candidatePhotoErrorResponse($photoUpload['error']);
        }
        if ($photoUpload['photo'] !== null) {
            $data['photo'] = $photoUpload['photo'];
        }

        if (! $model->save($data)) {
            if ($this->request->isAJAX()) {
                $errors = $model->errors();
                return $this->csrfJson([
                    'success' => false,
                    'message' => $errors ? implode(' ', $errors) : 'Unable to create candidate.',
                    'errors' => $errors,
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        (new AuditLogModel())->record('CREATE', 'Created candidate ' . $data['full_name'] . '.');
        if ($this->request->isAJAX()) {
            return $this->csrfJson(['success' => true, 'candidate_id' => $model->getInsertID()]);
        }

        if ($redirectTo) {
            return redirect()->to($redirectTo)->with('success', 'Candidate saved successfully.');
        }
        return redirect()->to('/admin/candidates')->with('success', 'Candidate saved successfully.');
    }

    private function candidatePhotoErrorResponse(string $message)
    {
        if ($this->request->isAJAX()) {
            $this->response->setStatusCode(422);
            return $this->csrfJson(['success' => false, 'message' => $message]);
        }

        return redirect()->back()->withInput()->with('error', $message);
    }

    private function candidateRelationError(string $message)
    {
        if ($this->request->isAJAX()) {
            $this->response->setStatusCode(422);
            return $this->csrfJson(['success' => false, 'message' => $message]);
        }

        return redirect()->back()->withInput()->with('error', $message);
    }

    private function getAvailableYearLevels(): array
    {
        $rows = \Config\Database::connect()->table('students')
            ->distinct()
            ->select('year_level')
            ->where('year_level IS NOT NULL', null, false)
            ->orderBy('year_level', 'ASC')
            ->get()
            ->getResultArray();
        $levels = array_values(array_unique(array_filter(
            array_map(static fn ($row) => trim((string) $row['year_level']), $rows),
            static fn ($level) => $level !== ''
        )));
        sort($levels, SORT_NATURAL | SORT_FLAG_CASE);
        return array_map(static fn ($level) => ['year_level' => $level], $levels);
    }

    private function candidateEligibilityValue(): ?string
    {
        if ($this->request->getPost('eligible_all') === '1') {
            return null;
        }

        $selectedYearLevel = $this->request->getPost('eligible_year_level');
        if (is_string($selectedYearLevel) && $selectedYearLevel !== '__preserve__') {
            $selectedYearLevel = trim($selectedYearLevel);
            return $selectedYearLevel === ''
                ? null
                : json_encode([$selectedYearLevel], JSON_UNESCAPED_UNICODE);
        }

        $levels = array_values(array_unique(array_map('trim', $this->request->getPost('eligible_year_levels') ?? [])));
        return empty($levels) ? null : json_encode($levels, JSON_UNESCAPED_UNICODE);
    }

    private function candidateEligibilityError(): ?string
    {
        if ($this->request->getPost('eligible_all') === '1') {
            return null;
        }
        $selectedYearLevel = $this->request->getPost('eligible_year_level');
        if (is_string($selectedYearLevel) && $selectedYearLevel !== '__preserve__') {
            $selectedYearLevel = trim($selectedYearLevel);
            if ($selectedYearLevel === '') {
                return null;
            }
            return in_array($selectedYearLevel, array_column($this->getAvailableYearLevels(), 'year_level'), true)
                ? null
                : 'The selected year level is invalid.';
        }

        $levels = $this->request->getPost('eligible_year_levels');
        if (! is_array($levels) || empty($levels)) {
            return null;
        }
        $available = array_column($this->getAvailableYearLevels(), 'year_level');
        foreach ($levels as $level) {
            if (! is_string($level) || ! in_array(trim($level), $available, true)) {
                return 'One or more selected year levels are invalid.';
            }
        }
        return null;
    }

    private function candidateElectionId(array $data): ?int
    {
        if (! empty($data['election_id'])) {
            return (int) $data['election_id'];
        }
        if (! empty($data['position_id'])) {
            $position = (new PositionModel())->find((int) $data['position_id']);
            return ! empty($position['election_id']) ? (int) $position['election_id'] : null;
        }
        return null;
    }

    private function candidateElectionIsOpen(?int $electionId): bool
    {
        if ($electionId === null) {
            return false;
        }
        $election = (new ElectionModel())->find($electionId);
        return strtolower((string) ($election['status'] ?? '')) === 'open';
    }

    private function storeCandidatePhoto(): array
    {
        $file = $this->request->getFile('photo');
        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return ['photo' => null, 'error' => null];
        }
        if (! $file->isValid() || $file->hasMoved()) {
            return ['photo' => null, 'error' => 'Candidate photo upload failed.'];
        }
        if (! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return ['photo' => null, 'error' => 'Candidate photos must be JPG, PNG, or WEBP images.'];
        }
        if ($file->getSize() <= 0 || $file->getSize() > 5 * 1024 * 1024) {
            return ['photo' => null, 'error' => 'Candidate photos must be non-empty and 5 MB or smaller.'];
        }

        $uploadPath = FCPATH . 'uploads/candidates';
        if (! is_dir($uploadPath) && ! mkdir($uploadPath, 0755, true) && ! is_dir($uploadPath)) {
            return ['photo' => null, 'error' => 'Unable to prepare candidate photo storage.'];
        }

        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);
        return ['photo' => $newName, 'error' => null];
    }

    public function editCandidate(int $id)
    {
        $model = new CandidateModel();
        $candidate = $model->find($id);
        if (! $candidate) {
            throw new PageNotFoundException('Candidate not found');
        }
        if ($this->candidateElectionIsOpen($this->candidateElectionId($candidate))) {
            return redirect()->to('/admin/candidates')->with('error', 'Candidate information cannot be modified during an active election.');
        }
        $eligibleYearLevels = json_decode($candidate['eligible_year_levels'] ?? '', true);

        $data = [
            'mode' => 'edit',
            'candidate' => $candidate,
            'positions' => (new PositionModel())->findAll(),
            'parties' => (new PartyModel())->findAll(),
            'elections' => (new ElectionModel())->findAll(),
            'yearLevels' => $this->getAvailableYearLevels(),
            'eligibleYearLevels' => is_array($eligibleYearLevels) ? $eligibleYearLevels : [],
            'eligibleAll' => empty($candidate['eligible_year_levels']),
        ];

        return view('admin/candidate_form', $data);
    }

    public function updateCandidate(int $id)
    {
        $model = new CandidateModel();
        $data = $this->request->getPost(['full_name', 'student_id', 'party_id', 'position_id', 'election_id', 'status']);
        $data['id'] = $id;
        $redirectTo = $this->request->getPost('redirect_to');
        $existingCandidate = $model->find($id);
        if (! $existingCandidate) {
            return redirect()->back()->with('error', 'Candidate not found.');
        }
        $currentElectionId = $this->candidateElectionId($existingCandidate);
        $targetElectionId = $this->candidateElectionId($data);
        if ($this->candidateElectionIsOpen($currentElectionId) || $this->candidateElectionIsOpen($targetElectionId)) {
            return redirect()->back()->withInput()->with('error', 'Candidate information cannot be modified during an active election.');
        }
        $eligibilityError = $this->candidateEligibilityError();
        if ($eligibilityError !== null) {
            return redirect()->back()->withInput()->with('error', $eligibilityError);
        }
        if (! empty($data['election_id']) && ! $model->findForElection($id, (int) $data['election_id'])) {
            return $this->candidateRelationError('You can only edit candidates assigned to the current election.');
        }
        $data['status'] = $data['status'] ?? $existingCandidate['status'] ?? 'active';

        if (! empty($data['election_id']) && ! empty($data['position_id'])) {
            $position = (new PositionModel())->findForElection((int) $data['position_id'], (int) $data['election_id']);
            if (! $position) {
                return $this->candidateRelationError('Selected position does not belong to the current election.');
            }
        }

        if (! empty($data['election_id']) && ! empty($data['party_id'])) {
            $party = (new PartyModel())->findForElection((int) $data['party_id'], (int) $data['election_id']);
            if (! $party) {
                return $this->candidateRelationError('Selected party does not belong to the current election.');
            }
        }

        $data['party_id'] = empty($data['party_id']) ? null : $data['party_id'];
        $data['election_id'] = empty($data['election_id']) ? null : (int) $data['election_id'];
        $data['position_id'] = empty($data['position_id']) ? null : (int) $data['position_id'];
        $data['eligible_year_levels'] = $this->candidateEligibilityValue();
        $photoUpload = $this->storeCandidatePhoto();
        if ($photoUpload['error'] !== null) {
            return $this->candidatePhotoErrorResponse($photoUpload['error']);
        }
        if ($photoUpload['photo'] !== null) {
            $data['photo'] = $photoUpload['photo'];
        }

        if (! $model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        (new AuditLogModel())->record('UPDATE', 'Updated candidate ' . $data['full_name'] . '.');
        if ($redirectTo) {
            return redirect()->to($redirectTo)->with('success', 'Candidate updated successfully.');
        }
        return redirect()->to('/admin/candidates')->with('success', 'Candidate updated successfully.');
    }

    public function deleteCandidate(int $id)
    {
        $model = new CandidateModel();
        $redirectTo = $this->request->getPost('redirect_to');
        $candidate = $model->find($id);
        if (! $candidate) {
            return redirect()->back()->with('error', 'Candidate not found.');
        }
        if ($this->candidateElectionIsOpen($this->candidateElectionId($candidate))) {
            return redirect()->back()->with('error', 'Candidate information cannot be modified during an active election.');
        }
        $electionId = $this->request->getPost('election_id');
        if (! empty($electionId) && ! $model->findForElection($id, (int) $electionId)) {
            return redirect()->back()->with('error', 'You can only delete candidates assigned to the current election.');
        }
        if (db_connect()->table('votes')->where('candidate_id', $id)->countAllResults() > 0) {
            return redirect()->back()->with('error', 'This candidate has votes and cannot be deleted.');
        }
        if (! $model->delete($id)) {
            return redirect()->back()->with('error', 'Failed to delete candidate.');
        }

        (new AuditLogModel())->record('DELETE', 'Deleted candidate ' . $candidate['full_name'] . '.');
        if ($redirectTo) {
            return redirect()->to($redirectTo)->with('success', 'Candidate deleted successfully.');
        }
        return redirect()->to('/admin/candidates')->with('success', 'Candidate deleted successfully.');
    }

    public function elections()
    {
        $electionModel = new ElectionModel();
        $data['elections'] = $electionModel->findAll();
        $data['positions'] = (new PositionModel())->findAll();
        $data['parties'] = (new PartyModel())->getPartiesWithElections();
        $data['yearLevels'] = $this->getAvailableYearLevels();
        return view('admin/elections', $data);
    }

    public function createElection()
    {
        $studentModel = new \App\Models\StudentModel();
        return view('admin/election_form', [
            'mode' => 'create', 
            'election' => [],
            'departments' => $studentModel->getDepartments(),
        ]);
    }

    public function editElection(int $id)
    {
        $electionModel = new ElectionModel();
        $positionModel = new PositionModel();
        $partyModel = new PartyModel();
        $candidateModel = new CandidateModel();
        $studentModel = new \App\Models\StudentModel();

        $election = $electionModel->find($id);
        if (! $election) {
            return redirect()->to('/admin/elections')->with('error', 'Election not found.');
        }
        if (in_array(strtolower((string) $election['status']), ['open', 'active'], true)) {
            return redirect()->to('/admin/elections')->with('error', 'An open election cannot be edited. Close or set it to pending first.');
        }

        $positions = $positionModel->where('election_id', $id)->orderBy('sort_order', 'ASC')->findAll();
        $candidatePositions = $positionModel->getByElection($id, true);
        $parties = $partyModel->where('election_id', $id)->orderBy('name', 'ASC')->findAll();
        $candidateParties = $partyModel->getByElection($id, true);
        $candidates = $candidateModel->getByElection($id, true);

        return view('admin/election_form', [
            'mode' => 'edit',
            'election' => $election,
            'positions' => $positions,
            'candidatePositions' => $candidatePositions,
            'parties' => $parties,
            'candidateParties' => $candidateParties,
            'candidates' => $candidates,
            'departments' => $studentModel->getDepartments(),
            'yearLevels' => $this->getAvailableYearLevels(),
            'stats' => [
                'positions' => count($positions),
                'parties' => count($parties),
                'candidates' => count($candidates),
            ],
        ]);
    }

    public function storeElection()
    {
        $model = new ElectionModel();
        $data = $this->request->getPost(['title', 'description', 'academic_year', 'status', 'start_time', 'end_time', 'access_type']);
        $data['title'] = trim((string) ($data['title'] ?? ''));
        $data['academic_year'] = trim((string) ($data['academic_year'] ?? ''));

        if ($model->hasDuplicateTitleForAcademicYear($data['title'], $data['academic_year'] ?: null)) {
            $errors = ['title' => 'An election with this title already exists for this school year.'];
            if ($this->request->isAJAX()) {
                return $this->csrfJson([
                    'success' => false,
                    'message' => implode(' ', $errors),
                    'errors' => $errors,
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        foreach (['start_time', 'end_time'] as $field) {
            if (! empty($data[$field])) {
                $data[$field] = str_replace('T', ' ', $data[$field]) . ':00';
            } else {
                $data[$field] = null;
            }
        }

        $data['status'] = $data['status'] ?? 'pending';
        $data['access_type'] = $data['access_type'] ?? 'all';

        if (in_array(strtolower((string) $data['status']), ['open', 'active'], true)
            && $this->electionStartTimeIsInFuture($data['start_time'])) {
            $message = 'This election cannot be opened before its scheduled start time.';
            if ($this->request->isAJAX()) {
                return $this->csrfJson([
                    'success' => false,
                    'message' => $message,
                ]);
            }
            return redirect()->back()->withInput()->with('error', $message);
        }

        if ($data['access_type'] === 'specific_departments') {
            $departments = $this->request->getPost('allowed_departments');
            $data['allowed_departments'] = json_encode($departments ?? []);
            $data['allowed_students'] = null;
        } elseif ($data['access_type'] === 'specific_students') {
            $students = $this->request->getPost('allowed_students');
            $data['allowed_students'] = json_encode($students ?? []);
            $data['allowed_departments'] = null;
        } else {
            $data['allowed_departments'] = null;
            $data['allowed_students'] = null;
        }

        if (! $model->save($data)) {
            if ($this->request->isAJAX()) {
                $errors = $model->errors();
                return $this->csrfJson([
                    'success' => false,
                    'message' => $errors ? implode(' ', $errors) : 'Unable to create election.',
                    'errors' => $errors,
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        $newElectionId = $model->getInsertID();
        (new AuditLogModel())->record('CREATE', 'Created election ' . $data['title'] . '.');
        if ($this->request->isAJAX()) {
            return $this->csrfJson(['success' => true, 'election_id' => $newElectionId]);
        }
        return redirect()->to('/admin/elections')->with('success', 'Election created successfully.');
    }

    public function updateElection(int $id)
    {
        $model = new ElectionModel();
        $election = $model->find($id);
        if (! $election) {
            return redirect()->to('/admin/elections')->with('error', 'Election not found.');
        }
        if (in_array(strtolower((string) $election['status']), ['open', 'active'], true)) {
            return redirect()->to('/admin/elections')->with('error', 'An open election cannot be edited. Close or set it to pending first.');
        }

        $data = $this->request->getPost(['title', 'description', 'academic_year', 'status', 'start_time', 'end_time', 'access_type']);
        $data['id'] = $id;
        $data['title'] = trim((string) ($data['title'] ?? ''));
        $data['academic_year'] = trim((string) ($data['academic_year'] ?? ''));
        $data['status'] = $data['status'] ?? $election['status'];
        $data['access_type'] = $data['access_type'] ?? ($election['access_type'] ?? 'all');

        if ($model->hasDuplicateTitleForAcademicYear($data['title'], $data['academic_year'] ?: null, $id)) {
            return redirect()->back()->withInput()->with('errors', [
                'title' => 'An election with this title already exists for this school year.',
            ]);
        }

        $statusMap = ['draft' => 'pending', 'active' => 'open', 'completed' => 'closed', 'inactive' => 'pending'];
        if (isset($data['status']) && array_key_exists(strtolower($data['status']), $statusMap)) {
            $data['status'] = $statusMap[strtolower($data['status'])];
        }

        foreach (['start_time', 'end_time'] as $field) {
            if (! empty($data[$field])) {
                $data[$field] = str_replace('T', ' ', $data[$field]) . ':00';
            } else {
                $data[$field] = null;
            }
        }

        if (in_array(strtolower((string) $data['status']), ['open', 'active'], true)
            && $this->electionStartTimeIsInFuture($data['start_time'])) {
            return redirect()->back()->withInput()->with(
                'error',
                'This election cannot be opened before its scheduled start time.'
            );
        }

        if ($data['access_type'] === 'specific_departments') {
            $departments = $this->request->getPost('allowed_departments');
            $data['allowed_departments'] = json_encode($departments ?? []);
            $data['allowed_students'] = null;
        } elseif ($data['access_type'] === 'specific_students') {
            $students = $this->request->getPost('allowed_students');
            $data['allowed_students'] = is_array($students)
                ? json_encode($students)
                : ($election['allowed_students'] ?? null);
            $data['allowed_departments'] = null;
        } else {
            $data['allowed_departments'] = null;
            $data['allowed_students'] = null;
        }

        if (! $model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        (new AuditLogModel())->record('UPDATE', 'Updated election ' . $data['title'] . '.');
        return redirect()->to('/admin/elections')->with('success', 'Election updated successfully.');
    }

    public function updateElectionStatus(int $id)
    {
        $model = new ElectionModel();
        $election = $model->find($id);

        if (! $election) {
            throw new PageNotFoundException('Election not found');
        }

        $status = $this->request->getPost('status');
        if (! in_array($status, ['pending', 'open', 'closed'], true)) {
            return redirect()->back()->with('error', 'Invalid election status.');
        }

        if ($status === 'open' && $this->electionStartTimeIsInFuture($election['start_time'] ?? null)) {
            return redirect()->to('/admin/elections')
                ->with('error', 'This election cannot be opened before its scheduled start time.');
        }

        if (! $model->save([
            'id' => $id,
            'status' => $status,
            'manual_open' => $status === 'open' ? 1 : 0,
        ])) {
            return redirect()->back()->with('error', 'Failed to update election status.');
        }

        (new AuditLogModel())->record('UPDATE', 'Changed election ' . $election['title'] . ' status to ' . $status . '.');
        return redirect()->to('/admin/elections')->with('success', 'Election status updated successfully.');
    }

    public function deleteElection(int $id)
    {
        $model = new ElectionModel();
        $election = $model->find($id);
        if (! $election) {
            return redirect()->back()->with('error', 'Election not found.');
        }
        if (in_array(strtolower((string) $election['status']), ['open', 'active'], true)) {
            return redirect()->back()->with('error', 'An open election cannot be deleted. Close or set it to pending first.');
        }
        if (! $model->delete($id)) {
            return redirect()->back()->with('error', 'Failed to delete election.');
        }

        (new AuditLogModel())->record('DELETE', 'Deleted election ' . $election['title'] . '.');
        return redirect()->to('/admin/elections')->with('success', 'Election deleted successfully.');
    }

    public function announcements()
    {
        $model = new AnnouncementModel();
        $data['announcements'] = $model->orderBy('created_at', 'DESC')->findAll();

        return view('admin/announcements', $data);
    }

    public function editAnnouncement(int $id)
    {
        $model = new AnnouncementModel();
        $announcement = $model->find($id);

        if (! $announcement) {
            throw new PageNotFoundException('Announcement not found');
        }

        $data['announcement'] = $announcement;
        $data['announcements'] = $model->orderBy('created_at', 'DESC')->findAll();

        return view('admin/announcements', $data);
    }

    public function storeAnnouncement()
    {
        $model = new AnnouncementModel();
        $data = $this->request->getPost(['title', 'message', 'is_active']);
        $data['is_active'] = $data['is_active'] ?? 0;
        $db = \Config\Database::connect();

        $file = $this->request->getFile('image');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/announcements';
            if (! is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            $imagePath = 'uploads/announcements/' . $newName;

            if ($db->fieldExists('image_path', 'announcements')) {
                $data['image_path'] = $imagePath;
            }
        }

        if (! $model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        (new AuditLogModel())->record('CREATE', 'Posted announcement ' . $data['title'] . '.');
        return redirect()->to('/admin/announcements')->with('success', 'Announcement posted successfully.');
    }

    public function updateAnnouncement(int $id)
    {
        $model = new AnnouncementModel();
        $announcement = $model->find($id);
        if (! $announcement) {
            throw new PageNotFoundException('Announcement not found');
        }

        $data = $this->request->getPost(['title', 'message', 'is_active']);
        $data['id'] = $id;
        $data['is_active'] = $data['is_active'] ?? 0;
        $db = \Config\Database::connect();
        $oldImagePath = $announcement['image_path'] ?? null;
        $newImagePath = null;

        $file = $this->request->getFile('image');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/announcements';
            if (! is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            $newImagePath = 'uploads/announcements/' . $newName;

            if ($db->fieldExists('image_path', 'announcements')) {
                $data['image_path'] = $newImagePath;
            }
        } elseif ($this->request->getPost('remove_image') === '1' && $db->fieldExists('image_path', 'announcements')) {
            $data['image_path'] = null;
        }

        if (! $model->save($data)) {
            if ($newImagePath !== null) {
                $this->deleteAnnouncementImage($newImagePath);
            }
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        if (($newImagePath !== null || $this->request->getPost('remove_image') === '1') && $oldImagePath) {
            $this->deleteAnnouncementImage($oldImagePath);
        }

        (new AuditLogModel())->record('UPDATE', 'Updated announcement ' . $data['title'] . '.');
        return redirect()->to('/admin/announcements')->with('success', 'Announcement updated successfully.');
    }

    private function deleteAnnouncementImage(?string $imagePath): void
    {
        $uploadDirectory = realpath(FCPATH . 'uploads/announcements');
        if (
            $imagePath === null
            || ! str_starts_with($imagePath, 'uploads/announcements/')
            || $uploadDirectory === false
        ) {
            return;
        }

        if (\Config\Database::connect()->table('announcements')->where('image_path', $imagePath)->countAllResults() > 0) {
            return;
        }

        $filePath = realpath(FCPATH . $imagePath);
        if ($filePath !== false && dirname($filePath) === $uploadDirectory && is_file($filePath) && ! unlink($filePath)) {
            log_message('error', 'Unable to remove announcement image: ' . $imagePath);
        }
    }

    public function deleteAnnouncement(int $id)
    {
        $model = new AnnouncementModel();
        if (! $model->delete($id)) {
            return redirect()->back()->with('error', 'Failed to delete announcement.');
        }

        (new AuditLogModel())->record('DELETE', 'Deleted announcement ' . $id . '.');
        return redirect()->to('/admin/announcements')->with('success', 'Announcement deleted successfully.');
    }

    public function positions()
    {
        $positionModel = new PositionModel();
        $data['positions'] = $positionModel->findAll();
        return view('admin/positions', $data);
    }

    public function createPosition()
    {
        return view('admin/position_form', ['mode' => 'create']);
    }

    public function storePosition()
    {
        $model = new PositionModel();
        $data = $this->request->getPost(['title', 'sort_order', 'description', 'votes_required', 'election_id', 'status']);
        $redirectTo = $this->request->getPost('redirect_to');

        if (empty($data['election_id'])) {
            $data['election_id'] = $this->request->getGet('election_id') ?: null;
        }
        if (empty($data['sort_order'])) {
            $currentMax = $model->where('election_id', $data['election_id'])->selectMax('sort_order')->first();
            $data['sort_order'] = (int) ($currentMax['sort_order'] ?? 0) + 1;
        }
        if (! isset($data['status']) || $data['status'] === '') {
            $data['status'] = 'active';
        }

        if (! $model->save($data)) {
            if ($this->request->isAJAX()) {
                return $this->csrfJson(['success' => false, 'message' => 'Unable to create position.', 'errors' => $model->errors()]);
            }
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        (new AuditLogModel())->record('CREATE', 'Created position ' . $data['title'] . '.');
        if ($this->request->isAJAX()) {
            return $this->csrfJson(['success' => true, 'position_id' => $model->getInsertID()]);
        }

        if ($redirectTo) {
            return redirect()->to($redirectTo)->with('success', 'Position added successfully.');
        }
        return redirect()->to('/admin/positions')->with('success', 'Position added successfully.');
    }

    public function editPosition(int $id)
    {
        $model = new PositionModel();
        $position = $model->find($id);
        if (! $position) {
            throw new PageNotFoundException('Position not found');
        }

        return view('admin/position_form', ['mode' => 'edit', 'position' => $position]);
    }

    public function updatePosition(int $id)
    {
        $model = new PositionModel();
        $data = $this->request->getPost(['title', 'sort_order', 'description', 'votes_required', 'status', 'election_id']);
        $data['id'] = $id;
        $redirectTo = $this->request->getPost('redirect_to');

        $position = $model->find($id);
        if (! $position) {
            return redirect()->back()->with('error', 'Position not found.');
        }
        if (! empty($data['election_id']) && (int) $position['election_id'] !== (int) $data['election_id']) {
            return redirect()->back()->withInput()->with('error', 'You can only edit positions in the current election.');
        }

        if (! $model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        (new AuditLogModel())->record('UPDATE', 'Updated position ' . $data['title'] . '.');
        if ($redirectTo) {
            return redirect()->to($redirectTo)->with('success', 'Position updated successfully.');
        }
        return redirect()->to('/admin/positions')->with('success', 'Position updated successfully.');
    }

    public function deletePosition(int $id)
    {
        $model = new PositionModel();
        $position = $model->find($id);
        $redirectTo = $this->request->getPost('redirect_to');

        if (! $position) {
            return redirect()->back()->with('error', 'Position not found.');
        }
        $electionId = $this->request->getPost('election_id');
        if (! empty($electionId) && (int) $position['election_id'] !== (int) $electionId) {
            return redirect()->back()->with('error', 'You can only delete positions in the current election.');
        }

        if ((new CandidateModel())->where('position_id', $id)->countAllResults() > 0) {
            return redirect()->back()->with('error', 'This position has candidates assigned to it. Please remove or reassign the candidates before deleting this position.');
        }

        if (! $model->delete($id)) {
            return redirect()->back()->with('error', 'Failed to delete position.');
        }

        (new AuditLogModel())->record('DELETE', 'Deleted position ' . $position['title'] . '.');
        if ($redirectTo) {
            return redirect()->to($redirectTo)->with('success', 'Position deleted successfully.');
        }
        return redirect()->to('/admin/positions')->with('success', 'Position deleted successfully.');
    }

    public function parties()
    {
        $partyModel = new PartyModel();
        $data['parties'] = $partyModel->getPartiesWithElections();
        return view('admin/parties', $data);
    }

    public function createParty()
    {
        $electionModel = new \App\Models\ElectionModel();
        $elections = $electionModel->findAll();
        return view('admin/party_form', ['mode' => 'create', 'elections' => $elections, 'party' => []]);
    }

    public function storeParty()
    {
        $model = new PartyModel();
        $data = $this->request->getPost(['name', 'acronym', 'description', 'election_id', 'status', 'logo']);
        $redirectTo = $this->request->getPost('redirect_to');

        if (isset($data['name']) && is_string($data['name'])) {
            $data['name'] = trim($data['name']);
        }
        if (empty($data['election_id'])) {
            $data['election_id'] = $this->request->getGet('election_id') ?: null;
        }
        if (! isset($data['status']) || $data['status'] === '') {
            $data['status'] = 'active';
        }

        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/parties';
            if (! is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            $data['logo'] = $newName;
        }

        if (! $model->save($data)) {
            if ($this->request->isAJAX()) {
                $errors = $model->errors();
                $message = $errors
                    ? implode(' ', array_values($errors))
                    : 'Unable to create party. Check the party name and election selection.';

                $this->response->setStatusCode(422);
                return $this->csrfJson([
                    'success' => false,
                    'message' => $message,
                    'errors' => $errors,
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        (new AuditLogModel())->record('CREATE', 'Created party ' . $data['name'] . '.');
        if ($this->request->isAJAX()) {
            return $this->csrfJson(['success' => true, 'party_id' => $model->getInsertID()]);
        }

        if ($redirectTo) {
            return redirect()->to($redirectTo)->with('success', 'Party saved successfully.');
        }
        return redirect()->to('/admin/parties')->with('success', 'Party saved successfully.');
    }

    public function editParty(int $id)
    {
        $model = new PartyModel();
        $party = $model->find($id);
        if (! $party) {
            throw new PageNotFoundException('Party not found');
        }

        $electionModel = new \App\Models\ElectionModel();
        $elections = $electionModel->findAll();

        return view('admin/party_form', ['mode' => 'edit', 'party' => $party, 'elections' => $elections]);
    }

    public function updateParty(int $id)
    {
        $model = new PartyModel();
        $data = $this->request->getPost(['name', 'acronym', 'description', 'election_id', 'status', 'logo']);
        $data['id'] = $id;
        $redirectTo = $this->request->getPost('redirect_to');

        $party = $model->find($id);
        if (! $party) {
            return redirect()->back()->with('error', 'Party not found.');
        }
        if (! empty($data['election_id']) && (int) $party['election_id'] !== (int) $data['election_id']) {
            return redirect()->back()->withInput()->with('error', 'You can only edit parties in the current election.');
        }
        if (isset($data['name']) && is_string($data['name'])) {
            $data['name'] = trim($data['name']);
        }

        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/parties';
            if (! is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            $data['logo'] = $newName;
        }

        if (! $model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        (new AuditLogModel())->record('UPDATE', 'Updated party ' . $data['name'] . '.');
        if ($redirectTo) {
            return redirect()->to($redirectTo)->with('success', 'Party updated successfully.');
        }
        return redirect()->to('/admin/parties')->with('success', 'Party updated successfully.');
    }

    public function deleteParty(int $id)
    {
        $model = new PartyModel();
        $party = $model->find($id);
        $redirectTo = $this->request->getPost('redirect_to');

        if (! $party) {
            return redirect()->back()->with('error', 'Party not found.');
        }
        $electionId = $this->request->getPost('election_id');
        if (! empty($electionId) && (int) $party['election_id'] !== (int) $electionId) {
            return redirect()->back()->with('error', 'You can only delete parties in the current election.');
        }

        if ((new CandidateModel())->where('party_id', $id)->countAllResults() > 0) {
            return redirect()->back()->with('error', 'This party has candidates assigned to it. Please remove or reassign the candidates before deleting the party.');
        }

        if (! $model->delete($id)) {
            return redirect()->back()->with('error', 'Failed to delete party.');
        }

        (new AuditLogModel())->record('DELETE', 'Deleted party ' . $party['name'] . '.');
        if ($redirectTo) {
            return redirect()->to($redirectTo)->with('success', 'Party deleted successfully.');
        }
        return redirect()->to('/admin/parties')->with('success', 'Party deleted successfully.');
    }

    public function admins()
    {
        $db = \Config\Database::connect();
        $hasAdminIdNumber = $db->fieldExists('admin_id_number', 'admins');
        $hasBuiltInFlag = $db->fieldExists('is_builtin_admin', 'admins');
        $data['admins'] = $db->table('admins')
            ->select(
                ($hasAdminIdNumber ? 'id, admin_id_number' : 'id, NULL AS admin_id_number')
                . ($hasBuiltInFlag ? ', is_builtin_admin' : ', 0 AS is_builtin_admin')
                . ', username, full_name, created_at'
            )
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
        $data['canManageAdmins'] = $this->isBuiltInAdmin();

        return view('admin/admins', $data);
    }

    public function createAdmin()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Method not allowed',
            ]);
        }

        if (! $this->isBuiltInAdmin()) {
            return $this->adminManagementDenied();
        }

        $data = $this->request->getJSON(true);
        if (! is_array($data)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request data.',
            ]);
        }

        $adminIdNumber = trim((string) ($data['admin_id_number'] ?? ''));
        $username = trim((string) ($data['username'] ?? ''));
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $passwordConfirm = (string) ($data['password_confirm'] ?? '');

        if ($adminIdNumber === '' || $username === '' || $fullName === '' || $password === '' || $passwordConfirm === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Complete all required fields.',
            ]);
        }

        if (strlen($adminIdNumber) > 50 || ! preg_match('/^[A-Za-z0-9][A-Za-z0-9 .\/_-]*$/', $adminIdNumber)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Enter a valid ID number (up to 50 letters, numbers, spaces, or - . / _).',
            ]);
        }

        if (strlen($username) < 3 || strlen($username) > 100 || ! preg_match('/^[A-Za-z0-9._-]+$/', $username)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Username must be 3-100 characters and use only letters, numbers, dots, underscores, or hyphens.',
            ]);
        }

        if (strlen($fullName) > 255) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Full name must be 255 characters or fewer.',
            ]);
        }

        if (strlen($password) < 8) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Password must be at least 8 characters.',
            ]);
        }

        if ($password !== $passwordConfirm) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Passwords do not match.',
            ]);
        }

        $db = \Config\Database::connect();
        if (! $db->fieldExists('admin_id_number', 'admins')) {
            log_message('error', 'Admin ID number migration has not been applied.');

            return $this->response->setStatusCode(503)->setJSON([
                'success' => false,
                'message' => 'Admin ID support is not ready. Ask a system administrator to apply the latest database migration.',
            ]);
        }

        $existingAdmin = $db->table('admins')
            ->groupStart()
            ->where('username', $username)
            ->orWhere('admin_id_number', $adminIdNumber)
            ->groupEnd()
            ->countAllResults();

        if ($existingAdmin > 0) {
            $duplicateUsername = $db->table('admins')->where('username', $username)->countAllResults() > 0;

            return $this->response->setJSON([
                'success' => false,
                'message' => $duplicateUsername
                    ? 'That username is already in use.'
                    : 'That ID number is already registered.',
            ]);
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        if ($hashedPassword === false) {
            log_message('error', 'Unable to hash password while creating an administrator.');

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Unable to create the admin account right now.',
            ]);
        }

        try {
            $result = $db->table('admins')->insert([
                'admin_id_number' => $adminIdNumber,
                'username' => $username,
                'full_name' => $fullName,
                'password' => $hashedPassword,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if ($result) {
                (new AuditLogModel())->record('CREATE', 'Created administrator account ' . $username . '.');
                return $this->response->setJSON(['success' => true, 'message' => 'Admin created successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to create admin']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Admin account creation failed: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Unable to create the admin account right now.',
            ]);
        }
    }

    public function updateAdmin(int $id)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Method not allowed',
            ]);
        }

        if (! $this->isBuiltInAdmin()) {
            return $this->adminManagementDenied();
        }

        $data = $this->request->getJSON(true);
        if (! is_array($data)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request data.',
            ]);
        }

        $adminIdNumber = trim((string) ($data['admin_id_number'] ?? ''));
        $username = trim((string) ($data['username'] ?? ''));
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $passwordConfirm = (string) ($data['password_confirm'] ?? '');

        if ($adminIdNumber === '' || $username === '' || $fullName === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID number, username, and full name are required.',
            ]);
        }

        if (strlen($adminIdNumber) > 50 || ! preg_match('/^[A-Za-z0-9][A-Za-z0-9 .\/_-]*$/', $adminIdNumber)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Enter a valid ID number (up to 50 letters, numbers, spaces, or - . / _).',
            ]);
        }

        if (strlen($username) < 3 || strlen($username) > 100 || ! preg_match('/^[A-Za-z0-9._-]+$/', $username)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Username must be 3-100 characters and use only letters, numbers, dots, underscores, or hyphens.',
            ]);
        }

        if (strlen($fullName) > 255) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Full name must be 255 characters or fewer.',
            ]);
        }

        if ($password !== '' && strlen($password) < 8) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'A new password must be at least 8 characters.',
            ]);
        }

        if ($password !== $passwordConfirm) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Passwords do not match.',
            ]);
        }

        $db = \Config\Database::connect();
        if (! $db->fieldExists('admin_id_number', 'admins')) {
            return $this->response->setStatusCode(503)->setJSON([
                'success' => false,
                'message' => 'Admin ID support is not ready. Apply the latest database migrations.',
            ]);
        }

        $targetAdmin = $db->table('admins')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        if (! $targetAdmin) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Admin account not found.',
            ]);
        }

        if ((int) ($targetAdmin['is_builtin_admin'] ?? 0) === 1) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'The built-in administrator account cannot be changed here.',
            ]);
        }

        $duplicateUsername = $db->table('admins')
            ->where('username', $username)
            ->where('id !=', $id)
            ->countAllResults() > 0;
        if ($duplicateUsername) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'That username is already in use.',
            ]);
        }

        $duplicateIdNumber = $db->table('admins')
            ->where('admin_id_number', $adminIdNumber)
            ->where('id !=', $id)
            ->countAllResults() > 0;
        if ($duplicateIdNumber) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'That ID number is already registered.',
            ]);
        }

        $updates = [
            'admin_id_number' => $adminIdNumber,
            'username' => $username,
            'full_name' => $fullName,
        ];

        if ($password !== '') {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($hashedPassword === false) {
                log_message('error', 'Unable to hash password while updating an administrator.');

                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Unable to update the admin account right now.',
                ]);
            }
            $updates['password'] = $hashedPassword;
        }

        try {
            if (! $db->table('admins')->where('id', $id)->update($updates)) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Unable to update the admin account.',
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Admin account update failed: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Unable to update the admin account right now.',
            ]);
        }

        (new AuditLogModel())->record('UPDATE', 'Updated administrator account ' . $id . '.');
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Admin account updated successfully.',
        ]);
    }

    public function deleteAdmin(int $id)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Method not allowed',
            ]);
        }

        if (! $this->isBuiltInAdmin()) {
            return $this->adminManagementDenied();
        }

        if ($id === (int) session()->get('admin_id')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'The built-in administrator account cannot be deleted.',
            ]);
        }

        $db = \Config\Database::connect();
        $targetAdmin = $db->table('admins')
            ->select('id, is_builtin_admin')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (! $targetAdmin) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Admin account not found.',
            ]);
        }

        if ((int) $targetAdmin['is_builtin_admin'] === 1) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'The built-in administrator account cannot be deleted.',
            ]);
        }

        try {
            if (! $db->table('admins')->where('id', $id)->delete()) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Unable to delete the admin account.',
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Admin account deletion failed: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Unable to delete the admin account right now.',
            ]);
        }

        (new AuditLogModel())->record('DELETE', 'Deleted administrator account ' . $id . '.');
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Admin account deleted successfully.',
        ]);
    }

    public function security()
    {
        $settingModel = new SettingModel();

        $data = [
            'campus_latitude' => $settingModel->getValue('campus_latitude', ''),
            'campus_longitude' => $settingModel->getValue('campus_longitude', ''),
            'campus_access_radius' => $settingModel->getValue('campus_access_radius', ''),
        ];

        return view('admin/security', $data);
    }

    public function updateSecurity()
    {
        $settingModel = new SettingModel();

        $latitude = $this->request->getPost('campus_latitude');
        $longitude = $this->request->getPost('campus_longitude');
        $radius = $this->request->getPost('campus_access_radius');

        if ($radius !== '' && ! is_numeric($radius)) {
            return redirect()->back()->withInput()->with('error', 'Please enter a valid numeric radius in meters.');
        }

        if ($latitude !== '' && ! is_numeric($latitude)) {
            return redirect()->back()->withInput()->with('error', 'Please enter a valid campus latitude.');
        }

        if ($longitude !== '' && ! is_numeric($longitude)) {
            return redirect()->back()->withInput()->with('error', 'Please enter a valid campus longitude.');
        }

        if ($radius !== '' && ($latitude === '' || $longitude === '')) {
            return redirect()->back()->withInput()->with('error', 'Campus latitude and longitude are required when an access radius is set.');
        }

        $db = \Config\Database::connect();
        $db->transStart();
        foreach ([
            'campus_latitude' => $latitude,
            'campus_longitude' => $longitude,
            'campus_access_radius' => $radius,
        ] as $name => $value) {
            if (! $settingModel->setValue($name, $value)) {
                $db->transRollback();
                log_message('error', 'Unable to save campus access setting: ' . $name . '.');
                return redirect()->back()->withInput()->with('error', 'Unable to save campus access settings.');
            }
        }
        $db->transComplete();
        if (! $db->transStatus()) {
            log_message('error', 'Unable to save campus access settings.');
            return redirect()->back()->withInput()->with('error', 'Unable to save campus access settings.');
        }

        (new AuditLogModel())->record('UPDATE', 'Updated campus access settings.');

        return redirect()->to('/admin/security')->with('success', 'Campus access settings saved successfully.');
    }

    public function results()
    {
        $electionModel = new ElectionModel();
        $voteModel = new VoteModel();

        $selectedElectionId = $this->request->getGet('election_id') ?? null;
        $elections = $electionModel->findAll();
        
        if ($selectedElectionId) {
            $election = $electionModel->find($selectedElectionId);
        } else {
            $election = $electionModel->where('status', 'closed')->orderBy('id', 'DESC')->first();
            if (empty($election)) {
                $election = $electionModel->orderBy('id', 'DESC')->first();
            }
        }
        
        $data['elections'] = $elections;
        $data['election'] = $election;
        $data['selectedElectionId'] = $election ? $election['id'] : null;
        $isClosed = $election && in_array(strtolower((string) $election['status']), ['closed', 'completed'], true);
        $data['positions'] = $election
            ? (new PositionModel())->getByElection((int) $election['id'], true)
            : [];
        $data['results'] = $election ? $voteModel->getResults($election['id']) : [];
        $data['tieWinners'] = $election
            ? \Config\Database::connect()->table('election_tie_winners')->where('election_id', $election['id'])->get()->getResultArray()
            : [];

        return view('admin/results', $data);
    }

    public function saveTieWinner()
    {
        $electionId = filter_var($this->request->getPost('election_id'), FILTER_VALIDATE_INT);
        $positionId = filter_var($this->request->getPost('position_id'), FILTER_VALIDATE_INT);
        $candidateId = filter_var($this->request->getPost('candidate_id'), FILTER_VALIDATE_INT);
        if (! $electionId || ! $positionId || ! $candidateId) {
            return redirect()->back()->with('error', 'Select a tied candidate to save the winner.');
        }

        $election = (new ElectionModel())->find($electionId);
        if (! $election || ! in_array(strtolower((string) $election['status']), ['closed', 'completed'], true)) {
            return redirect()->to('/admin/results?election_id=' . $electionId)
                ->with('error', 'A tie-break winner can only be saved after the election closes.');
        }

        $candidateResults = (new VoteModel())->getResults($electionId);
        $positionCandidates = array_values(array_filter(
            $candidateResults,
            static fn (array $candidate): bool => (int) $candidate['position_id'] === $positionId
        ));
        if ($positionCandidates === []) {
            return redirect()->back()->with('error', 'No tied candidates were found for this position.');
        }
        $highestVotes = (int) $positionCandidates[0]['vote_count'];
        $leaders = array_values(array_filter(
            $positionCandidates,
            static fn (array $candidate): bool => (int) $candidate['vote_count'] === $highestVotes
        ));
        if (count($leaders) < 2 || ! in_array($candidateId, array_map(
            static fn (array $candidate): int => (int) $candidate['candidate_id'],
            $leaders
        ), true)) {
            return redirect()->back()->with('error', 'The selected candidate is not tied for the lead.');
        }

        $table = \Config\Database::connect()->table('election_tie_winners');
        $existing = $table->where('election_id', $electionId)->where('position_id', $positionId)->get()->getRowArray();
        $winnerData = [
            'candidate_id' => $candidateId,
            'selected_by' => session()->get('admin_id'),
            'selected_at' => date('Y-m-d H:i:s'),
        ];
        if ($existing) {
            $saved = $table->where('id', $existing['id'])->update($winnerData);
        } else {
            $saved = $table->insert($winnerData + [
                'election_id' => $electionId,
                'position_id' => $positionId,
            ]);
        }
        if (! $saved) {
            log_message('error', 'Unable to save selected tie-break winner for election ' . $electionId . '.');
            return redirect()->back()->with('error', 'Unable to save the selected winner.');
        }

        $selectedCandidate = array_values(array_filter(
            $leaders,
            static fn (array $candidate): bool => (int) $candidate['candidate_id'] === $candidateId
        ))[0];
        (new AuditLogModel())->record('UPDATE', 'Selected ' . $selectedCandidate['full_name'] . ' as the tie-break winner.');

        return redirect()->to('/admin/results?election_id=' . $electionId)->with('success', 'Tie-break winner saved.');
    }
}