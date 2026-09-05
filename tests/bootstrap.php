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

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
