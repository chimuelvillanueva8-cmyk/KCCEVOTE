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

        $this->model->insert([
            'department_name' => trim($data['department_name']),
            'status' => $data['status'] ?? 'Active',
        ]);

        (new AuditLogModel())->record('CREATE', 'Created department ' . trim($data['department_name']) . '.');
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

        $this->model->update($id, [
            'department_name' => trim($data['department_name']),
            'status' => $data['status'] ?? 'Active',
        ]);

        (new AuditLogModel())->record('UPDATE', 'Updated department ' . trim($data['department_name']) . '.');
        return $this->response->setJSON(['success' => true, 'message' => 'Department updated successfully.']);
    }

    public function delete($id)
    {
        // prevent delete if courses exist
        $courseModel = new CourseModel();
        $count = $courseModel->where('department_id', $id)->countAllResults();
        if ($count > 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'This department has courses assigned to it. Please remove or deactivate the courses first.']);
        }

        $department = $this->model->find($id);
        $this->model->delete($id);
        if ($department) {
            (new AuditLogModel())->record('DELETE', 'Deleted department ' . $department['department_name'] . '.');
        }
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
