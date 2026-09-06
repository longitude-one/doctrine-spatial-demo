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

use App\Entity\Hero;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Hero::class)]
final class HeroTest extends TestCase
{
    public function testConstructorInitializesAnUnpersistedHero(): void
    {
        $position = new Point(2.35, 48.86);
        $hero = new Hero('Batman', $position);

        self::assertNull($hero->getId());
        self::assertSame('Batman', $hero->getName());
        self::assertSame($position, $hero->getPosition());
        self::assertNull($hero->getDescription());
    }

    public function testConstructorInitializesDescription(): void
    {
        $hero = new Hero('Batman', new Point(2.35, 48.86), 'The protector of Gotham.');

        self::assertSame('The protector of Gotham.', $hero->getDescription());
    }

    public function testDescriptionCanBeChangedFluently(): void
    {
        $position = new Point(2.35, 48.86);
        $hero = new Hero('Batman', $position, 'The protector of Gotham.');

        self::assertSame($hero, $hero->setDescription('The Dark Knight.'));
        self::assertSame('The Dark Knight.', $hero->getDescription());
        self::assertSame('Batman', $hero->getName());
        self::assertSame($position, $hero->getPosition());
    }

    public function testDescriptionCanBeClearedFluently(): void
    {
        $hero = new Hero('Batman', new Point(2.35, 48.86), 'The protector of Gotham.');

        self::assertSame($hero, $hero->setDescription(null));
        self::assertNull($hero->getDescription());
    }

    public function testNameCanBeChangedFluently(): void
    {
        $position = new Point(2.35, 48.86);
        $hero = new Hero('Batman', $position);

        self::assertSame($hero, $hero->setName('Superman'));
        self::assertSame('Superman', $hero->getName());
        self::assertSame($position, $hero->getPosition());
    }

    public function testPositionCanBeReplacedFluently(): void
    {
        $hero = new Hero('Batman', new Point(2.35, 48.86));
        $position = new Point(-0.12, 51.51);

        self::assertSame($hero, $hero->setPosition($position));
        self::assertSame($position, $hero->getPosition());
        self::assertSame('Batman', $hero->getName());
    }
}
