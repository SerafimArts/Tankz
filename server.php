<?php

use App\Server\Server;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use React\EventLoop\Factory;

require __DIR__ . '/vendor/autoload.php';

$logger = new Logger('Server');
$logger->pushHandler(new StreamHandler(\STDOUT));

$loop = Factory::create();

$server = new Server($loop, $logger);
$server->run('0.0.0.0:80');
