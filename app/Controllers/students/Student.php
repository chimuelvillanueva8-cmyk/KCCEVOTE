<?php

namespace App\Controllers\students;

use App\Controllers\BaseController;
use App\Libraries\FaceIdentity;
use App\Models\AuditLogModel;
use App\Models\StudentModel;
use CodeIgniter\HTTP\IncomingRequest;

class Student extends BaseController
{
    protected $studentModel;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
    }

    public function index()
    {
        return view('admin/students/index');
    }

    public function list()
    {
        $q = $this->request->getGet('q');
        $filters = [
            'department' => $this->request->getGet('department'),
            'course' => $this->request->getGet('course'),
            'year_level' => $this->request->getGet('year_level'),
            'school_year' => $this->request->getGet('school_year'),
            'section' => $this->request->getGet('section'),
            'is_active' => $this->request->getGet('is_active'),
            'face_registered' => $this->request->getGet('face_registered'),
        ];

        $builder = $this->studentModel;

        if ($q) {
            $builder = $builder->groupStart()
                ->like('student_id', $q)
                ->orLike('full_name', $q)
                ->orLike('first_name', $q)
                ->orLike('middle_name', $q)
                ->orLike('last_name', $q)
                ->orLike('department', $q)
                ->orLike('course', $q)
                ->orLike('section', $q)
                ->groupEnd();
        }

        foreach ($filters as $key => $val) {
            if ($val !== null && $val !== '') {
                if ($key === 'is_active') {
                    $builder = $builder->where('is_active', $val);
                } elseif ($key === 'department') {
                    $department = (new \App\Models\DepartmentModel())->find($val);
                    if ($department) {
                        $builder->groupStart()
                            ->where('department_id', $department['id'])
                            ->orWhere('department', $department['department_name'])
                            ->groupEnd();
                    } else {
                        $builder->where('id', 0);
                    }
                } elseif ($key === 'course') {
                    $course = (new \App\Models\CourseModel())->find($val);
                    if ($course) {
                        $builder->groupStart()
                            ->where('course_id', $course['id'])
                            ->orWhere('course', $course['course_name'])
                            ->groupEnd();
                    } else {
                        $builder->where('id', 0);
                    }
                } else {
                    $builder = $builder->where($key, $val);
                }
            }
        }

        $perPage = (int) ($this->request->getGet('per_page') ?? 25);
        $page = (int) ($this->request->getGet('page') ?? 1);

        $total = $builder->countAllResults(false);
        $students = $builder->orderBy('created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->findAll();

        // Never expose password hashes in the normal student list response.
        $deptModel = new \App\Models\DepartmentModel();
        $courseModel = new \App\Models\CourseModel();
        foreach ($students as &$student) {
            if (!empty($student['department_id'])) {
                $dept = $deptModel->find($student['department_id']);
                if ($dept) {
                    $student['department'] = $dept['department_name'] ?? $dept['department_code'] ?? $student['department'] ?? '';
                    $student['department_name'] = $student['department'];
                }
            }
            if (!empty($student['course_id'])) {
                $course = $courseModel->find($student['course_id']);
                if ($course) {
                    $student['course'] = $course['course_name'] ?? $course['course_code'] ?? $student['course'] ?? '';
                    $student['course_name'] = $student['course'];
                }
            }
            unset($student['password'], $student['face_descriptor'], $student['id_photo_hash'], $student['failed_login_attempts'], $student['locked_until']);
        }

        // DEBUG: log total and sample
        log_message('debug', 'Students list total: ' . intval($total));
        log_message('debug', 'Students list sample: ' . json_encode(array_slice($students, 0, 5)));

        // provide filter options (distinct values)
        $years = $this->studentModel->distinct()->select('year_level')->where('year_level IS NOT NULL', null, false)->orderBy('year_level','ASC')->findColumn('year_level');
        $schoolYears = $this->studentModel->distinct()->select('school_year')
            ->where('school_year IS NOT NULL', null, false)
            ->where('school_year !=', '')
            ->orderBy('school_year', 'ASC')
            ->findColumn('school_year');
        $sections = $this->studentModel->distinct()->select('section')->where('section IS NOT NULL', null, false)->orderBy('section','ASC')->findColumn('section');

        $summaryModel = new StudentModel();
        $summaryTotal = $summaryModel->countAllResults();
        $activeCount = $summaryModel->where('is_active', 1)->countAllResults();
        $inactiveCount = $summaryModel->where('is_active', 0)->countAllResults();
        $faceRegisteredCount = $summaryModel->where('face_registered', 1)->countAllResults();

        return $this->response->setJSON([
            'data' => $students,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'summary' => [
                'total' => $summaryTotal,
                'active' => $activeCount,
                'inactive' => $inactiveCount,
                'face_registered' => $faceRegisteredCount,
            ],
            'filters' => [
                'years' => array_values(array_filter($years)),
                'school_years' => array_values(array_filter($schoolYears)),
                'sections' => array_values(array_filter($sections)),
            ],
        ]);
    }

    public function store()
    {
        $data = $this->request->getPost();
        unset($data['id']);

        // sanitize student_id to digits only
        if (!empty($data['student_id'])) {
            $data['student_id'] = preg_replace('/\D+/', '', $data['student_id']);
        }

        // Build full_name if not provided
        if (empty($data['full_name']) && (!empty($data['first_name']) || !empty($data['last_name']))) {
            $data['full_name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['middle_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        }

        // Sync department and course names if department_id or course_id is provided
        if (!empty($data['department_id'])) {
            $deptModel = new \App\Models\DepartmentModel();
            $dept = $deptModel->find($data['department_id']);
            if ($dept) {
                $data['department'] = $dept['department_name'] ?? $dept['department_code'] ?? '';
            }
        }
        if (!empty($data['course_id'])) {
            $courseModel = new \App\Models\CourseModel();
            $course = $courseModel->find($data['course_id']);
            if ($course) {
                $data['course'] = $course['course_name'] ?? $course['course_code'] ?? '';
            }
        }

        // auto-generate username and password, but use the client-generated password when provided
        $data['username'] = $data['student_id'] ?? '';
        $plainPassword = $data['password'] ?? $this->generatePassword();
        $data['password'] = $plainPassword;
        $data['is_active'] = $data['is_active'] ?? 1;
        $data['status'] = ((int) $data['is_active'] === 1) ? 'Active' : 'Inactive';

        try {
            $id = $this->insertStudentWithAllocatedId($data);
        } catch (\Throwable $exception) {
            log_message('error', 'Student registration failed: ' . $exception->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Unable to save the student right now. Please try again.',
            ]);
        }

        if ($id !== false) {
            (new AuditLogModel())->record('CREATE', 'Created student account ' . $data['student_id'] . '.');
            return $this->response->setJSON(['success' => true, 'id' => $id, 'password' => $plainPassword]);
        }

        $errors = $this->studentModel->errors();
        if ($errors === []) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Unable to save the student right now. Please try again.',
            ]);
        }

        return $this->response->setJSON(['success' => false, 'errors' => $errors]);
    }

    protected function insertStudentWithAllocatedId(array $data)
    {
        $db = $this->studentModel->db;
        if (! $db->transBegin()) {
            throw new \RuntimeException('Unable to start student ID allocation transaction.');
        }

        try {
            $allocator = $db->query(
                'SELECT next_id FROM student_id_allocator WHERE allocator_id = 1 FOR UPDATE'
            )->getRowArray();
            if (! $allocator) {
                throw new \RuntimeException('Student ID allocator is not initialized.');
            }

            $maxStudentId = (int) $db->table('students')
                ->selectMax('id', 'max_id')
                ->get()
                ->getRow()
                ->max_id;
            $id = max((int) $allocator['next_id'], $maxStudentId + 1);
            if ($id > 4294967295) {
                throw new \RuntimeException('Student IDs have exceeded the supported integer range.');
            }

            if ($db->table('student_id_allocator')
                ->where('allocator_id', 1)
                ->update(['next_id' => $id + 1]) === false) {
                throw new \RuntimeException('Unable to reserve a student ID: ' . json_encode($db->error()));
            }

            $data['id'] = $id;
            if (! $this->studentModel->insert($data, false)) {
                $db->transRollback();
                return false;
            }

            if (! $db->transStatus()) {
                throw new \RuntimeException('Student registration transaction failed.');
            }
            if (! $db->transCommit()) {
                throw new \RuntimeException('Student registration transaction could not be committed.');
            }

            return $id;
        } catch (\Throwable $exception) {
            $db->transRollback();
            throw $exception;
        }
    }

    public function update($id)
    {
        $data = $this->request->getPost();
        unset($data['id']);
        $existing = $this->studentModel->find($id);
        if (!$existing) {
            return $this->response->setJSON(['success' => false, 'errors' => ['Student not found.']]);
        }

        // sanitize student_id to digits only if provided
        if (!empty($data['student_id'])) {
            $data['student_id'] = preg_replace('/\D+/', '', $data['student_id']);
            $data['username'] = $data['student_id'];
        }

        // Build full_name if not provided
        if (empty($data['full_name']) && (!empty($data['first_name']) || !empty($data['last_name']))) {
            $data['full_name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['middle_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        }

        // Sync department and course names if department_id or course_id is provided
        if (!empty($data['department_id'])) {
            $deptModel = new \App\Models\DepartmentModel();
            $dept = $deptModel->find($data['department_id']);
            if ($dept) {
                $data['department'] = $dept['department_name'] ?? $dept['department_code'] ?? '';
            }
        }
        if (!empty($data['course_id'])) {
            $courseModel = new \App\Models\CourseModel();
            $course = $courseModel->find($data['course_id']);
            if ($course) {
                $data['course'] = $course['course_name'] ?? $course['course_code'] ?? '';
            }
        }

        if (isset($data['is_active']) && (string) $data['is_active'] !== (string) ($existing['is_active'] ?? '')) {
            $data['password'] = $this->generatePassword();
            $data['must_change_password'] = 1;
        }

        if (isset($data['is_active'])) {
            $data['status'] = ((int) $data['is_active'] === 1) ? 'Active' : 'Inactive';
        }

        // Configure validation rules to ignore current ID for unique checks
        $rules = $this->studentModel->getValidationRules();
        $rules['student_id'] = "required|is_natural_no_zero|is_unique[students.student_id,id,{$id}]";
        $rules['username'] = "permit_empty|is_unique[students.username,id,{$id}]";
        $this->studentModel->setValidationRules($rules);

        if ($this->studentModel->update($id, $data)) {
            (new AuditLogModel())->record('UPDATE', 'Updated student account ' . $existing['student_id'] . '.');
            return $this->response->setJSON(['success' => true]);
        }
        return $this->response->setJSON(['success' => false, 'errors' => $this->studentModel->errors()]);
    }

    public function delete($id)
    {
        $student = $this->studentModel->find($id);
        if ($this->studentModel->delete($id)) {
            if ($student) {
                (new AuditLogModel())->record('DELETE', 'Deleted student account ' . $student['student_id'] . '.');
            }
            return $this->response->setJSON(['success' => true]);
        }
        return $this->response->setJSON(['success' => false]);
    }

    public function bulkAction()
    {
        $action = $this->request->getPost('action');
        $ids = $this->request->getPost('ids') ?? [];
        if (!is_array($ids)) $ids = json_decode($ids, true) ?: [];

        if (empty($ids)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No students selected']);
        }

        switch ($action) {
            case 'activate':
                $passwords = [];
                foreach ($ids as $id) {
                    $newPassword = $this->generatePassword();
                    $this->studentModel->update($id, ['is_active' => 1, 'status' => 'Active', 'password' => $newPassword, 'must_change_password' => 1]);
                    $passwords[$id] = $newPassword;
                }
                (new AuditLogModel())->record('UPDATE', 'Activated ' . count($ids) . ' student account(s).');
                return $this->response->setJSON(['success' => true, 'passwords' => $passwords]);
            case 'deactivate':
                foreach ($ids as $id) {
                    $this->studentModel->update($id, ['is_active' => 0, 'status' => 'Inactive']);
                }
                (new AuditLogModel())->record('UPDATE', 'Deactivated ' . count($ids) . ' student account(s).');
                return $this->response->setJSON(['success' => true]);
            case 'delete':
                foreach ($ids as $id) {
                    $this->studentModel->delete($id);
                }
                (new AuditLogModel())->record('DELETE', 'Deleted ' . count($ids) . ' student account(s).');
                break;
        }

        return $this->response->setJSON(['success' => true]);
    }

    public function uploadFace($id)
    {
        $payload = FaceIdentity::normalizePayload($this->request->getPost('face_descriptor'));
        $descriptor = $payload['descriptor'] ?? [];
        if (! FaceIdentity::isValidDescriptor($descriptor)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'A valid face descriptor is required.',
            ]);
        }

        if (! $this->studentModel->find($id)) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Student not found.',
            ]);
        }

        $faceImages = $this->request->getPost('faceImages');
        $saved = [];
        if ($faceImages && is_array($faceImages)) {
            $uploadDir = WRITEPATH . 'uploads/faces/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            foreach ($faceImages as $idx => $dataUrl) {
                if (preg_match('/^data:image\/png;base64,/', $dataUrl)) {
                    $imgData = base64_decode(preg_replace('#^data:image/[^;]+;base64,#', '', $dataUrl));
                    $fname = $uploadDir . $id . '_' . uniqid() . '.png';
                    file_put_contents($fname, $imgData);
                    $saved[] = $fname;
                }
            }
        }

        if (!empty($saved)) {
            if (! $this->studentModel->update($id, [
                'face_registered' => 1,
                'face_descriptor' => json_encode(['descriptor' => array_values($descriptor)]),
                'face_enrolled_at' => date('Y-m-d H:i:s')
            ])) {
                log_message('error', 'Unable to save administrator-enrolled face descriptor.');
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Face enrollment could not be saved.',
                ]);
            }

            (new AuditLogModel())->record('UPDATE', 'Updated face registration for student account ' . $id . '.');
            return $this->response->setJSON(['success' => true, 'files' => $saved]);
        }

        return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'No face images received']);
    }

    public function resetPassword($id)
    {
        $plain = $this->generatePassword();
        if ($this->studentModel->update($id, ['password' => $plain, 'must_change_password' => 1])) {
            (new AuditLogModel())->record('UPDATE', 'Reset password for student account ' . $id . '.');
            return $this->response->setJSON(['success' => true, 'password' => $plain]);
        }
        return $this->response->setJSON(['success' => false]);
    }

    protected function appendImportRow(array &$rows, array $headers, array $values): void
    {
        if ($headers === []) {
            return;
        }

        $columnCount = count($headers);
        $values = array_slice(array_pad($values, $columnCount, ''), 0, $columnCount);
        if (count(array_filter($values, static fn ($value) => trim((string) $value) !== '')) === 0) {
            return;
        }

        $rows[] = array_combine($headers, $values);
    }

    protected function importFieldForHeader(string $header, array $fieldMap): ?string
    {
        $key = strtolower(trim($header));
        $key = trim((string) preg_replace('/[^a-z0-9]+/', ' ', $key));
        if (isset($fieldMap[$key])) {
            return $fieldMap[$key];
        }

        $compactKey = str_replace(' ', '', $key);
        if (
            (str_contains($compactKey, 'student') && preg_match('/(id|number|num|no|code)/', $compactKey))
            || in_array($compactKey, ['idnumber', 'idnum', 'idno', 'schoolid', 'lrn'], true)
        ) {
            return 'student_id';
        }

        return null;
    }

    public function import()
    {
        $file = $this->request->getFile('student_file');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Please upload a valid CSV file.']);
        }

        $ext = strtolower(pathinfo($file->getClientName(), PATHINFO_EXTENSION));
        $path = $file->getTempName();
        $rows = [];

        if (in_array($ext, ['csv', 'txt'])) {
            $handle = fopen($path, 'r');
            if (!$handle) {
                return $this->response->setJSON(['success' => false, 'message' => 'Unable to read uploaded file.']);
            }
            $headers = fgetcsv($handle);
            if ($headers === false) {
                fclose($handle);
                return $this->response->setJSON(['success' => false, 'message' => 'Uploaded file is empty or invalid.']);
            }
            $headers = array_map(static fn ($header) => trim((string) $header), $headers);
            if (isset($headers[0])) {
                $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
            }
            while (($line = fgetcsv($handle)) !== false) {
                $this->appendImportRow($rows, $headers, $line);
            }
            fclose($handle);
        } elseif (in_array($ext, ['xlsx', 'xls'])) {
            if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
                return $this->response->setJSON(['success' => false, 'message' => 'Excel import requires PhpSpreadsheet. Please upload CSV instead or install PhpSpreadsheet.']);
            }
            if (!extension_loaded('zip') || !class_exists('ZipArchive')) {
                return $this->response->setJSON(['success' => false, 'message' => 'Excel import requires the PHP Zip extension (ZipArchive). Please enable ext-zip in php.ini and restart Apache.']);
            }
            if (!extension_loaded('gd')) {
                return $this->response->setJSON(['success' => false, 'message' => 'Excel import requires the PHP GD extension. Please enable ext-gd in php.ini and restart Apache.']);
            }
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $sheetRows = $sheet->toArray(null, true, true, false);
            $headerRow = array_map(
                static fn ($header) => trim((string) $header),
                array_shift($sheetRows) ?? []
            );
            foreach ($sheetRows as $rowValues) {
                $this->appendImportRow($rows, $headerRow, $rowValues);
            }
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Unsupported file type. Use CSV.']);
        }

        $fieldMap = [
            'student id' => 'student_id',
            'student id number' => 'student_id',
            'student id no' => 'student_id',
            'student id num' => 'student_id',
            'student number' => 'student_id',
            'student no' => 'student_id',
            'student num' => 'student_id',
            'roll number' => 'student_id',
            'roll no' => 'student_id',
            'registration number' => 'student_id',
            'registration no' => 'student_id',
            'enrollment number' => 'student_id',
            'serial number' => 'student_id',
            'sr no' => 'student_id',
            'studentid' => 'student_id',
            'id number' => 'student_id',
            'id no' => 'student_id',
            'id num' => 'student_id',
            'school id' => 'student_id',
            'lrn' => 'student_id',
            'id' => 'student_id',
            'first name' => 'first_name',
            'firstname' => 'first_name',
            'middle name' => 'middle_name',
            'middlename' => 'middle_name',
            'middle initial' => 'middle_name',
            'last name' => 'last_name',
            'lastname' => 'last_name',
            'full name' => 'full_name',
            'fullname' => 'full_name',
            'student name' => 'full_name',
            'name' => 'full_name',
            'email' => 'email',
            'department' => 'department',
            'department name' => 'department',
            'department id' => 'department_id',
            'course' => 'course',
            'course name' => 'course',
            'course id' => 'course_id',
            'school year' => 'school_year',
            'academic year' => 'school_year',
            'year level' => 'year_level',
            'yearlevel' => 'year_level',
            'year' => 'year_level',
            'section' => 'section',
            'status' => 'status',
        ];

        $imported = 0;
        $updated = 0;
        $errors = [];
        $departmentsById = [];
        $departmentsByName = [];
        foreach ((new \App\Models\DepartmentModel())->findAll() as $department) {
            $departmentsById[(string) $department['id']] = $department;
            $departmentsByName[strtolower(trim((string) $department['department_name']))] = $department;
        }
        $coursesById = [];
        $coursesByDepartmentAndName = [];
        $coursesByName = [];
        foreach ((new \App\Models\CourseModel())->findAll() as $course) {
            $coursesById[(string) $course['id']] = $course;
            $courseName = strtolower(trim((string) $course['course_name']));
            $coursesByDepartmentAndName[(string) $course['department_id'] . '|' . $courseName] = $course;
            $coursesByName[$courseName] ??= $course;
        }

        foreach ($rows as $index => $row) {
            $record = [];
            foreach ($row as $header => $value) {
                $field = $this->importFieldForHeader((string) $header, $fieldMap);
                if ($field) {
                    $record[$field] = trim((string) $value);
                }
            }

            if (isset($record['student_id']) && preg_match('/^\d+\.0+$/', $record['student_id'])) {
                $record['student_id'] = strstr($record['student_id'], '.', true);
            }
            if (isset($record['student_id'])) {
                $record['student_id'] = preg_replace('/\D+/', '', $record['student_id']);
            }

            if (empty($record['student_id'])) {
                $errors[] = 'Row ' . ($index + 2) . ': Missing student_id.';
                continue;
            }

            $department = !empty($record['department_id'])
                ? ($departmentsById[(string) $record['department_id']] ?? null)
                : null;
            if (!$department && !empty($record['department'])) {
                $department = $departmentsByName[strtolower(trim($record['department']))] ?? null;
            }
            if ($department) {
                $record['department_id'] = $department['id'];
                $record['department'] = $department['department_name'];
            }

            $course = !empty($record['course_id'])
                ? ($coursesById[(string) $record['course_id']] ?? null)
                : null;
            if (!$course && !empty($record['course'])) {
                $courseName = strtolower(trim($record['course']));
                $departmentId = $department['id'] ?? ($record['department_id'] ?? '');
                $course = $coursesByDepartmentAndName[(string) $departmentId . '|' . $courseName]
                    ?? ($coursesByName[$courseName] ?? null);
            }
            if ($course) {
                $record['course_id'] = $course['id'];
                $record['course'] = $course['course_name'];
                if (!$department && !empty($course['department_id'])) {
                    $record['department_id'] = $course['department_id'];
                    $department = $departmentsById[(string) $course['department_id']] ?? null;
                    if ($department) {
                        $record['department'] = $department['department_name'];
                    }
                }
            }

            if (empty($record['full_name'])) {
                $record['full_name'] = trim(($record['first_name'] ?? '') . ' ' . ($record['middle_name'] ?? '') . ' ' . ($record['last_name'] ?? ''));
            }

            if (empty($record['full_name'])) {
                $errors[] = 'Row ' . ($index + 2) . ': Missing full name.';
                continue;
            }

            if (!isset($record['status'])) {
                $record['status'] = 'Active';
            }
            $record['is_active'] = stripos((string) $record['status'], 'inactive') !== false ? 0 : 1;
            $record['username'] = $record['student_id'];

            $existing = $this->studentModel->where('student_id', $record['student_id'])->first();
            if ($existing) {
                $rules = $this->studentModel->getValidationRules();
                $originalRules = $rules;
                $rules['student_id'] = "required|is_natural_no_zero|is_unique[students.student_id,id,{$existing['id']}]";
                $rules['username'] = "permit_empty|is_unique[students.username,id,{$existing['id']}]";
                $this->studentModel->setValidationRules($rules);
                try {
                    if ($this->studentModel->update($existing['id'], $record)) {
                        $updated++;
                    } else {
                        $validationErrors = $this->studentModel->errors();
                        $errors[] = 'Row ' . ($index + 2) . ': '
                            . ($validationErrors ? implode(' ', $validationErrors) : 'Unable to update student.');
                    }
                } finally {
                    $this->studentModel->setValidationRules($originalRules);
                }
            } else {
                if (empty($record['password'])) {
                    $record['password'] = $this->generatePassword();
                }
                try {
                    if ($this->insertStudentWithAllocatedId($record) !== false) {
                        $imported++;
                    } else {
                        $validationErrors = $this->studentModel->errors();
                        $errors[] = 'Row ' . ($index + 2) . ': '
                            . ($validationErrors ? implode(' ', $validationErrors) : 'Unable to save student.');
                    }
                } catch (\Throwable $exception) {
                    log_message('error', 'Student import insert failed for row ' . ($index + 2) . ': ' . $exception->getMessage());
                    $errors[] = 'Row ' . ($index + 2) . ': Unable to save student.';
                }
            }
        }

        $processed = $imported + $updated;
        $message = null;
        if ($processed === 0) {
            $message = $errors
                ? 'No students were imported. ' . implode(' ', array_slice($errors, 0, 5))
                : 'No student rows were found in the uploaded file.';
        }

        return $this->response->setJSON([
            'success' => $processed > 0,
            'message' => $message,
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
        ]);
    }

    protected function generatePassword($length = 10)
    {
        $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits  = '0123456789';
        $chars   = $letters . $digits;

        // Ensure at least one letter and at least one digit
        $pwd = [
            $letters[random_int(0, strlen($letters) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
        ];

        $max = strlen($chars) - 1;
        for ($i = 2; $i < $length; $i++) {
            $pwd[] = $chars[random_int(0, $max)];
        }

        shuffle($pwd);
        return implode('', $pwd);
    }
}
