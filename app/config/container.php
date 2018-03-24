<?php

use GitReview\Command\HookInstallCommand;
use GitReview\Command\HookListCommand;
use GitReview\Command\HookRunCommand;
use GitReview\Command\PhpCsFixerCommand;
use GitReview\Console\Application;
use Pimple\Container;

$container = new Container();

$container['commands'] = function($container) {
    return [
        new HookListCommand(),
        new HookInstallCommand(),
        new HookRunCommand(),
        new PhpCsFixerCommand(),
    ];
};

$container['application'] = function($container) {
    $application = new Application(
        'GitReview Command Line Tool',
        '3.0.0'
    );
    $application->addCommands($container['commands']);

    return $application;
};

return $container;
