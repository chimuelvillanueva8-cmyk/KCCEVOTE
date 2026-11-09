<?php

use App\Libraries\CampusLocation;
use PHPUnit\Framework\TestCase;

final class CampusLocationTest extends TestCase
{
    public function testUnconfiguredLocationDoesNotBlockAccess(): void
    {
        self::assertTrue(CampusLocation::isWithinRange(null, null, []));
    }

    public function testPartialSettingsAndMissingCoordinatesFailClosed(): void
    {
        self::assertFalse(CampusLocation::isWithinRange(10.0, 20.0, [
            'campus_latitude' => '10',
        ]));
        self::assertFalse(CampusLocation::isWithinRange(null, null, [
            'campus_latitude' => '10',
            'campus_longitude' => '20',
            'campus_access_radius' => '100',
        ]));
    }

    public function testConfiguredRadiusAcceptsOnlyValidCoordinatesWithinRange(): void
    {
        $settings = [
            'campus_latitude' => '14.5995',
            'campus_longitude' => '120.9842',
            'campus_access_radius' => '500',
        ];

        self::assertTrue(CampusLocation::isWithinRange(14.5996, 120.9842, $settings));
        self::assertFalse(CampusLocation::isWithinRange(14.61, 120.9842, $settings));
        self::assertFalse(CampusLocation::isWithinRange(91, 120.9842, $settings));
    }
}
