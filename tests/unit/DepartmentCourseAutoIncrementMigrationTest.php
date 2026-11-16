<?php

use CodeIgniter\Test\CIUnitTestCase;

final class DepartmentCourseAutoIncrementMigrationTest extends CIUnitTestCase
{
    public function testRepairMigrationIsDiscoverableByCodeIgniter(): void
    {
        $versions = array_map(
            static fn (object $migration): string => $migration->version,
            service('migrations')->findMigrations()
        );

        self::assertContains(
            '2026-11-16-180000',
            $versions,
            'Discovered versions: ' . implode(', ', $versions)
        );
    }
}
