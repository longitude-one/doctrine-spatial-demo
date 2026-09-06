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

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\ORM\Mapping as ORM;
use LongitudeOne\Spatial\PHP\Types\SpatialInterface;

#[ORM\Entity(repositoryClass: CityRepository::class)]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private string $name;

    #[ORM\Column(type: 'polygon')]
    private SpatialInterface $surface;

    public function __construct(string $name, SpatialInterface $surface)
    {
        $this->name = $name;
        $this->surface = $surface;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getSurface(): ?SpatialInterface
    {
        return $this->surface;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function setSurface(SpatialInterface $surface): static
    {
        $this->surface = $surface;

        return $this;
    }
}
