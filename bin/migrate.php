<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Config\Config;
use App\Core\Database\Connection;
use App\Core\Database\Migrator;
use Dotenv\Dotenv;

$basePath = dirname(__DIR__);

Dotenv::createImmutable($basePath)->safeLoad();

$config = new Config($basePath . '/config');
$connection = new Connection($config);
$migrator = new Migrator($connection);

$migrator->run($basePath . '/database/migrations');