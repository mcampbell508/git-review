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

namespace GitReview\Collection;

use GitReview\Commit\CommitMessageInterface;
use GitReview\File\FileInterface;
use GitReview\Review\ReviewInterface;

class ReviewCollection extends Collection
{
    /**
     * Validates that $object is an instance of ReviewInterface.
     *
     * @throws InvalidArgumentException
     */
    public function validate($object): bool
    {
        if ($object instanceof ReviewInterface) {
            return true;
        }

        throw new \InvalidArgumentException($object . ' was not an instance of ReviewInterface.');
    }

    public function select(callable $filter): Collection
    {
        if (!$this->collection) {
            return new self();
        }

        $filtered = \array_filter($this->collection, $filter);

        return new self($filtered);
    }

    public function forFile(FileInterface $file): Collection
    {
        $filter = function ($review) use ($file) {
            if ($review->canReview($file)) {
                return true;
            }

            return false;
        };

        return $this->select($filter);
    }

    public function forMessage(CommitMessageInterface $message): Collection
    {
        $filter = function ($review) use ($message) {
            if ($review->canReview($message)) {
                return true;
            }

            return false;
        };

        return $this->select($filter);
    }
}
