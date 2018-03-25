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

use Countable;
use GitReview\Issue\IssueInterface;
use Iterator;

abstract class Collection implements Iterator, Countable
{
    protected $collection = [];

    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->append($item);
        }
    }

    public function __toString()
    {
        return \sprintf('%s(%s)', \get_class($this), $this->count());
    }

    /**
     * Method should throw an InvalidArgumentException if $item is not the
     * expected type.
     *
     * @throws InvalidArgumentException
     */
    abstract public function validate($item): bool;

    abstract public function select(callable $filter): Collection;

    public function append($item): Collection
    {
        if ($this->validate($item)) {
            $this->collection[] = $item;
        }

        return $this;
    }

    public function count(): int
    {
        return \count($this->collection);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return \current($this->collection);
    }

    public function key(): string
    {
        return \key($this->collection);
    }

    public function next()
    {
        return \next($this->collection);
    }

    public function rewind(): Collection
    {
        \reset($this->collection);

        return $this;
    }

    public function valid(): bool
    {
        return \key($this->collection) !== null;
    }
}
