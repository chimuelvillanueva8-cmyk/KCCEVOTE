<?php

use CodeIgniter\Test\CIUnitTestCase;

final class DepartmentCourseAutoIncrementMigrationTest extends CIUnitTestCase
{
    public function testRepairMigrationIsDiscoverableByCodeIgniter(): void
    {
        service('locator', true)->deleteCache();
        $versions = array_map(
            static fn (object $migration): string => $migration->version,
            \Config\Services::migrations(null, null, false)->findMigrations()
        );

        self::assertContains(
            '2026-11-16-180000',
            $versions,
            'Discovered versions: ' . implode(', ', $versions)
        );
        self::assertContains(
            '2026-11-18-120000',
            $versions,
            'Discovered versions: ' . implode(', ', $versions)
        );
    }
}
