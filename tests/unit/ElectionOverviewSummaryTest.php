<?php

namespace Tests\Unit;

use App\Models\ElectionModel;
use CodeIgniter\Test\CIUnitTestCase;

class ElectionOverviewSummaryTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $db = db_connect('tests');
        foreach (['votes', 'candidates', 'positions', 'students', 'elections'] as $table) {
            $db->query('DROP TABLE IF EXISTS db_' . $table);
        }

        $db->query(
            'CREATE TABLE db_elections ('
            . 'id INTEGER NOT NULL, title TEXT NOT NULL, status TEXT NOT NULL, manual_open INTEGER NOT NULL DEFAULT 0, '
            . 'start_time TEXT NULL, end_time TEXT NULL, access_type TEXT NOT NULL DEFAULT "all", '
            . 'allowed_students TEXT NULL, allowed_departments TEXT NULL'
            . ')'
        );
        $db->query('CREATE TABLE db_votes (election_id INTEGER NOT NULL, student_id INTEGER NOT NULL)');
        $db->query('CREATE TABLE db_positions (id INTEGER NOT NULL, election_id INTEGER NULL)');
        $db->query('CREATE TABLE db_candidates (id INTEGER NOT NULL, election_id INTEGER NULL, position_id INTEGER NULL, status TEXT NULL)');

        $db->query('INSERT INTO db_elections (id, title, status, manual_open, start_time, access_type) VALUES (1, "First", "open", 1, "2025-01-01 00:00:00", "specific_students")');
        $db->query('INSERT INTO db_elections (id, title, status, manual_open, start_time, access_type) VALUES (2, "Second", "open", 1, "2025-02-01 00:00:00", "specific_students")');
        $db->query('INSERT INTO db_positions (id, election_id) VALUES (10, 1)');
        $db->query('INSERT INTO db_positions (id, election_id) VALUES (20, 2)');
        $db->query('INSERT INTO db_candidates (id, election_id, position_id, status) VALUES (100, 1, 10, "active")');
        $db->query('INSERT INTO db_candidates (id, election_id, position_id, status) VALUES (200, NULL, 20, "inactive")');
    }

    public function testSummaryIncludesCandidateCountsForEveryElection(): void
    {
        $summary = (new ElectionModel())->getOverviewSummary();

        $this->assertCount(2, $summary);
        $this->assertSame('Second', $summary[0]['title']);
        $this->assertSame(1, $summary[0]['candidate_count']);
        $this->assertSame('First', $summary[1]['title']);
        $this->assertSame(1, $summary[1]['candidate_count']);
    }
}
