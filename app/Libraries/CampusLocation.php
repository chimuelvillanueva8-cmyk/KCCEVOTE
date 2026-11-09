<?php

namespace App\Libraries;

final class CampusLocation
{
    public static function isConfigured(array $settings): bool
    {
        foreach (['campus_latitude', 'campus_longitude', 'campus_access_radius'] as $key) {
            if (trim((string) ($settings[$key] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    public static function isWithinRange(?float $latitude, ?float $longitude, array $settings): bool
    {
        if (! self::isConfigured($settings)) {
            return true;
        }

        $campusLatitude = $settings['campus_latitude'] ?? null;
        $campusLongitude = $settings['campus_longitude'] ?? null;
        $radius = $settings['campus_access_radius'] ?? null;

        if (
            ! is_numeric($campusLatitude)
            || ! is_numeric($campusLongitude)
            || ! is_numeric($radius)
            || $latitude === null
            || $longitude === null
        ) {
            return false;
        }

        $campusLatitude = (float) $campusLatitude;
        $campusLongitude = (float) $campusLongitude;
        $radius = (float) $radius;

        if (
            ! is_finite($campusLatitude)
            || ! is_finite($campusLongitude)
            || ! is_finite($radius)
            || ! is_finite($latitude)
            || ! is_finite($longitude)
            || $campusLatitude < -90
            || $campusLatitude > 90
            || $campusLongitude < -180
            || $campusLongitude > 180
            || $radius < 0
            || $latitude < -90
            || $latitude > 90
            || $longitude < -180
            || $longitude > 180
        ) {
            return false;
        }

        return self::calculateDistanceMeters($campusLatitude, $campusLongitude, $latitude, $longitude) <= $radius;
    }

    public static function calculateDistanceMeters(float $latitude1, float $longitude1, float $latitude2, float $longitude2): float
    {
        $earthRadius = 6371000.0;
        $deltaLatitude = deg2rad($latitude2 - $latitude1);
        $deltaLongitude = deg2rad($longitude2 - $longitude1);

        $a = sin($deltaLatitude / 2) * sin($deltaLatitude / 2)
            + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2))
            * sin($deltaLongitude / 2) * sin($deltaLongitude / 2);
        $a = max(0.0, min(1.0, $a));
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
