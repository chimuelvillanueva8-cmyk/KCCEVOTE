<?php

namespace App\Libraries;

class FaceIdentity
{
    public static function isValidDescriptor(array $descriptor): bool
    {
        if (count($descriptor) !== 128) {
            return false;
        }

        foreach ($descriptor as $value) {
            if (! is_numeric($value) || ! is_finite((float) $value)) {
                return false;
            }
        }

        return true;
    }

    public static function buildPayload(?string $azureFaceId, array $descriptor = [], array $attributes = []): array
    {
        return [
            'azure_face_id' => $azureFaceId,
            'descriptor' => array_values($descriptor),
            'attributes' => $attributes,
        ];
    }

    public static function normalizePayload($payload): array
    {
        if (is_array($payload)) {
            if (array_is_list($payload)) {
                return ['descriptor' => array_values($payload)];
            }

            return $payload;
        }

        $decoded = json_decode((string) $payload, true);
        if (is_array($decoded)) {
            if (array_is_list($decoded)) {
                return ['descriptor' => array_values($decoded)];
            }

            return $decoded;
        }

        return ['descriptor' => []];
    }

    public static function extractDescriptor($payload): array
    {
        $normalized = self::normalizePayload($payload);
        if (isset($normalized['descriptor']) && is_array($normalized['descriptor'])) {
            return array_values($normalized['descriptor']);
        }

        if (isset($normalized['azure_face_id']) && isset($normalized['descriptor']) && is_array($normalized['descriptor'])) {
            return array_values($normalized['descriptor']);
        }

        if (is_array($normalized)) {
            $values = [];
            foreach ($normalized as $value) {
                if (is_numeric($value)) {
                    $values[] = (float) $value;
                }
            }

            if ($values !== []) {
                return $values;
            }
        }

        return [];
    }

    public static function descriptorsMatch(array $left, array $right, float $threshold = 0.35): bool
    {
        if (! self::isValidDescriptor($left) || ! self::isValidDescriptor($right)) {
            return false;
        }

        $distance = 0.0;
        foreach ($left as $index => $value) {
            $difference = (float) $value - (float) $right[$index];
            $distance += $difference * $difference;
        }

        return sqrt($distance) < $threshold;
    }
}
