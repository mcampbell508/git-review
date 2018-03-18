#!/usr/bin/env php
<?php

/*
 * This file is part of GitReview
 *
 * Copyright (c) 2014 Samuel Parkinson <@samparkinson_>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see http://github.com/sjparkinson/static-review/blob/master/LICENSE
 */

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

use GitReview\Command\HookInstallCommand;
use GitReview\Command\HookListCommand;
use GitReview\Command\HookRunCommand;
use GitReview\Command\PhpCsFixerCommand;
use Symfony\Component\Console\Application;

$name    = 'GitReview Command Line Tool';
$version = '3.0.0';

$console = new Application($name, $version);

$console->addCommands([
    new HookListCommand(),
    new HookInstallCommand(),
    new HookRunCommand(),
    new PhpCsFixerCommand(),
]);

$console->run();
