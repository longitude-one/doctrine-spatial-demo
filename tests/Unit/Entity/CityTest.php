<?php

declare(strict_types=1);

/**
 * This file is part of the LongitudeOne DoctrineSpatial Symfony demo.
 *
 * PHP 8.4 | Symfony 8.1
 *
 * Copyright LongitudeOne - Alexandre Tranchant.
 * Copyright 2024-2026.
 *
 */

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use LongitudeOne\Spatial\PHP\Types\Geometry\Polygon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(City::class)]
final class CityTest extends TestCase
{
    public function testConstructorInitializesAnUnpersistedCity(): void
    {
        $surface = new Polygon([[[0, 0], [10, 0], [10, 10], [0, 0]]]);
        $city = new City('Gotham', $surface);

        self::assertNull($city->getId());
        self::assertSame('Gotham', $city->getName());
        self::assertSame($surface, $city->getSurface());
    }

    public function testNameCanBeChangedFluently(): void
    {
        $surface = new Polygon([[[0, 0], [10, 0], [10, 10], [0, 0]]]);
        $city = new City('Gotham', $surface);

        self::assertSame($city, $city->setName('Metropolis'));
        self::assertSame('Metropolis', $city->getName());
        self::assertSame($surface, $city->getSurface());
    }

    public function testSurfaceCanBeReplacedFluently(): void
    {
        $city = new City('Gotham', new Polygon([[[0, 0], [10, 0], [10, 10], [0, 0]]]));
        $surface = new Polygon([[[0, 0], [20, 0], [20, 20], [0, 0]]]);

        self::assertSame($city, $city->setSurface($surface));
        self::assertSame($surface, $city->getSurface());
        self::assertSame('Gotham', $city->getName());
    }
}
