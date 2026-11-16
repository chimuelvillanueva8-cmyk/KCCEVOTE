<?php

namespace Tests\Unit;

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
}
