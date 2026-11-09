<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TestStudentSeeder extends Seeder
{
    public function run()
    {
        if (ENVIRONMENT === 'production') {
            throw new \RuntimeException('TestStudentSeeder may not be run in production.');
        }

        $data = [
            'student_id' => 'STU002',
            'full_name' => 'Test Student',
            'id_name' => 'TS',
            'course' => 'Computer Science',
            'school_year' => '2024-2025',
            'year_level' => '3rd Year',
            'username' => 'teststudent',
            'password' => '$2y$10$4X1iqickzCh1fhVur86aJuIGaffMAwgk6lXQg.AH6xsCpAEsAZK5m', // password: student123
            'department' => 'IT',
            'section' => 'A'
        ];

        $this->db->table('students')->insert($data);
    }
}
