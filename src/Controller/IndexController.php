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

use App\Repository\CityRepository;
use App\Repository\HeroRepository;
use App\Service\CityMapFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Displays every city and hero on a shared Cartesian map.
 *
 * Heroes are included even when they lie outside all city boundaries.
 * CityMapFactory prepares the polygons and markers for Twig rendering.
 */
final class IndexController extends AbstractController
{
    /**
     * Loads cities and heroes alphabetically and renders the overview.
     * Empty datasets are supported and produce an empty map and lists.
     */
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(
        CityRepository $cityRepository,
        HeroRepository $heroRepository,
        CityMapFactory $cityMapFactory,
    ): Response {
        $cities = $cityRepository->findBy([], ['name' => 'ASC']);
        $heroes = $heroRepository->findBy([], ['name' => 'ASC']);

        return $this->render('index.html.twig', [
            'cities' => $cities,
            'heroes' => $heroes,
            'map' => $cityMapFactory->create($cities, $heroes),
        ]);
    }
}
