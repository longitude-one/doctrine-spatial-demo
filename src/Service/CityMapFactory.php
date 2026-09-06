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

namespace App\Service;

use App\Entity\City;
use App\Entity\Hero;
use LongitudeOne\Spatial\PHP\Types\PointInterface;
use LongitudeOne\Spatial\PHP\Types\PolygonInterface;
use Symfony\UX\Map\Bridge\Leaflet\LeafletOptions;
use Symfony\UX\Map\Exception\InvalidArgumentException;
use Symfony\UX\Map\InfoWindow;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;
use Symfony\UX\Map\Polygon;

/**
 * Converts city and hero spatial data into a Symfony UX Map for Twig rendering.
 *
 * The map displays city polygons and the supplied heroes as markers, with
 * their names and descriptions in information windows. This keeps presentation
 * logic out of the controller; selecting heroes inside the city remains the
 * repository's responsibility.
 *
 * Coordinates belong to the demo's fictional Cartesian plane. They are scaled
 * for UX Map's Point validation, without changing the entities or converting
 * them to geographical coordinates. The city-map Stimulus controller configures
 * Leaflet's CRS.Simple projection and fits the viewport to all map elements.
 */
class CityMapFactory
{
    // Keep the fictional Cartesian coordinates within UX Map's Point limits.
    private const SCALE = 1000;

    /**
     * Builds a tile-free map with city outlines and one marker per hero.
     *
     * All polygon rings are preserved, including holes. The initial center is
     * the midpoint of the bounds of all cities and heroes. An empty map uses
     * the origin. The browser then fits the viewport to all map elements,
     * including heroes outside any city.
     *
     * Marker information windows contain HTML-escaped names and descriptions,
     * with an empty description when the entity's description is null.
     *
     * @param City[] $cities Cities with non-empty polygon surfaces
     * @param Hero[] $heroes Heroes already selected by the caller;
     *                       no spatial filtering is performed here
     *
     * @return Map Map definition ready to pass to Twig's ux_map() function
     *
     * @throws \LogicException
     *                                  When the city surface is not a polygon
     *                                  or a hero position is not a point
     * @throws InvalidArgumentException
     *                                  When scaled coordinates exceed
     *                                  UX Map's Point limits
     */
    public function create(array $cities, array $heroes): Map
    {
        $points = [];
        $map = (new Map())
            ->center(new Point(0, 0))
            ->zoom(3)
            ->options(new LeafletOptions(tileLayer: false, attributionControl: false));

        foreach ($cities as $city) {
            $surface = $city->getSurface();
            if (!$surface instanceof PolygonInterface) {
                throw new \LogicException('A city map requires a polygon surface.');
            }

            $rings = [];
            foreach ($surface->toArray() as $ring) {
                $rings[] = array_map($this->toMapPoint(...), $ring);
            }

            array_push($points, ...$rings[0]);
            $map->addPolygon(new Polygon(
                $rings,
                infoWindow: new InfoWindow(
                    headerContent: htmlspecialchars($city->getName() ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                ),
            ));
        }

        foreach ($heroes as $hero) {
            $position = $hero->getPosition();
            if (!$position instanceof PointInterface) {
                throw new \LogicException('A hero marker requires a point position.');
            }

            $point = $this->toMapPoint($position->toArray());
            $points[] = $point;
            $map->addMarker(new Marker(
                position: $point,
                title: $hero->getName(),
                infoWindow: new InfoWindow(
                    headerContent: htmlspecialchars($hero->getName(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                    content: htmlspecialchars($hero->getDescription() ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                ),
            ));
        }

        if ([] !== $points) {
            $latitudes = array_map(static fn (Point $point): float => $point->latitude, $points);
            $longitudes = array_map(static fn (Point $point): float => $point->longitude, $points);
            $map->center(new Point(
                (min($latitudes) + max($latitudes)) / 2,
                (min($longitudes) + max($longitudes)) / 2,
            ));
        }

        return $map;
    }

    /**
     * Converts Cartesian [X, Y] into UX Map's (latitude, longitude) order.
     *
     * Both axes are divided by SCALE, so polygon vertices and hero markers stay
     * aligned. Latitude carries Y and longitude carries X solely for rendering
     * with CRS.Simple; this is not a geographical projection or SRID conversion.
     *
     * @param array{0: float|int, 1: float|int} $point
     *                                                 Cartesian coordinates
     *                                                 in the entity's original units
     *
     * @throws InvalidArgumentException
     *                                  When scaled Y is outside [-90, 90]
     *                                  or scaled X is outside [-180, 180]
     */
    private function toMapPoint(array $point): Point
    {
        // Leaflet expects Y, X (latitude, longitude).
        // Apply the same scale to both axes.
        return new Point($point[1] / self::SCALE, $point[0] / self::SCALE);
    }
}
