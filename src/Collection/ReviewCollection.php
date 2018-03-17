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

namespace StaticReview\Collection;

use StaticReview\Commit\CommitMessageInterface;
use StaticReview\File\FileInterface;
use StaticReview\Review\ReviewInterface;

class ReviewCollection extends Collection
{
    /**
     * Validates that $object is an instance of ReviewInterface.
     *
     * @param  ReviewInterface          $object
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validate($object)
    {
        if ($object instanceof ReviewInterface) {
            return true;
        }

        throw new \InvalidArgumentException($object . ' was not an instance of ReviewInterface.');
    }

    /**
     * Filters the collection with the given closure, returning a new collection.
     *
     * @return ReviewCollection
     */
    public function select(callable $filter)
    {
        if (!$this->collection) {
            return new self();
        }

        $filtered = array_filter($this->collection, $filter);

        return new self($filtered);
    }

    /**
     * Returns a filtered ReviewCollection that should be run against the given
     * file.
     *
     * @param  FileInterface $file
     * @return ReviewCollection
     */
    public function forFile(FileInterface $file)
    {
        $filter = function ($review) use ($file) {
            if ($review->canReview($file)) {
                return true;
            }

            return false;
        };

        return $this->select($filter);
    }

    /**
     * Returns a filtered ReviewCollection that should be run against the given
     * message.
     *
     * @param  CommitMessage $message
     * @return ReviewCollection
     */
    public function forMessage(CommitMessageInterface $message)
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
