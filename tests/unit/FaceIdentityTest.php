<?php

use App\Libraries\FaceIdentity;
use PHPUnit\Framework\TestCase;

final class FaceIdentityTest extends TestCase
{
    public function testAcceptsAValidFaceDescriptor(): void
    {
        self::assertTrue(FaceIdentity::isValidDescriptor(array_fill(0, 128, 0.25)));
    }

    public function testRejectsDescriptorsWithAnInvalidLength(): void
    {
        self::assertFalse(FaceIdentity::isValidDescriptor(array_fill(0, 127, 0.25)));
        self::assertFalse(FaceIdentity::isValidDescriptor(array_fill(0, 129, 0.25)));
    }

    public function testRejectsNonNumericAndNonFiniteDescriptorValues(): void
    {
        $nonNumeric = array_fill(0, 128, 0.25);
        $nonNumeric[4] = 'not-a-number';
        self::assertFalse(FaceIdentity::isValidDescriptor($nonNumeric));

        $nonFinite = array_fill(0, 128, 0.25);
        $nonFinite[4] = INF;
        self::assertFalse(FaceIdentity::isValidDescriptor($nonFinite));
    }

    public function testMatchesDescriptorsWithinTheConfiguredDistanceThreshold(): void
    {
        $registered = array_fill(0, 128, 0.0);
        $live = $registered;
        $live[0] = 0.62;

        self::assertTrue(FaceIdentity::descriptorsMatch($live, $registered, 0.65));
        self::assertFalse(FaceIdentity::descriptorsMatch($live, $registered, 0.6));
        self::assertFalse(FaceIdentity::descriptorsMatch($live, $registered));
    }

    public function testDoesNotMatchInvalidFaceDescriptors(): void
    {
        self::assertFalse(FaceIdentity::descriptorsMatch(
            array_fill(0, 127, 0.0),
            array_fill(0, 128, 0.0)
        ));
    }
}
