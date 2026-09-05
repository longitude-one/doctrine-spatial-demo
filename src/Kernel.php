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

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * @return list<string> An array of allowed values for APP_ENV
     */
    private function getAllowedEnvs(): array
    {
        return ['prod', 'dev', 'test'];
    }
}
