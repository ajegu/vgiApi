<?php

/** @var Laravel\Lumen\Application $app */

use Illuminate\Contracts\Console\Kernel;

$app = require __DIR__.'/../bootstrap/app.php';
$artisan = $app[Kernel::class];

$artisan->call('database:drop');
$artisan->call('database:create');
$artisan->call('database:seed');
