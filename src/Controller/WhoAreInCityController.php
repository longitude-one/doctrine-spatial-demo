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

namespace App\Controller;

use App\Entity\City;
use App\Repository\CityRepository;
use App\Repository\HeroRepository;
use App\Service\CityMapFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Displays the heroes located inside Gotham City and their positions on a map.
 *
 * Repositories handle database access and spatial filtering. CityMapFactory
 * prepares the map, while Twig renders the city, hero list and map together.
 */
final class WhoAreInCityController extends AbstractController
{
    /**
     * Loads Gotham City and renders the heroes strictly inside its polygon.
     *
     * The repository returns heroes ordered by name. Heroes on the polygon's
     * boundary are excluded by the spatial ST_Within predicate.
     * A missing city produces an HTTP 404 response.
     */
    #[Route('/who-are-in-city', name: 'app_who_are_in_city')]
    public function index(CityRepository $cityRepository, HeroRepository $heroRepository, CityMapFactory $cityMapFactory): Response
    {
        // Load Gotham City from the database.
        // Return HTTP 404 when the city does not exist.
        $gotham = $cityRepository->findOneBy(['name' => 'Gotham City']);
        if (!$gotham instanceof City) {
            throw $this->createNotFoundException('Gotham City not found. Did you miss to load the fixtures?');
        }

        // Use the repository's spatial query to select heroes whose positions
        // lie strictly inside Gotham City's polygon.
        $heroesInGotham = $heroRepository->findHeroesInCity($gotham);

        // Build the map definition with the city polygon and hero markers.
        // CityMapFactory prepares the data consumed by Twig's ux_map() helper.
        $map = $cityMapFactory->create([$gotham], $heroesInGotham);

        // Render the city details, selected heroes and map in the same view.
        return $this->render('who_are_in_city.html.twig', [
            'city' => $gotham,
            'heroes' => $heroesInGotham,
            'map' => $map,
        ]);
    }
}
