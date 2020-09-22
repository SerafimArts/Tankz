<?php

use App\Application;

require __DIR__ . '/vendor/autoload.php';

$app = new Application('Test', 1920, 1080);
$app->run('95.165.142.132:80');

