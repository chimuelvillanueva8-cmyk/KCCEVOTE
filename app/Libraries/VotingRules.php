<?php

namespace App\Libraries;

final class VotingRules
{
    public static function filterEligibleCandidates(array $candidates, ?string $yearLevel): array
    {
        return array_values(array_filter($candidates, static function (array $candidate) use ($yearLevel): bool {
            $eligibility = $candidate['eligible_year_levels'] ?? null;
            if ($eligibility === null || $eligibility === '') {
                return true;
            }

            $eligibleLevels = json_decode($eligibility, true);
            if (! is_array($eligibleLevels) || $yearLevel === null || trim($yearLevel) === '') {
                return false;
            }

            $normalizedYear = mb_strtolower(trim($yearLevel));
            foreach ($eligibleLevels as $eligibleLevel) {
                if (is_scalar($eligibleLevel) && mb_strtolower(trim((string) $eligibleLevel)) === $normalizedYear) {
                    return true;
                }
            }

            return false;
        }));
    }

    public static function hasValidSelectionCount(int $selectionCount, int $votesRequired): bool
    {
        return $votesRequired === 0
            ? $selectionCount <= 1
            : $selectionCount === $votesRequired;
    }
}
