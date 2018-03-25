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
use GitReview\Issue\Issue;
use GitReview\Review\ReviewableInterface;
use GitReview\Review\ReviewInterface;

class Reporter implements ReporterInterface
{
    protected $issues;

    public function __construct()
    {
        $this->issues = new IssueCollection();
    }

    public function progress(int $current, int $total): void
    {
        echo \sprintf("Reviewing %d of %d.\r", $current, $total);
    }

    public function report(int $level, string $message, ReviewInterface $review, ReviewableInterface $subject): self
    {
        $issue = new Issue($level, $message, $review, $subject);

        $this->issues->append($issue);

        return $this;
    }

    public function info(string $message, ReviewInterface $review, ReviewableInterface $subject): self
    {
        $this->report(Issue::LEVEL_INFO, $message, $review, $subject);

        return $this;
    }

    public function warning(string $message, ReviewInterface $review, ReviewableInterface $subject): self
    {
        $this->report(Issue::LEVEL_WARNING, $message, $review, $subject);

        return $this;
    }

    public function error(string $message, ReviewInterface $review, ReviewableInterface $subject): self
    {
        $this->report(Issue::LEVEL_ERROR, $message, $review, $subject);

        return $this;
    }

    public function hasIssues(): bool
    {
        return \count($this->issues) > 0;
    }

    public function getIssues(): IssueCollection
    {
        return $this->issues;
    }
}
