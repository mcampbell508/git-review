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

use GitReview\Issue\IssueInterface;

class IssueCollection extends Collection
{
    /**
     * Validates that $object is an instance of IssueInterface.
     *
     * @throws InvalidArgumentException
     */
    public function validate(IssueInterface $object): bool
    {
        if ($object instanceof IssueInterface) {
            return true;
        }

        throw new \InvalidArgumentException($object . ' was not an instance of IssueInterface.');
    }

    public function select(callable $filter): Collection
    {
        if (!$this->collection) {
            return new self();
        }

        $filtered = \array_filter($this->collection, $filter);

        return new self($filtered);
    }

    public function forLevel(int $option): IssueCollection
    {
        // Only return issues matching the level.
        $filter = function ($issue) use ($option) {
            if ($issue->matches($option)) {
                return true;
            }

            return false;
        };

        return $this->select($filter);
    }
}
