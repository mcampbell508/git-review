<?php

$included = false;
foreach ([
    __DIR__ . '/../../../autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
] as $file) {
    if (file_exists($file)) {
        $included = include $file;

        break;
    }
}

if (!$included) {
    echo 'You must set up the project dependencies, run the following commands:' . PHP_EOL
         . 'curl -sS https://getcomposer.org/installer | php' . PHP_EOL
         . 'php composer.phar install' . PHP_EOL;

    exit(1);
}


require __DIR__ . '/../vendor/autoload.php';

$container = require(__DIR__ . '/config/container.php');
