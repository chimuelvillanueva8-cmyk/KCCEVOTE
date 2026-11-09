<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentModel extends Model
{
    protected $table      = 'students';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'student_id',
        'full_name',
        'first_name',
        'last_name',
        'middle_name',
        'id_name',
        'department',
        'course',
        'school_year',
        'year_level',
        'section',
        'email',
        'mobile',
        'middle_initial',
        'password_hash',
        'department_id',
        'course_id',
        'username',
        'password',
        'face_descriptor',
        'face_registered',
        'id_photo',
        'id_photo_hash',
        'is_active',
        'status',
        'face_enrolled_at',
        'must_change_password',
        'failed_login_attempts',
        'locked_until',
        'last_login_at'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'student_id' => 'required|is_natural_no_zero|is_unique[students.student_id]',
        'department_id' => 'permit_empty|integer',
        'course_id' => 'permit_empty|integer',
        'full_name'  => 'required|min_length[2]',
        'username'   => 'permit_empty|is_unique[students.username]',
        'password'   => 'permit_empty|min_length[6]',
        'department' => 'permit_empty',
        'course'     => 'permit_empty',
        'school_year' => 'permit_empty',
        'year_level'  => 'permit_empty',
        'section'    => 'permit_empty',
        'email'      => 'permit_empty|valid_email',
        'status'     => 'permit_empty|in_list[Active,Inactive]',
    ];

    protected $validationMessages = [
        'student_id' => [
            'required' => 'Student ID is required.',
            'is_natural_no_zero' => 'Student ID must be a positive integer.',
            'is_unique' => 'This Student ID is already taken.',
        ],
        'full_name' => [
            'required' => 'Full name is required.',
            'min_length' => 'Full name must be at least 2 characters.',
        ],
        'username' => [
            'is_unique' => 'This username is already taken.',
        ],
        'password' => [
            'required' => 'Password is required.',
            'min_length' => 'Password must be at least 6 characters.',
        ],
    ];

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (!empty($data['data']['password'])) {
            $password = $data['data']['password'];
            if (!preg_match('/^\$2[axy]\$/', $password)) {
                $data['data']['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
        }
        return $data;
    }

    public function verifyPassword($password, $storedPassword): bool
    {
        if (! is_string($password) || ! is_string($storedPassword) || $storedPassword === '') {
            return false;
        }

        if (password_verify($password, $storedPassword)) {
            return true;
        }

        return hash_equals($storedPassword, $password);
    }

    public function getDepartments()
    {
        return $this->distinct()
                    ->select('department')
                    ->where('department IS NOT NULL', null, false)
                    ->orderBy('department', 'ASC')
                    ->findColumn('department');
    }
}