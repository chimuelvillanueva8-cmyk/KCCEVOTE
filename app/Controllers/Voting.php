<?php

namespace App\Controllers;

use App\Models\ElectionModel;
use App\Models\AuditLogModel;
use App\Models\VoteModel;
use App\Libraries\VotingRules;
use App\Libraries\CampusLocation;

class Voting extends BaseController
{
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        if (session()->get('role') !== 'student') {
            $session = session();
            $session->setFlashdata('error', 'Please log in to access the student portal.');
            $redirectUrl = base_url('/login?action=student');
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    protected function checkLocationAndLogoutIfNeeded(bool $requireSubmittedLocation = false)
    {
        $session = session();
        $currentLatitude = $this->request->getPost('latitude') ?? $this->request->getGet('latitude');
        $currentLongitude = $this->request->getPost('longitude') ?? $this->request->getGet('longitude');

        $requestHasLocation = $this->request->getPost('latitude') !== null
            || $this->request->getPost('longitude') !== null
            || $this->request->getGet('latitude') !== null
            || $this->request->getGet('longitude') !== null;
        if (! $requireSubmittedLocation && ! $requestHasLocation) {
            $currentLatitude = $session->get('login_latitude');
            $currentLongitude = $session->get('login_longitude');
        }

        $latValue = is_numeric($currentLatitude) ? floatval($currentLatitude) : null;
        $lngValue = is_numeric($currentLongitude) ? floatval($currentLongitude) : null;

        if (! $this->isWithinCampusRange($latValue, $lngValue)) {
            $logoutAt = date('Y-m-d H:i:s');
            (new AuditLogModel())->record(
                'STUDENT LOGOUT',
                'Student was logged out because campus location verification failed at ' . $logoutAt . '.',
                $logoutAt
            );
            $session->remove(['user_id', 'student_id', 'full_name', 'username', 'role', 'isLoggedIn', 'login_latitude', 'login_longitude', 'temp_user_id', 'temp_user_type', 'temp_user_name', 'temp_student_id', 'temp_face_descriptor']);
            return redirect()->to('/login?action=student')->with('error', 'A valid campus location is required to access the student portal.');
        }

        return null;
    }

    public function index()
    {
        // Check location and auto-logout if needed
        $locationCheck = $this->checkLocationAndLogoutIfNeeded();
        if ($locationCheck) {
            return $locationCheck;
        }

        $session = session();
        $studentId = $session->get('user_id');
        $studentModel = new \App\Models\StudentModel();
        $electionModel = new ElectionModel();

        // Get student's department
        $student = $studentModel->find($studentId);
        $isInactive = $student && (empty($student['is_active']) && ($student['status'] ?? '') !== 'Active');
        if (!$student || $isInactive) {
            $logoutAt = date('Y-m-d H:i:s');
            $reason = $student ? 'Inactive student session ended' : 'Student session ended because the account no longer exists';
            (new AuditLogModel())->record('STUDENT LOGOUT', $reason . ' at ' . $logoutAt . '.', $logoutAt);
            $session->remove(['user_id', 'student_id', 'full_name', 'username', 'role', 'isLoggedIn', 'login_latitude', 'login_longitude']);
            return redirect()->to('/login?action=student')->with('error', 'Your account is inactive and cannot vote. Please contact the administrator.');
        }

        $needsFaceRegistration = empty($student['face_descriptor']) && empty($student['face_registered']);

        $studentDepartment = $student['department'] ?? null;

        // Get only accessible elections for this student
        $allOpenElections = $electionModel->getOpenElections();
        $accessibleElections = [];
        
        foreach ($allOpenElections as $election) {
            if ($electionModel->canStudentAccess($election['id'], $studentDepartment, $studentId)) {
                $accessibleElections[] = $election;
            }
        }

        // Get campus settings for location verification
        $campusSettings = $this->getCampusSettings();

        return view('voting/home', [
            'elections' => $accessibleElections,
            'selectedElection' => null,
            'candidates' => [],
            'hasVoted' => false,
            'receipt' => [],
            'results' => [],
            'full_name' => $session->get('full_name'),
            'campus_latitude' => $campusSettings['campus_latitude'],
            'campus_longitude' => $campusSettings['campus_longitude'],
            'campus_radius' => $campusSettings['campus_access_radius'],
            'campus_location_required' => CampusLocation::isConfigured($campusSettings),
            'needs_face_registration' => $needsFaceRegistration,
            'show_completion_receipt' => false
        ]);
    }

    public function show($electionId)
    {
        // Check location and auto-logout if needed
        $locationCheck = $this->checkLocationAndLogoutIfNeeded();
        if ($locationCheck) {
            return $locationCheck;
        }

        $session = session();
        $studentId = $session->get('user_id');
        $studentModel = new \App\Models\StudentModel();
        $electionModel = new ElectionModel();
        $voteModel = new VoteModel();

        $election = $electionModel->find($electionId);
        if (! $election || $election['status'] !== 'open') {
            return redirect()->to('/voting')->with('error', 'Selected election is not available.');
        }

        // Get student's department
        $student = $studentModel->find($studentId);
        $isInactive = $student && (empty($student['is_active']) && ($student['status'] ?? '') !== 'Active');
        if (!$student || $isInactive) {
            $session->remove(['user_id', 'student_id', 'full_name', 'username', 'role', 'isLoggedIn', 'login_latitude', 'login_longitude']);
            return redirect()->to('/login?action=student')->with('error', 'Your account is inactive and cannot vote. Please contact the administrator.');
        }

        $needsFaceRegistration = empty($student['face_descriptor']) && empty($student['face_registered']);

        $studentDepartment = $student['department'] ?? null;

        // Check if student has access to this election
        if (! $electionModel->canStudentAccess($electionId, $studentDepartment, $studentId)) {
            return redirect()->to('/voting')->with('error', 'You do not have access to this election.');
        }

        $hasVoted = $voteModel->hasVoted($studentId, $electionId);
        $showCompletionReceipt = $this->request->getGet('completed') === '1';
        $candidates = $this->eligibleCandidates(
            $electionModel->getCandidatesWithDetails($electionId, false),
            $student['year_level'] ?? null
        );
        $positions = (new \App\Models\PositionModel())->getByElection((int) $electionId);
        $receipt = $hasVoted ? $voteModel->getReceipt($studentId, $electionId) : [];
        $results = $voteModel->getResults($electionId);

        // Get accessible elections for the sidebar
        $allOpenElections = $electionModel->getOpenElections();
        $accessibleElections = [];
        
        foreach ($allOpenElections as $e) {
            if ($electionModel->canStudentAccess($e['id'], $studentDepartment, $studentId)) {
                $accessibleElections[] = $e;
            }
        }

        // Get campus settings for location verification
        $campusSettings = $this->getCampusSettings();

        return view('voting/home', [
            'elections' => $accessibleElections,
            'selectedElection' => $election,
            'candidates' => $candidates,
            'positions' => $positions,
            'hasVoted' => $hasVoted,
            'receipt' => $receipt,
            'results' => $results,
            'full_name' => $session->get('full_name'),
            'campus_latitude' => $campusSettings['campus_latitude'],
            'campus_longitude' => $campusSettings['campus_longitude'],
            'campus_radius' => $campusSettings['campus_access_radius'],
            'campus_location_required' => CampusLocation::isConfigured($campusSettings),
            'needs_face_registration' => $needsFaceRegistration
            , 'show_completion_receipt' => $showCompletionReceipt
        ]);
    }

    public function cast()
    {
        // Check location and auto-logout if needed
        $locationCheck = $this->checkLocationAndLogoutIfNeeded(true);
        if ($locationCheck) {
            return $locationCheck;
        }

        $db = db_connect();
        $voteModel = new VoteModel();
        $electionModel = new ElectionModel();
        $studentModel = new \App\Models\StudentModel();
        $positionModel = new \App\Models\PositionModel();

        $electionId = filter_var($this->request->getPost('election_id'), FILTER_VALIDATE_INT);
        $postedVotes = $this->request->getPost('votes') ?? [];
        $studentId = session()->get('user_id');
        if (! $electionId || ! is_array($postedVotes)) {
            return redirect()->back()->with('error', 'Invalid ballot submission.');
        }

        $student = $studentModel->find($studentId);
        $isInactive = $student && (empty($student['is_active']) && ($student['status'] ?? '') !== 'Active');
        if (! $student || $isInactive) {
            return redirect()->back()->with('error', 'Your account is inactive and cannot vote.');
        }
        if (! $electionModel->canStudentAccess($electionId, $student['department'] ?? null, $studentId)) {
            return redirect()->back()->with('error', 'You do not have access to this election.');
        }

        $candidateRows = $this->eligibleCandidates(
            $electionModel->getCandidatesWithDetails($electionId, false),
            $student['year_level'] ?? null
        );
        $candidatesByPosition = [];
        foreach ($candidateRows as $candidate) {
            $candidatesByPosition[(int) $candidate['position_id']][(int) $candidate['id']] = $candidate;
        }

        $positions = $positionModel->getByElection((int) $electionId);
        $validatedVotes = [];
        foreach ($positions as $position) {
            $positionId = (int) $position['id'];
            $selection = $postedVotes[$positionId] ?? [];
            $selection = is_array($selection) ? $selection : [$selection];
            $selectedIds = [];
            foreach ($selection as $candidateId) {
                $candidateId = filter_var($candidateId, FILTER_VALIDATE_INT);
                if (! $candidateId || ! isset($candidatesByPosition[$positionId][$candidateId]) || in_array($candidateId, $selectedIds, true)) {
                    return redirect()->back()->with('error', 'Your ballot contains an invalid or ineligible candidate selection.');
                }
                $selectedIds[] = $candidateId;
            }
            $votesRequired = (int) ($position['votes_required'] ?? 1);
            if (! VotingRules::hasValidSelectionCount(count($selectedIds), $votesRequired)) {
                return redirect()->back()->with('error', 'Please make the required number of selections for ' . $position['title'] . '.');
            }
            $validatedVotes[$positionId] = $selectedIds;
        }

        foreach ($postedVotes as $positionId => $selection) {
            if (! isset($validatedVotes[(int) $positionId])) {
                return redirect()->back()->with('error', 'Your ballot contains an invalid position.');
            }
        }

        $voteCastAt = date('Y-m-d H:i:s');
        $db->transBegin();
        try {
            $lockedElection = $db->query('SELECT id, status FROM elections WHERE id = ? FOR UPDATE', [$electionId])->getRowArray();
            if (! $lockedElection || strtolower((string) $lockedElection['status']) !== 'open') {
                $db->transRollback();
                return redirect()->back()->with('error', 'The selected election is not available.');
            }
            if ($voteModel->hasVoted($studentId, $electionId)) {
                $db->transRollback();
                return redirect()->back()->with('error', 'You have already submitted a ballot for this election.');
            }

            foreach ($validatedVotes as $positionId => $candidateIds) {
                foreach ($candidateIds as $candidateId) {
                    if ($voteModel->insert([
                        'student_id' => $studentId,
                        'candidate_id' => $candidateId,
                        'position_id' => $positionId,
                        'election_id' => $electionId,
                        'created_at' => $voteCastAt,
                    ]) === false) {
                        throw new \RuntimeException('Vote insert failed: ' . json_encode($voteModel->errors()));
                    }
                }
            }

            if (! $db->transStatus()) {
                throw new \RuntimeException('Vote transaction failed.');
            }
            if (! $db->transCommit()) {
                throw new \RuntimeException('Vote transaction could not be committed.');
            }
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Vote submission failed for student ' . $studentId . ' and election ' . $electionId . ': ' . $exception->getMessage());
            return redirect()->back()->with('error', 'Your vote could not be saved. Please try again.');
        }
        (new AuditLogModel())->record(
            'VOTING',
            'Student cast a vote in election ' . $electionId . ' at ' . $voteCastAt . '.',
            $voteCastAt
        );

        return redirect()->to('/voting/' . $electionId . '?completed=1')->with('success', 'Your vote has been cast successfully!');
    }

    private function eligibleCandidates(array $candidates, ?string $yearLevel): array
    {
        return VotingRules::filterEligibleCandidates($candidates, $yearLevel);
    }

    public function deactivateAfterVote()
    {
        $studentId = session()->get('user_id');
        $electionId = $this->request->getPost('election_id');
        if (!$studentId || session()->get('role') !== 'student') {
            return $this->response->setJSON(['success' => false]);
        }

        if (!$electionId || !(new VoteModel())->hasCompletedVote($studentId, $electionId)) {
            return $this->response->setJSON(['success' => false]);
        }

        $studentModel = new \App\Models\StudentModel();
        $updated = $studentModel->update($studentId, [
            'status' => 'Inactive',
            'is_active' => 0,
        ]);
        $logoutAt = date('Y-m-d H:i:s');
        if ($updated) {
            (new AuditLogModel())->record('UPDATE', 'Student account was deactivated after completing a vote.');
        }
        (new AuditLogModel())->record(
            'STUDENT LOGOUT',
            'Student was logged out after completing a vote at ' . $logoutAt . '.',
            $logoutAt
        );
        session()->destroy();

        return $this->response->setJSON(['success' => (bool)$updated]);
    }

    public function accountSettings()
    {
        $studentModel = new \App\Models\StudentModel();
        $studentId = session()->get('user_id');
        
        $student = $studentModel->find($studentId);
        
        if (!$student) {
            return redirect()->to('/login')->with('error', 'Student not found.');
        }

        $departmentName = trim((string) ($student['department'] ?? ''));
        if (! empty($student['department_id'])) {
            $department = (new \App\Models\DepartmentModel())->find($student['department_id']);
            $departmentName = $department['department_name'] ?? $departmentName;
        }

        $courseName = trim((string) ($student['course'] ?? ''));
        if (! empty($student['course_id'])) {
            $course = (new \App\Models\CourseModel())->find($student['course_id']);
            $courseName = $course['course_name'] ?? $courseName;
        }
        
        return view('student/account-settings', [
            'student' => $student,
            'departmentName' => $departmentName,
            'courseName' => $courseName,
        ]);
    }

    public function updateSettings()
    {
        $studentModel = new \App\Models\StudentModel();
        $studentId = session()->get('user_id');
        
        // Validation
        if (!$this->validate([
            'section' => 'required|string',
            'year_level' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[4]',
            'school_year' => 'required|string',
            'current_password' => 'permit_empty',
            'new_password' => 'permit_empty',
            'confirm_password' => 'permit_empty'
        ])) {
            return redirect()->back()->withInput()->with('error', 'Validation failed: ' . implode(', ', $this->validator->getErrors()));
        }
        
        $section = $this->request->getPost('section');
        $year_level = $this->request->getPost('year_level');
        $school_year = $this->request->getPost('school_year');
        $current_password = $this->request->getPost('current_password');
        $new_password = $this->request->getPost('new_password');
        $confirm_password = $this->request->getPost('confirm_password');
        
        $updateData = [
            'section' => $section,
            'year_level' => $year_level,
            'school_year' => $school_year
        ];
        
        // Handle password change
        if (!empty($new_password) || !empty($confirm_password)) {
            if (empty($current_password)) {
                return redirect()->back()->withInput()->with('error', 'Current password is required to change password.');
            }
            
            if ($new_password !== $confirm_password) {
                return redirect()->back()->withInput()->with('error', 'New passwords do not match.');
            }
            
            if (strlen($new_password) < 6) {
                return redirect()->back()->withInput()->with('error', 'New password must be at least 6 characters.');
            }
            
            // Verify current password
            $student = $studentModel->find($studentId);
            if (!password_verify($current_password, $student['password'])) {
                return redirect()->back()->withInput()->with('error', 'Current password is incorrect.');
            }
            
            // Don't hash here, StudentModel's beforeUpdate event will do it!
            $updateData['password'] = $new_password;
        }
        
        // Update student
        if ($studentModel->update($studentId, $updateData)) {
            (new AuditLogModel())->record('UPDATE', 'Student updated account settings.');
            return redirect()->to('/voting')->with('success', 'Account settings updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update account settings. Please try again.');
        }
    }

    /**
     * Verify student location and logout if outside campus range
     * Called periodically from frontend JavaScript
     */
    public function verifyLocation()
    {
        $latitude = $this->request->getPost('latitude');
        $longitude = $this->request->getPost('longitude');
        
        $latValue = is_numeric($latitude) ? floatval($latitude) : null;
        $lngValue = is_numeric($longitude) ? floatval($longitude) : null;

        $settings = $this->getCampusSettings();
        
        if (! CampusLocation::isConfigured($settings)) {
            return $this->response->setJSON(['outside_range' => false, 'message' => 'Campus settings not configured']);
        }

        if (! CampusLocation::isWithinRange($latValue, $lngValue, $settings)) {
            // Student is outside campus range - logout
            $session = session();
            $logoutAt = date('Y-m-d H:i:s');
            (new AuditLogModel())->record(
                'STUDENT LOGOUT',
                'Student was logged out after leaving the campus access range at ' . $logoutAt . '.',
                $logoutAt
            );
            $session->destroy();
            return $this->response->setJSON(['outside_range' => true, 'message' => 'You are outside the campus range and have been logged out']);
        }

        return $this->response->setJSON(['outside_range' => false, 'message' => 'Location verified - within range']);
    }
}
