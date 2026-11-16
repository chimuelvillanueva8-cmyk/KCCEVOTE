<?php

namespace Tests\Unit;

use App\Models\AdminModel;
use App\Models\CourseModel;
use App\Models\DepartmentModel;
use CodeIgniter\Test\CIUnitTestCase;

class DepartmentCourseModelTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $db = db_connect('tests');
        $db->query('DROP TABLE IF EXISTS db_courses');
        $db->query('DROP TABLE IF EXISTS db_departments');

        $db->query(
            'CREATE TABLE db_departments ('
            . 'id INTEGER NOT NULL,'
            . 'department_code TEXT NOT NULL,'
            . 'department_name TEXT NOT NULL,'
            . 'status TEXT NOT NULL DEFAULT "Active",'
            . 'created_at TEXT NULL,'
            . 'updated_at TEXT NULL'
            . ')'
        );

        $db->query(
            'CREATE TABLE db_courses ('
            . 'id INTEGER NOT NULL,'
            . 'department_id INTEGER NOT NULL,'
            . 'course_code TEXT NOT NULL,'
            . 'course_name TEXT NOT NULL,'
            . 'status TEXT NOT NULL DEFAULT "Active",'
            . 'created_at TEXT NULL,'
            . 'updated_at TEXT NULL'
            . ')'
        );

        $db->query(
            'CREATE TABLE db_admins ('
            . 'id INTEGER NOT NULL,'
            . 'admin_id_number TEXT NOT NULL,'
            . 'is_builtin_admin INTEGER NOT NULL DEFAULT 0,'
            . 'username TEXT NOT NULL,'
            . 'full_name TEXT NULL,'
            . 'password TEXT NOT NULL,'
            . 'created_at TEXT NULL'
            . ')'
        );
    }

    public function testDepartmentInsertUsesNextIdWhenAutoIncrementIsMissing(): void
    {
        $model = new DepartmentModel();

        $result = $model->insertWithNextId([
            'department_code' => 'TEST',
            'department_name' => 'Testing',
            'status' => 'Active',
        ]);

        $this->assertNotFalse($result);

        $db = db_connect('tests');
        $row = $db->query('SELECT MAX(id) AS max_id FROM db_departments')->getRow();
        $this->assertSame(1, (int) ($row->max_id ?? 0));
    }

    public function testCourseInsertUsesNextIdWhenAutoIncrementIsMissing(): void
    {
        $model = new CourseModel();

        $result = $model->insertWithNextId([
            'department_id' => 1,
            'course_code' => 'TEST',
            'course_name' => 'Testing',
            'status' => 'Active',
        ]);

        $this->assertNotFalse($result);

        $db = db_connect('tests');
        $row = $db->query('SELECT MAX(id) AS max_id FROM db_courses')->getRow();
        $this->assertSame(1, (int) ($row->max_id ?? 0));
    }

    public function testAdminInsertUsesNextIdWhenAutoIncrementIsMissing(): void
    {
        $model = new AdminModel();

        $result = $model->insertWithNextId([
            'admin_id_number' => 'ADM-1001',
            'is_builtin_admin' => 0,
            'username' => 'admin_test',
            'full_name' => 'Test Admin',
            'password' => password_hash('secret123', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assertNotFalse($result);

        $db = db_connect('tests');
        $row = $db->query('SELECT MAX(id) AS max_id FROM db_admins')->getRow();
        $this->assertSame(1, (int) ($row->max_id ?? 0));
    }
}
