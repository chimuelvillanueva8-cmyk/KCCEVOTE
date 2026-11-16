<?php
namespace App\Models;

use CodeIgniter\Model;

class CourseModel extends Model
{
    protected $table = 'courses';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['department_id', 'course_code', 'course_name', 'status', 'created_at', 'updated_at'];

    public function activeForDepartment($departmentId)
    {
        return $this->where('department_id', $departmentId)->where('status', 'Active')->findAll();
    }
}
