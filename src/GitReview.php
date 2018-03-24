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

namespace GitReview;

use GitReview\Collection\FileCollection;
use GitReview\Collection\ReviewCollection;
use GitReview\Commit\CommitMessageInterface;
use GitReview\Reporter\ReporterInterface;
use GitReview\Review\ReviewInterface;

class GitReview
{
    /**
     * A ReviewCollection.
     */
    protected $reviews;

    /**
     * A Reporter.
     */
    protected $reporter;

    /**
     * Initializes a new instance of the StaticReview class.
     *
     * @param ReporterInterface $reporter
     */
    public function __construct(ReporterInterface $reporter)
    {
        $this->reviews = new ReviewCollection();

        $this->setReporter($reporter);
    }

    public function getReporter(): ReporterInterface
    {
        return $this->reporter;
    }

    public function setReporter(ReporterInterface $reporter): self
    {
        $this->reporter = $reporter;

        return $this;
    }

    public function getReviews(): ReviewCollection
    {
        return $this->reviews;
    }

    public function addReview(ReviewInterface $review): self
    {
        $this->reviews->append($review);

        return $this;
    }

    public function addReviews(ReviewCollection $reviews): self
    {
        foreach ($reviews as $review) {
            $this->reviews->append($review);
        }

        return $this;
    }

    /**
     * Runs through each review on each file, collecting any errors.
     */
    public function files(FileCollection $files): self
    {
        foreach ($files as $key => $file) {
            $this->getReporter()->progress($key + 1, \count($files));

            foreach ($this->getReviews()->forFile($file) as $review) {
                $review->review($this->getReporter(), $file);
            }
        }

        return $this;
    }

    /**
     * Runs through each review on the commit, collecting any errors.
     */
    public function message(CommitMessageInterface $message): self
    {
        foreach ($this->getReviews()->forMessage($message) as $review) {
            $review->review($this->getReporter(), $message);
        }

        return $this;
    }
}
