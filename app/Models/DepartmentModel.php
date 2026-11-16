<?php
namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['id', 'department_code', 'department_name', 'status', 'created_at', 'updated_at'];

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

    public function active()
    {
        return $this->where('status', 'Active');
    }
}
