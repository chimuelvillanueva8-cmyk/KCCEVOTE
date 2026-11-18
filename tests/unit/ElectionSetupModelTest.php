<?php

namespace Tests\Unit;

use App\Models\CandidateModel;
use App\Models\PartyModel;
use App\Models\PositionModel;
use CodeIgniter\Test\CIUnitTestCase;

class ElectionSetupModelTest extends CIUnitTestCase
{
    private $testDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDatabase = db_connect('tests', false);
        foreach (['candidates', 'parties', 'positions'] as $table) {
            $this->testDatabase->query('DROP TABLE IF EXISTS db_' . $table);
        }

        $this->testDatabase->query(
            'CREATE TABLE db_positions ('
            . 'id INTEGER NOT NULL PRIMARY KEY, election_id INTEGER NULL, title TEXT NOT NULL, '
            . 'sort_order INTEGER NOT NULL, description TEXT NULL, votes_required INTEGER NOT NULL, '
            . 'status TEXT NULL, created_at TEXT NULL, updated_at TEXT NULL'
            . ')'
        );
        $this->testDatabase->query(
            'CREATE TABLE db_parties ('
            . 'id INTEGER NOT NULL PRIMARY KEY, name TEXT NOT NULL, election_id INTEGER NULL, '
            . 'acronym TEXT NULL, description TEXT NULL, logo TEXT NULL, status TEXT NULL, '
            . 'created_at TEXT NULL, updated_at TEXT NULL'
            . ')'
        );

        $this->testDatabase->query(
            'CREATE TABLE db_candidates ('
            . 'id INTEGER NOT NULL PRIMARY KEY, student_id TEXT NULL, full_name TEXT NOT NULL, '
            . 'party_id INTEGER NULL, position_id INTEGER NOT NULL, election_id INTEGER NOT NULL, '
            . 'photo TEXT NULL, status TEXT NULL, eligible_year_levels TEXT NULL, '
            . 'created_at TEXT NULL, updated_at TEXT NULL'
            . ')'
        );

        $this->testDatabase->query("INSERT INTO db_positions (id, election_id, title, sort_order, votes_required) VALUES (7, 1, 'Existing', 1, 1)");
        $this->testDatabase->query("INSERT INTO db_parties (id, name, election_id) VALUES (7, 'Existing Party', 1)");
        $this->testDatabase->query("INSERT INTO db_candidates (id, full_name, position_id, election_id) VALUES (7, 'Existing Candidate', 1, 1)");
    }

    public function testElectionSetupModelsAllocateAndReturnIdsWithoutAutoIncrement(): void
    {
        $position = new PositionModel($this->testDatabase);
        $positionSaved = $position->insertWithNextId([
            'election_id' => 1,
            'title' => 'President',
            'sort_order' => 1,
            'votes_required' => 1,
            'status' => 'active',
        ]);
        $this->assertTrue(
            $positionSaved,
            json_encode($position->errors())
        );
        $this->assertSame(8, $position->getInsertID());

        $party = new PartyModel($this->testDatabase);
        $partySaved = $party->insertWithNextId([
            'name' => 'Party A',
            'election_id' => 1,
            'status' => 'active',
        ]);
        $this->assertTrue($partySaved, json_encode($party->errors()));
        $this->assertSame(8, $party->getInsertID());

        $candidate = new CandidateModel($this->testDatabase);
        $candidateSaved = $candidate->insertWithNextId([
            'full_name' => 'Candidate One',
            'party_id' => 1,
            'position_id' => 1,
            'election_id' => 1,
            'status' => 'active',
        ]);
        $this->assertTrue($candidateSaved, json_encode($candidate->errors()));
        $this->assertSame(8, $candidate->getInsertID());
    }
}
