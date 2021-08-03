<?php
declare(strict_types=1);

use App\Core;

define('ROOT', dirname(__DIR__));
require ROOT . '/vendor/autoload.php';

$app = Core::create(ROOT);
$app->run();
