<?php

namespace Tests\Unit;

use App\Models\ElectionModel;
use CodeIgniter\Test\CIUnitTestCase;

class ElectionModelTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $db = db_connect('tests');
        $db->query('DROP TABLE IF EXISTS db_elections');
        $db->query(
            'CREATE TABLE db_elections ('
            . 'id INTEGER NOT NULL PRIMARY KEY,'
            . 'title TEXT NOT NULL,'
            . 'description TEXT NULL,'
            . 'academic_year TEXT NULL,'
            . 'status TEXT NOT NULL,'
            . 'manual_open INTEGER NOT NULL DEFAULT 0,'
            . 'start_time TEXT NULL,'
            . 'end_time TEXT NULL,'
            . 'access_type TEXT NULL,'
            . 'allowed_departments TEXT NULL,'
            . 'allowed_students TEXT NULL'
            . ')'
        );
    }

    public function testInsertAssignsUniqueIdsWhenAutoIncrementIsMissing(): void
    {
        $model = new ElectionModel();

        $this->assertTrue($model->insertWithNextId([
            'title' => 'Election One',
            'status' => 'pending',
        ]));
        $this->assertTrue($model->insertWithNextId([
            'title' => 'Election Two',
            'status' => 'pending',
        ]));

        $ids = db_connect('tests')->table('db_elections')->orderBy('id')->get()->getResultArray();
        $this->assertSame([1, 2], array_map('intval', array_column($ids, 'id')));
    }
}
