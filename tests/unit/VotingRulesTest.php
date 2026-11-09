<?php

use App\Libraries\VotingRules;
use PHPUnit\Framework\TestCase;

final class VotingRulesTest extends TestCase
{
    public function testUnrestrictedCandidatesRemainAvailable(): void
    {
        $candidates = [
            ['id' => 1, 'eligible_year_levels' => null],
            ['id' => 2, 'eligible_year_levels' => ''],
        ];

        self::assertSame($candidates, VotingRules::filterEligibleCandidates($candidates, null));
    }

    public function testAllowlistMatchesYearLevelWithoutCaseSensitivity(): void
    {
        $candidates = [
            ['id' => 1, 'eligible_year_levels' => '["First Year","Second Year"]'],
            ['id' => 2, 'eligible_year_levels' => '["Third Year"]'],
        ];

        self::assertSame(
            [$candidates[0]],
            VotingRules::filterEligibleCandidates($candidates, ' first year ')
        );
    }

    public function testMalformedEligibilityDataFailsClosed(): void
    {
        $candidates = [
            ['id' => 1, 'eligible_year_levels' => '{invalid'],
            ['id' => 2, 'eligible_year_levels' => '["First Year"]'],
        ];

        self::assertSame([], VotingRules::filterEligibleCandidates($candidates, 'Fourth Year'));
    }

    public function testEmptyAllowlistDoesNotMakeCandidateEligible(): void
    {
        $candidate = ['id' => 1, 'eligible_year_levels' => '[]'];

        self::assertSame([], VotingRules::filterEligibleCandidates([$candidate], 'First Year'));
    }

    public function testRequiredSelectionCountMustMatchExactly(): void
    {
        self::assertTrue(VotingRules::hasValidSelectionCount(2, 2));
        self::assertFalse(VotingRules::hasValidSelectionCount(1, 2));
        self::assertFalse(VotingRules::hasValidSelectionCount(3, 2));
    }

    public function testOptionalPositionMayHaveZeroOrOneSelection(): void
    {
        self::assertTrue(VotingRules::hasValidSelectionCount(0, 0));
        self::assertTrue(VotingRules::hasValidSelectionCount(1, 0));
        self::assertFalse(VotingRules::hasValidSelectionCount(2, 0));
    }
}
