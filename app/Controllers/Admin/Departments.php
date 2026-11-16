<?php
namespace App\Controllers\Admin;

use App\Models\AuditLogModel;
use App\Controllers\BaseController;
use App\Models\DepartmentModel;
use App\Models\CourseModel;

class Departments extends BaseController
{
    protected $model;

    private function getRequestData(): array
    {
        $post = $this->request->getPost();
        if (! empty($post)) {
            return $post;
        }

        try {
            $json = $this->request->getJSON(true);
            if (is_array($json)) {
                return $json;
            }
        } catch (\Exception $e) {
            return [];
        }

        return [];
    }

    public function __construct()
    {
        $this->model = new DepartmentModel();
    }

    public function index()
    {
        $data['departments'] = $this->model->orderBy('created_at', 'DESC')->findAll();
        return view('admin/departments/index', $data);
    }

    public function list()
    {
        // Return active departments for dropdowns
        $deps = $this->model->where('status', 'Active')->orderBy('department_name')->findAll();
        return $this->response->setJSON($deps);
    }

    public function all()
    {
        $courseModel = new CourseModel();
        $departments = $this->model->orderBy('department_name')->findAll();

        foreach ($departments as &$department) {
            $department['course_count'] = $courseModel
                ->where('department_id', $department['id'])
                ->countAllResults();
        }

        return $this->response->setJSON($departments);
    }

    public function store()
    {
        $data = $this->getRequestData();
        $rules = [
            'department_name' => 'required',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $departmentName = trim($data['department_name']);
        $departmentCode = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $departmentName));
        if ($departmentCode === '') {
            $departmentCode = 'DEPT';
        }

        if (! $this->model->insertWithNextId([
            'department_code' => substr($departmentCode, 0, 32),
            'department_name' => $departmentName,
            'status' => $data['status'] ?? 'Active',
        ])) {
            $errorMessage = 'Failed to add department. Database error: ' . json_encode($this->model->db->error());
            log_message('error', $errorMessage);
            error_log($errorMessage);
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Unable to add the department. Please try again or check the application logs.',
            ]);
        }

        (new AuditLogModel())->record('CREATE', 'Created department ' . $departmentName . '.');
        return $this->response->setJSON(['success' => true, 'message' => 'Department added successfully.']);
    }

    public function edit($id)
    {
        $dept = $this->model->find($id);
        return $this->response->setJSON($dept);
    }

    public function update($id)
    {
        $data = $this->getRequestData();
        $rules = [
            'department_name' => 'required',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        if (! $this->model->update($id, [
            'department_name' => trim($data['department_name']),
            'status' => $data['status'] ?? 'Active',
        ])) {
            log_message('error', 'Failed to update department {departmentId}. Database error: {error}', [
                'departmentId' => $id,
                'error' => json_encode($this->model->db->error()),
            ]);
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Unable to update the department. Please try again or check the application logs.',
            ]);
        }

        (new AuditLogModel())->record('UPDATE', 'Updated department ' . trim($data['department_name']) . '.');
        return $this->response->setJSON(['success' => true, 'message' => 'Department updated successfully.']);
    }

    public function delete($id)
    {
        $department = $this->model->find($id);
        if (! $department) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Department not found.',
            ]);
        }

        $courseModel = new CourseModel();
        $count = $courseModel->where('department_id', $id)->countAllResults();
        if ($count > 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'This department has courses assigned to it. Please remove or deactivate the courses first.']);
        }

        $db = \Config\Database::connect();
        $linkedStudents = $db->table('students')
            ->where('department_id', $id)
            ->countAllResults();

        try {
            $db->transBegin();

            if ($linkedStudents > 0) {
                $db->table('students')
                    ->where('department_id', $id)
                    ->update(['department_id' => null]);
            }

            $deleted = $db->table('departments')->where('id', $id)->delete();
            if ($deleted === false || ! $db->transStatus() || $db->affectedRows() !== 1) {
                $db->transRollback();
                log_message('error', 'Failed to delete department {departmentId}. Database error: {error}', [
                    'departmentId' => $id,
                    'error' => $db->error()['message'] ?? 'No database error details available.',
                ]);
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'The department could not be deleted. Please try again or check the application logs.',
                ]);
            }

            $db->transCommit();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $exception) {
            $db->transRollback();
            log_message(
                'error',
                'Failed to delete department {departmentId}: {error}',
                ['departmentId' => $id, 'error' => $exception->getMessage()]
            );
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'The department could not be deleted. Please try again or check the application logs.',
            ]);
        }

        $auditMessage = 'Deleted department ' . $department['department_name'] . '.';
        if ($linkedStudents > 0) {
            $auditMessage .= ' Cleared the department selection for ' . $linkedStudents . ' student account(s); saved department names were retained.';
        }
        (new AuditLogModel())->record('DELETE', $auditMessage);
        return $this->response->setJSON(['success' => true, 'message' => 'Department deleted.']);
    }

    public function toggle($id)
    {
        $dept = $this->model->find($id);
        if (! $dept) {
            return $this->response->setJSON(['success' => false]);
        }
        $new = $dept['status'] === 'Active' ? 'Inactive' : 'Active';
        $this->model->update($id, ['status' => $new]);
        (new AuditLogModel())->record('UPDATE', 'Changed department ' . $dept['department_name'] . ' status to ' . $new . '.');
        return $this->response->setJSON(['success' => true, 'status' => $new]);
    }
}
