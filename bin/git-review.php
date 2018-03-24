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

use GitReview\Console\Application;

require __DIR__ . '/../app/bootstrap.php';

/** @var Application $application */
$console = $container['application'];

$console->run();
