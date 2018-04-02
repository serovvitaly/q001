<?php

require __DIR__ . '/vendor/autoload.php';

$app = new \Symfony\Component\Console\Application();

$app->add(new \Src\Commands\FirstCommand());

try {
    $app->run();
} catch (Exception $e) {
}
