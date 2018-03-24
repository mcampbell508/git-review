<?php

/*
 * This file is part of StaticReview
 *
 * Copyright (c) 2014 Samuel Parkinson <@samparkinson_>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see http://github.com/sjparkinson/static-review/blob/master/LICENSE
 */

namespace GitReview\Reporter;

use GitReview\Collection\IssueCollection;
use GitReview\Review\ReviewableInterface;
use GitReview\Review\ReviewInterface;

interface ReporterInterface
{
    public function report(
        int $level,
        string $message,
        ReviewInterface $review,
        ReviewableInterface $subject
    ): Reporter;

    public function info(string $message, ReviewInterface $review, ReviewableInterface $subject): Reporter;

    public function warning(string $message, ReviewInterface $review, ReviewableInterface $subject): Reporter;

    public function error(string $message, ReviewInterface $review, ReviewableInterface $subject): Reporter;

    public function hasIssues(): bool;

    public function getIssues(): IssueCollection;

    public function progress(int $current, int $total);
}
