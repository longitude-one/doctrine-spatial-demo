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

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Hero;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use LongitudeOne\Spatial\PHP\Types\Geometry\LineString;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use LongitudeOne\Spatial\PHP\Types\Geometry\Polygon;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. Create cities (polygons).

        // Gotham City (an island with three bridges extending from its boundary).
        $gothamArea = new Polygon([
            new LineString([
                [20000, 50000], [20000, 70000], [23000, 70000],
                [23000, 75000], [24000, 75000], [24000, 70000], // North Bridge
                [35000, 70000], [40000, 60000],
                [45000, 60000], [45000, 59000], [40000, 59000], // East Bridge
                [40000, 50000], [30000, 45000],
                [25000, 45000], [25000, 40000], [24000, 40000], // South Bridge
                [24000, 45000], [20000, 50000],
            ]),
        ]);

        $gotham = new City('Gotham City', $gothamArea);
        $manager->persist($gotham);

        // Metropolis (southeast of Gotham City).
        $metropolisArea = new Polygon([
            new LineString([
                [60000, 10000], [60000, 30000], [85000, 30000],
                [90000, 20000], [85000, 10000], [60000, 10000],
            ]),
        ]);

        $metropolis = new City('Metropolis', $metropolisArea);
        $manager->persist($metropolis);

        // 2. Create heroes (points).

        $heroesData = [
            // Inside Gotham City.
            [
                'name' => 'Batman',
                'position' => [28000, 58000],
                'description' => 'Inside the main area of Gotham City, away from its boundaries and bridges.',
            ],
            [
                'name' => 'Catwoman',
                'position' => [42000, 59500],
                'description' => 'On the East Bridge, halfway between its northern and southern edges and strictly inside Gotham City.',
            ],

            // On or near Gotham City's boundaries.
            [
                'name' => 'Nightwing',
                'position' => [20000, 60000],
                'description' => 'Exactly on Gotham City\'s western boundary, halfway between its endpoints; not strictly inside the city.',
            ],
            [
                'name' => 'Robin',
                'position' => [23500, 76000],
                'description' => 'Aligned with the center of the North Bridge, 1000 coordinate units beyond its northern end and outside Gotham City.',
            ],

            // Inside Metropolis.
            [
                'name' => 'Superman',
                'position' => [72000, 20000],
                'description' => 'Inside Metropolis, halfway between its northern and southern boundaries.',
            ],
            [
                'name' => 'Supergirl',
                'position' => [70000, 28000],
                'description' => 'Inside the northern part of Metropolis, 2000 coordinate units south of its northern boundary.',
            ],
            [
                'name' => 'Lois Lane',
                'position' => [75000, 12000],
                'description' => 'Inside the southern part of Metropolis, 2000 coordinate units north of its southern boundary.',
            ],

            // Outside both cities (rural area / transit).
            [
                'name' => 'The Flash',
                'position' => [48000, 38000],
                'description' => 'Outside both cities, southeast of Gotham City and northwest of Metropolis.',
            ],
        ];

        foreach ($heroesData as $data) {
            $hero = new Hero(
                $data['name'],
                new Point($data['position'][0], $data['position'][1]),
                $data['description'],
            );
            $manager->persist($hero);
        }

        $manager->flush();
    }
}
