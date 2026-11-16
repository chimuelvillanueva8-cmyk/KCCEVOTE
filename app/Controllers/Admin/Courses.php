<?php
namespace App\Controllers\Admin;

use App\Models\AuditLogModel;
use App\Controllers\BaseController;
use App\Models\CourseModel;
use App\Models\DepartmentModel;

class Courses extends BaseController
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
        $this->model = new CourseModel();
    }

    public function index()
    {
        $deptModel = new DepartmentModel();
        $data['departments'] = $deptModel->orderBy('department_name')->findAll();
        $data['courses'] = $this->model->orderBy('created_at', 'DESC')->findAll();
        return view('admin/courses/index', $data);
    }

    public function all()
    {
        $builder = $this->model
            ->select('courses.*, departments.department_name')
            ->join('departments', 'departments.id = courses.department_id', 'left')
            ->orderBy('courses.created_at', 'DESC');

        $departmentId = $this->request->getGet('department_id');
        if ($departmentId !== null && $departmentId !== '') {
            $builder->where('courses.department_id', (int) $departmentId);
        }

        $courses = $builder->findAll();
        return $this->response->setJSON($courses);
    }

    public function store()
    {
        $data = $this->getRequestData();
        $rules = [
            'course_name' => 'required',
            'department_id' => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $this->model->insert([
            'department_id' => $data['department_id'],
            'course_name' => trim($data['course_name']),
            'status' => $data['status'] ?? 'Active',
        ]);

        (new AuditLogModel())->record('CREATE', 'Created course ' . trim($data['course_name']) . '.');
        return $this->response->setJSON(['success' => true, 'message' => 'Course added successfully.']);
    }

    public function edit($id)
    {
        $course = $this->model->find($id);
        return $this->response->setJSON($course);
    }

    public function update($id)
    {
        $data = $this->getRequestData();
        $rules = [
            'course_name' => 'required',
            'department_id' => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $this->model->update($id, [
            'department_id' => $data['department_id'],
            'course_name' => trim($data['course_name']),
            'status' => $data['status'] ?? 'Active',
        ]);

        (new AuditLogModel())->record('UPDATE', 'Updated course ' . trim($data['course_name']) . '.');
        return $this->response->setJSON(['success' => true, 'message' => 'Course updated successfully.']);
    }

    public function delete($id)
    {
        $course = $this->model->find($id);
        if (! $course) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Course not found.',
            ]);
        }

        $db = \Config\Database::connect();
        $linkedStudents = $db->table('students')
            ->where('course_id', $id)
            ->countAllResults();

        try {
            $db->transBegin();
            if ($linkedStudents > 0) {
                $db->table('students')
                    ->where('course_id', $id)
                    ->update(['course_id' => null]);
            }

            $deleted = $db->table('courses')->where('id', $id)->delete();
            if ($deleted === false || ! $db->transStatus() || $db->affectedRows() !== 1) {
                $db->transRollback();
                log_message('error', 'Failed to delete course {courseId}.', ['courseId' => $id]);
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'The course could not be deleted. Please try again or check the application logs.',
                ]);
            }

            $db->transCommit();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $exception) {
            $db->transRollback();
            log_message(
                'error',
                'Failed to delete course {courseId}: {error}',
                ['courseId' => $id, 'error' => $exception->getMessage()]
            );
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'The course could not be deleted. Please try again or check the application logs.',
            ]);
        }

        $auditMessage = 'Deleted course ' . $course['course_name'] . '.';
        if ($linkedStudents > 0) {
            $auditMessage .= ' Cleared the course selection for ' . $linkedStudents . ' student account(s); saved course names were retained.';
        }
        (new AuditLogModel())->record('DELETE', $auditMessage);
        return $this->response->setJSON(['success' => true, 'message' => 'Course deleted.']);
    }

    public function toggle($id)
    {
        $course = $this->model->find($id);
        if (! $course) {
            return $this->response->setJSON(['success' => false, 'message' => 'Course not found.']);
        }

        $newStatus = ($course['status'] === 'Active') ? 'Inactive' : 'Active';
        $this->model->update($id, ['status' => $newStatus]);

        (new AuditLogModel())->record('UPDATE', 'Changed course ' . $course['course_name'] . ' status to ' . $newStatus . '.');
        return $this->response->setJSON(['success' => true, 'status' => $newStatus]);
    }

    public function byDepartment($id)
    {
        $courses = $this->model
            ->select('courses.*, departments.department_name')
            ->join('departments', 'departments.id = courses.department_id', 'left')
            ->where('courses.department_id', $id)
            ->where('courses.status', 'Active')
            ->orderBy('courses.course_name')
            ->findAll();

        return $this->response->setJSON($courses);
    }
}
