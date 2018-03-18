#!/usr/bin/env php
<?php

/*
 * This file is part of StaticReview
 *
 * Copyright (c) 2015 Woody Gilk <@shadowhand>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see http://github.com/sjparkinson/static-review/blob/master/LICENSE
 */

$included = include file_exists(__DIR__ . '/../vendor/autoload.php')
    ? __DIR__ . '/../vendor/autoload.php'
    : __DIR__ . '/../../../autoload.php';

if (!$included) {
    echo 'You must set up the project dependencies, run the following commands:' . PHP_EOL
       . 'curl -sS https://getcomposer.org/installer | php' . PHP_EOL
       . 'php composer.phar install' . PHP_EOL;

    exit(1);
}

if (empty($argv[1]) || !is_file($argv[1])) {
    echo 'WARNING: Skipping commit message check because the Git hook was not ' . PHP_EOL
       . 'passed the commit message file path; normally `.git/COMMIT_EDITMSG`' . PHP_EOL;

    exit(1);
}

// Reference the required classes and the reviews you want to use.
use GitReview\GitReview;
use GitReview\Reporter\Reporter;
use GitReview\Review\Message\BodyLineLengthReview;
use GitReview\Review\Message\SubjectImperativeReview;
use GitReview\Review\Message\SubjectLineCapitalReview;
use GitReview\Review\Message\SubjectLineLengthReview;
use GitReview\Review\Message\SubjectLinePeriodReview;
use GitReview\Review\Message\WorkInProgressReview;
use GitReview\VersionControl\GitVersionControl;
use League\CLImate\CLImate;

$reporter = new Reporter();
$climate  = new CLImate();
$git      = new GitVersionControl();

$review   = new GitReview($reporter);

// Add any reviews to the StaticReview instance, supports a fluent interface.
$review->addReview(new BodyLineLengthReview())
       ->addReview(new SubjectImperativeReview())
       ->addReview(new SubjectLineCapitalReview())
       ->addReview(new SubjectLineLengthReview())
       ->addReview(new SubjectLinePeriodReview())
       ->addReview(new WorkInProgressReview());

// Check the commit message.
$review->message($git->getCommitMessage($argv[1]));

// Check if any matching issues were found.
if ($reporter->hasIssues()) {
    $climate->out('')->out('');

    foreach ($reporter->getIssues() as $issue) {
        $climate->red($issue);
    }

    $climate->out('')->red('✘ Please fix the errors above using: git commit --amend');

    exit(0);
}
    $climate->green('✔ That commit looks good!');

    exit(0);
