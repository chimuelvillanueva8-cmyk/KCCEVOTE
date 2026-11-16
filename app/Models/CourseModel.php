<?php
namespace App\Models;

use CodeIgniter\Model;

class CourseModel extends Model
{
    protected $table = 'courses';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['id', 'department_id', 'course_code', 'course_name', 'status', 'created_at', 'updated_at'];

    public function insertWithNextId(array $data): bool
    {
        if (! $this->db->tableExists($this->table)) {
            return false;
        }

        if (! array_key_exists('id', $data) || $data['id'] === null || $data['id'] === '') {
            $maxId = (int) $this->db->table($this->table)
                ->selectMax('id', 'max_id')
                ->get()
                ->getRow()
                ->max_id;

            $data['id'] = $maxId + 1;
        }

        return $this->insert($data, false);
    }

    public function activeForDepartment($departmentId)
    {
        return $this->where('department_id', $departmentId)->where('status', 'Active')->findAll();
    }
}
