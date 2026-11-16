<?php
namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['department_code', 'department_name', 'status', 'created_at', 'updated_at'];

    public function active()
    {
        return $this->where('status', 'Active');
    }
}
