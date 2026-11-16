<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table = 'admins';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['id', 'admin_id_number', 'is_builtin_admin', 'username', 'full_name', 'password', 'created_at'];

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
}
