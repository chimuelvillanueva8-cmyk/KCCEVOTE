<?php

namespace App\Controllers;

use App\Libraries\FaceIdentity;
use App\Models\AuditLogModel;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        $session = session();
        if ($session->get('isLoggedIn')) {
            return redirect()->to($session->get('role') === 'admin' ? '/admin' : '/voting');
        }

        $action = $this->request->getGet('action') ?? 'student';
        $verify = $this->request->getGet('verify');
        
        return view('auth/login', [
            'action' => $action,
            'verify' => $verify,
            'error'  => $session->getFlashdata('error'),
            'success'=> $session->getFlashdata('success'),
            'campus_location_required' => $this->isCampusLocationConfigured(),
        ]);
    }

    public function attemptLogin()
    {
        $session = session();
        $userModel = new UserModel();
        
        $action = $this->request->getGet('action') ?? 'student';
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $latitude = $this->request->getPost('latitude');
        $longitude = $this->request->getPost('longitude');

        if ($action === 'admin') {
            $admin = $userModel->verifyAdmin($username, $password);
            if ($admin) {
                $loginAt = date('Y-m-d H:i:s');
                $session->regenerate(true);
                $session->set([
                    'admin_id'   => $admin['id'],
                    'admin_name' => $admin['full_name'] ?? $admin['username'],
                    'username'   => $admin['username'],
                    'is_builtin_admin' => (int) ($admin['is_builtin_admin'] ?? 0) === 1,
                    'role'       => 'admin',
                    'isLoggedIn' => true
                ]);
                (new AuditLogModel())->record('ADMIN LOGIN', 'Administrator logged in at ' . $loginAt . '.', $loginAt);
                return redirect()->to('/admin');
            }
        } else {
            $latValue = is_numeric($latitude) ? floatval($latitude) : null;
            $lngValue = is_numeric($longitude) ? floatval($longitude) : null;

            if (! $this->isWithinCampusRange($latValue, $lngValue)) {
                $session->setFlashdata('error', 'Access denied: your location is outside the permitted campus access radius.');
                return redirect()->back()->withInput();
            }

            $studentModel = new \App\Models\StudentModel();
            // Use raw student identifier (allow alphanumeric IDs)
            $identifier = trim((string) ($username ?? ''));
            if ($identifier === '') {
                $session->setFlashdata('error', 'Student ID not found.');
                return redirect()->back()->withInput();
            }
            $student = $userModel->getStudent($identifier);
            if (!$student) {
                $session->setFlashdata('error', 'Student ID not found.');
                return redirect()->back()->withInput();
            }

            if ($student) {
                $lockedUntil = !empty($student['locked_until']) ? strtotime($student['locked_until']) : 0;
                if ($lockedUntil && $lockedUntil > time()) {
                    $session->setFlashdata('error', 'This student account is temporarily locked after repeated failed attempts. Please contact the admin.');
                    return redirect()->back()->withInput();
                }
                $isActive = (string) ($student['is_active'] ?? '') === '1';
                $hasActiveStatus = ($student['status'] ?? '') === 'Active';
                if (!$isActive && !$hasActiveStatus) {
                    $session->setFlashdata('error', 'Your student account is currently inactive. Please contact the administrator.');
                    return redirect()->back()->withInput();
                }

                if (!$studentModel->verifyPassword($password, $student['password'])) {
                    log_message('warning', 'Student login password verification failed.');
                    $attempts = (int) ($student['failed_login_attempts'] ?? 0) + 1;
                    $lockUntil = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 900) : null;
                    $studentModel->update($student['id'], [
                        'failed_login_attempts' => $attempts,
                        'locked_until' => $lockUntil,
                    ]);
                    $session->setFlashdata('error', $attempts >= 5 ? 'Too many failed attempts. Please contact the admin to unlock your account.' : 'Incorrect password. Please try again.');
                    return redirect()->back()->withInput();
                }
                $loginAt = date('Y-m-d H:i:s');
                $hasFaceRegistration = !empty($student['face_descriptor']) || !empty($student['face_registered']);
                $loginUpdate = [
                    'failed_login_attempts' => 0,
                    'locked_until' => null,
                    'last_login_at' => $loginAt,
                    'face_registered' => $hasFaceRegistration ? 1 : 0,
                ];
                $storedPassword = (string) $student['password'];
                if (! password_verify($password, $storedPassword) || password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    if ($passwordHash === false) {
                        log_message('error', 'Unable to generate an upgraded student password hash.');
                        $session->setFlashdata('error', 'Unable to complete login. Please contact the administrator.');
                        return redirect()->back();
                    }
                    $loginUpdate['password'] = $passwordHash;
                }

                if (! $studentModel->update($student['id'], $loginUpdate)) {
                    log_message('error', 'Unable to update student login security state.');
                    $session->setFlashdata('error', 'Unable to complete login. Please contact the administrator.');
                    return redirect()->back();
                }

                $session->regenerate(true);
                $session->set([
                    'user_id'         => $student['id'],
                    'student_id'      => $student['student_id'],
                    'full_name'       => $student['full_name'],
                    'username'        => $student['username'],
                    'role'            => 'student',
                    'isLoggedIn'      => true,
                    'login_latitude'  => $latValue,
                    'login_longitude' => $lngValue
                ]);
                (new AuditLogModel())->record('STUDENT LOGIN', 'Student logged in at ' . $loginAt . '.', $loginAt);
                return redirect()->to('/voting');
            }
        }

        $session->setFlashdata('error', 'Invalid credentials');
        return redirect()->back();
    }

    public function faceVerify()
    {
        $session = session();
        $requestData = $this->request->getJSON(true) ?? [];
        if ($session->get('temp_user_id')) {
                $incomingFaceDescriptor = $requestData['face_descriptor'] ?? $this->request->getPost('face_descriptor');
                $normalizedFacePayload = $incomingFaceDescriptor ? $this->normalizeFacePayload($incomingFaceDescriptor) : null;
                $registeredFacePayload = (string) $session->get('temp_face_descriptor');
                $incomingDescriptor = $normalizedFacePayload
                    ? (FaceIdentity::normalizePayload($normalizedFacePayload)['descriptor'] ?? [])
                    : [];
                if (
                    ! FaceIdentity::isValidDescriptor($incomingDescriptor)
                    || ! $this->facesMatch($normalizedFacePayload, $registeredFacePayload, 0.65)
                ) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Face verification failed. Please try again.']);
                }

                $latitude = $this->request->getPost('latitude') ?? ($requestData['latitude'] ?? null);
                $longitude = $this->request->getPost('longitude') ?? ($requestData['longitude'] ?? null);
                
                $latValue = is_numeric($latitude) ? floatval($latitude) : null;
                $lngValue = is_numeric($longitude) ? floatval($longitude) : null;
                
                if (! $this->isWithinCampusRange($latValue, $lngValue)) {
                    $session->setFlashdata('error', 'Access denied: your location is outside the permitted campus access radius.');
                    $session->remove(['temp_user_id', 'temp_user_type', 'temp_user_name', 'temp_student_id', 'temp_face_descriptor']);
                    return $this->response->setJSON(['success' => false, 'message' => 'Location verification failed.']);
                }
                
                $loginAt = date('Y-m-d H:i:s');
                $studentModel = new \App\Models\StudentModel();
                if (! $studentModel->update($session->get('temp_user_id'), [
                    'failed_login_attempts' => 0,
                    'locked_until' => null,
                    'last_login_at' => $loginAt,
                    'face_registered' => 1
                ])) {
                    log_message('error', 'Unable to update student face-login security state.');
                    return $this->response->setStatusCode(500)->setJSON([
                        'success' => false,
                        'message' => 'Unable to complete face login. Please use your password or contact the administrator.',
                    ]);
                }
                
                $session->regenerate(true);
                $session->set([
                    'user_id'         => $session->get('temp_user_id'),
                    'student_id'      => $session->get('temp_student_id'),
                    'full_name'       => $session->get('temp_user_name'),
                    'role'            => 'student',
                    'isLoggedIn'      => true,
                    'login_latitude'  => $latValue,
                    'login_longitude' => $lngValue
            ]);
            $session->remove(['temp_user_id', 'temp_user_type', 'temp_user_name', 'temp_student_id', 'temp_face_descriptor']);
            (new AuditLogModel())->record('STUDENT LOGIN', 'Student logged in using face verification at ' . $loginAt . '.', $loginAt);
            return $this->response->setJSON(['success' => true]);
        }
        
        $studentModel = new \App\Models\StudentModel();
        $studentId = $this->request->getPost('student_id');
        
        // Check if student already has registered account with face
        $existingStudent = $studentModel->where('student_id', $studentId)
                                        ->where('face_descriptor IS NOT NULL', null, false)
                                        ->first();
        
        if ($existingStudent) {
            return redirect()->back()->withInput()->with('error', '⚠️ This Student ID is already registered. One account per student is allowed. Please login with your existing account instead.');
        }
        
        $rules = [
            'student_id' => 'required|is_unique[students.student_id]',
            'full_name' => 'required|min_length[2]',
            'username' => 'required|is_unique[students.username]',
            'department' => 'required',
            'course' => 'required',
            'year_level' => 'required',
            'section' => 'required',
            'school_year' => 'required',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
            'id_photo' => 'uploaded[id_photo]|mime_in[id_photo,image/jpg,image/jpeg,image/png]|max_size[id_photo,2048]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $latitude = $this->request->getPost('latitude');
        $longitude = $this->request->getPost('longitude');
        $latValue = is_numeric($latitude) ? floatval($latitude) : null;
        $lngValue = is_numeric($longitude) ? floatval($longitude) : null;

        if (! $this->isWithinCampusRange($latValue, $lngValue)) {
            return redirect()->back()->withInput()->with('error', 'Registration blocked: your location is outside the permitted campus access radius.');
        }

        $postFaceDescriptor = $this->request->getPost('face_descriptor');
        $postFaceVerified = $this->request->getPost('face_verified');

        if (!$postFaceDescriptor || $postFaceVerified !== '1') {
            return redirect()->back()->withInput()->with('error', 'Face verification is required and must match your ID photo.');
        }

        $normalizedFacePayload = $this->normalizeFacePayload($postFaceDescriptor);
        if ($normalizedFacePayload === null) {
            return redirect()->back()->withInput()->with('error', 'Invalid face descriptor. Please complete face verification again.');
        }

        $data = [
            'student_id' => $this->request->getPost('student_id'),
            'full_name' => $this->request->getPost('full_name'),
            'username' => $this->request->getPost('username'),
            'department' => $this->request->getPost('department'),
            'course' => $this->request->getPost('course'),
            'school_year' => $this->request->getPost('school_year'),
            'year_level' => $this->request->getPost('year_level'),
            'section' => $this->request->getPost('section'),
            'password' => $this->request->getPost('password'),
            'face_descriptor' => $normalizedFacePayload,
        ];

        // Handle ID photo upload to public/uploads
        $idPhoto = $this->request->getFile('id_photo');
        if ($idPhoto && $idPhoto->isValid() && !$idPhoto->hasMoved()) {
            $uploadPath = FCPATH . 'uploads';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $newName = $idPhoto->getRandomName();
            if ($idPhoto->move($uploadPath, $newName)) {
                $data['id_photo'] = $newName;
                $data['id_photo_hash'] = hash_file('sha256', $uploadPath . '/' . $newName);
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to upload ID photo.');
            }
        } else {
            return redirect()->back()->withInput()->with('error', 'Valid ID photo is required.');
        }

        if (!$studentModel->save($data)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $studentModel->errors()));
        }

        return redirect()->to('/login')->with('success', 'Registration successful! Please login.');
    }

    public function startFaceLogin()
    {
        $session = session();
        $studentId = $this->request->getPost('student_id');

        if (!$studentId) {
            $session->setFlashdata('error', 'Student ID not found.');
            return redirect()->back()->withInput();
        }

        $userModel = new UserModel();
        $student = $userModel->getStudent($studentId);

        if (! $student) {
            $session->setFlashdata('error', 'Student ID not found.');
            return redirect()->back()->withInput();
        }

        $isActive = (string) ($student['is_active'] ?? '') === '1';
        $hasActiveStatus = ($student['status'] ?? '') === 'Active';
        if (!$isActive && !$hasActiveStatus) {
            $session->setFlashdata('error', 'Your student account is currently inactive. Please contact the administrator.');
            return redirect()->back()->withInput();
        }

        $registeredPayload = FaceIdentity::normalizePayload($student['face_descriptor'] ?? null);
        $registeredDescriptor = $registeredPayload['descriptor'] ?? [];
        if (! FaceIdentity::isValidDescriptor($registeredDescriptor)) {
            $session->setFlashdata('error', 'Face verification needs to be re-enrolled by an administrator. Please use your password to log in.');
            return redirect()->back()->withInput();
        }

        $session->set([
            'temp_user_id'         => $student['id'],
            'temp_user_type'       => 'student',
            'temp_user_name'       => $student['full_name'],
            'temp_student_id'      => $student['student_id'],
            'temp_face_descriptor' => json_encode(['descriptor' => array_values($registeredDescriptor)])
        ]);

        return redirect()->to('/login?action=student&verify=face');
    }

    public function logout()
    {
        $session = session();
        $role = $session->get('role');
        if ($role === 'admin' || $role === 'student') {
            $logoutAt = date('Y-m-d H:i:s');
            (new AuditLogModel())->record(strtoupper($role) . ' LOGOUT', ucfirst($role) . ' logged out at ' . $logoutAt . '.', $logoutAt);
        }
        $session->destroy();
        return redirect()->to('/login');
    }

    public function faceReference()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setBody('Face reference access is restricted.');
        }

        $descriptor = (string) session()->get('temp_face_descriptor');
        $decoded = json_decode($descriptor, true);
        $legacyPaths = is_array($decoded)
            ? array_values(array_filter($decoded, 'is_string'))
            : [];
        $imageIndex = filter_var($this->request->getGet('index') ?? 0, FILTER_VALIDATE_INT);
        $legacyPath = $imageIndex !== false && $imageIndex >= 0
            ? ($legacyPaths[$imageIndex] ?? null)
            : null;
        $facesDirectory = realpath(WRITEPATH . 'uploads/faces');
        $imagePath = $legacyPath ? realpath($legacyPath) : false;

        if (!$facesDirectory || !$imagePath || strpos($imagePath, $facesDirectory . DIRECTORY_SEPARATOR) !== 0 || !is_file($imagePath)) {
            return $this->response->setStatusCode(404)->setBody('Face reference not found.');
        }

        $mimeType = mime_content_type($imagePath) ?: 'image/png';
        return $this->response->setHeader('Content-Type', $mimeType)->setBody((string) file_get_contents($imagePath));
    }

    public function saveFaceReference()
    {
        return $this->response->setStatusCode(403)->setJSON([
            'success' => false,
            'message' => 'Face descriptors can only be updated through administrator student management.',
        ]);
    }

    // API endpoint to check if Student ID is already registered
    public function checkStudentId()
    {
        $studentModel = new \App\Models\StudentModel();
        $studentId = $this->request->getJSON('student_id');

        if (!$studentId) {
            return $this->response->setJSON([
                'exists' => false,
                'has_face_registered' => false
            ]);
        }

        // Check if student exists with face registered (meaning account is complete)
        $student = $studentModel->where('student_id', $studentId)
                                ->first();

        if ($student) {
            return $this->response->setJSON([
                'exists' => true,
                'has_face_registered' => !empty($student['face_descriptor'])
            ]);
        }

        return $this->response->setJSON([
            'exists' => false,
            'has_face_registered' => false
        ]);
    }

    // NEW REGISTRATION SYSTEM
    public function register()
    {
        return $this->redirectToLogin();
    }

    public function store()
    {
        return $this->redirectToLogin();
    }

    public function redirectToLogin()
    {
        return redirect()->to('/login')->with('error', 'Student self-registration is disabled. Please contact the admin to create an account.');
    }

    public function registerNew()
    {
        return $this->redirectToLogin();
    }

    public function registerSubmit()
    {
        $studentModel = new \App\Models\StudentModel();
        
        $studentId = $this->request->getPost('student_id');
        
        // Check if student already has registered account with face
        $existingStudent = $studentModel->where('student_id', $studentId)
                                        ->where('face_descriptor IS NOT NULL', null, false)
                                        ->first();
        
        if ($existingStudent) {
            return $this->response->setJSON([
                'success' => false,
                'message' => '⚠️ This Student ID is already registered. One account per student is allowed. Please login with your existing account instead.'
            ]);
        }

        // Validation
        $rules = [
            'student_id' => 'required|is_unique[students.student_id]',
            'full_name' => 'required|min_length[2]',
            'username' => 'required|is_unique[students.username]',
            'password' => 'required|min_length[6]',
            'department' => 'required',
            'course' => 'required',
            'school_year' => 'required',
            'year_level' => 'required',
            'section' => 'required',
        ];

        $data = [
            'student_id' => $this->request->getPost('student_id'),
            'full_name' => $this->request->getPost('full_name'),
            'username' => $this->request->getPost('username'),
            'password' => $this->request->getPost('password'),
            'department' => $this->request->getPost('department'),
            'course' => $this->request->getPost('course'),
            'school_year' => $this->request->getPost('school_year'),
            'year_level' => $this->request->getPost('year_level'),
            'section' => $this->request->getPost('section'),
        ];

        // Validate
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => implode(', ', $this->validator->getErrors())
            ]);
        }

        // Handle ID Photo
        $idPhotoBase64 = $this->request->getPost('id_photo_base64');
        if ($idPhotoBase64) {
            // Save base64 as image file
            $idPhotoData = str_replace('data:image/jpeg;base64,', '', $idPhotoBase64);
            $idPhotoData = str_replace('data:image/png;base64,', '', $idPhotoData);
            $uploadPath = FCPATH . 'uploads';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $fileName = 'id_' . uniqid() . '.jpg';
            file_put_contents($uploadPath . '/' . $fileName, base64_decode($idPhotoData));
            $data['id_photo'] = $fileName;
            $data['id_photo_hash'] = hash('sha256', $idPhotoData);
        }

        // Handle Face Descriptor
        $faceDescriptor = $this->request->getPost('face_descriptor');
        if ($faceDescriptor) {
            $normalizedFacePayload = $this->normalizeFacePayload($faceDescriptor);
            $data['face_descriptor'] = $normalizedFacePayload;

            if ($this->isFaceAlreadyRegistered($normalizedFacePayload, $studentId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => '❌ SECURITY ALERT: This face is already registered to another student account. One account per student is strictly enforced.'
                ]);
            }
        }

        // Save to database
        if (!$studentModel->save($data)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Registration successful! Redirecting to login...'
        ]);
    }

    // API endpoint to check if face is already registered
    public function checkFaceRegistered()
    {
        $studentModel = new \App\Models\StudentModel();
        $inputData = $this->request->getJSON();
        $faceDescriptorJson = $inputData->face_descriptor ?? null;
        $currentStudentId = $inputData->student_id ?? null;

        if (!$faceDescriptorJson) {
            return $this->response->setJSON([
                'registered' => false,
                'message' => 'No face descriptor provided'
            ]);
        }

        $normalizedFacePayload = $this->normalizeFacePayload($faceDescriptorJson);
        if ($normalizedFacePayload === null) {
            return $this->response->setJSON([
                'registered' => false,
                'message' => 'Invalid face descriptor format'
            ]);
        }

        $query = $studentModel->where('face_descriptor IS NOT NULL', null, false)
                                        ->select('id, student_id, full_name, face_descriptor');
        
        if ($currentStudentId) {
            $query = $query->where('student_id !=', $currentStudentId);
        }
        
        $registeredFaces = $query->findAll();

        if (empty($registeredFaces)) {
            return $this->response->setJSON([
                'registered' => false,
                'message' => 'No registered faces found'
            ]);
        }

        foreach ($registeredFaces as $registered) {
            if ($this->facesMatch($normalizedFacePayload, $registered['face_descriptor'] ?? '')) {
                return $this->response->setJSON([
                    'registered' => true,
                    'message' => '⚠️ This face is already registered!',
                    'details' => 'Student ID: ' . ($registered['student_id'] ?? '') . ' (' . ($registered['full_name'] ?? '') . ')',
                    'matched_student' => $registered['student_id'] ?? null,
                ]);
            }
        }

        return $this->response->setJSON([
            'registered' => false,
            'message' => 'Face not found in database'
        ]);
    }

    private function normalizeFacePayload($payload)
    {
        if (is_array($payload)) {
            return json_encode($payload);
        }

        $decoded = json_decode((string) $payload, true);
        if (is_array($decoded)) {
            if (!empty($decoded['azure_face_id']) || !empty($decoded['descriptor'])) {
                return json_encode($decoded);
            }

            return json_encode(['descriptor' => array_values($decoded)]);
        }

        $descriptor = FaceIdentity::extractDescriptor($payload);
        if ($descriptor !== []) {
            return json_encode(['descriptor' => $descriptor]);
        }

        return null;
    }

    private function isFaceAlreadyRegistered(string $incomingPayload, ?string $currentStudentId = null): bool
    {
        $studentModel = new \App\Models\StudentModel();
        $query = $studentModel->where('face_descriptor IS NOT NULL', null, false)
            ->select('student_id, full_name, face_descriptor');

        if ($currentStudentId) {
            $query = $query->where('student_id !=', $currentStudentId);
        }

        foreach ($query->findAll() as $registered) {
            if ($this->facesMatch($incomingPayload, $registered['face_descriptor'] ?? '')) {
                return true;
            }
        }

        return false;
    }

    private function facesMatch(string $incomingPayload, string $registeredPayload, float $threshold = 0.35): bool
    {
        $incoming = json_decode($incomingPayload, true);
        $registered = json_decode($registeredPayload, true);

        if (!is_array($incoming) || !is_array($registered)) {
            return false;
        }

        if (!empty($incoming['azure_face_id']) && !empty($registered['azure_face_id']) && $incoming['azure_face_id'] === $registered['azure_face_id']) {
            return true;
        }

        $incomingDescriptor = FaceIdentity::extractDescriptor($incoming);
        $registeredDescriptor = FaceIdentity::extractDescriptor($registered);
        if ($incomingDescriptor === [] || $registeredDescriptor === []) {
            return false;
        }

        if (count($incomingDescriptor) !== count($registeredDescriptor)) {
            return false;
        }

        return FaceIdentity::descriptorsMatch($incomingDescriptor, $registeredDescriptor, $threshold);
    }
}
