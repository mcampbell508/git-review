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

use GitReview\File\FileInterface;

class FileCollection extends Collection
{
    /**
     * Validates that $object is an instance of FileInterface.
     *
     * @throws InvalidArgumentException
     */
    public function validate($object): bool
    {
        if ($object instanceof FileInterface) {
            return true;
        }

        $exceptionMessage = $object . ' was not an instance of FileInterface.';

        throw new \InvalidArgumentException($exceptionMessage);
    }

    public function select(callable $filter): Collection
    {
        if (!$this->collection) {
            return new self();
        }

        $filtered = \array_filter($this->collection, $filter);

        return new self($filtered);
    }
}
