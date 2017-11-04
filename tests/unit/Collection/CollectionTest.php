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

namespace StaticReview\Test\Unit\Collection;

use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class CollectionTest extends TestCase
{
    protected $collection;

    protected $item;

    public function setUp(): void
    {
        $this->collection = Mockery::mock('StaticReview\Collection\Collection')->makePartial();

        $this->item = 'Example Item';
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testConstructorWithArgument(): void
    {
        $items = [1, 2, 3];

        $this->collection->shouldReceive('validate')->times(count($items))->andReturn(true);

        $this->collection->__construct($items);

        for ($i = 0; $i > count($this->collection); $i++) {
            $this->assertSame($items[$i], $this->collection[$i]);
        }

        $this->assertCount(3, $this->collection);
    }

    public function testConstructorWithoutArgument(): void
    {
        $this->collection->shouldReceive('validate')->never()->andReturn(true);

        $this->collection->__construct();

        $this->assertCount(0, $this->collection);
    }

    public function testAppendWithValidItem(): void
    {
        $this->collection->shouldReceive('validate')->twice()->andReturn(true);

        $this->collection->append($this->item);

        $this->assertCount(1, $this->collection);
        $this->assertSame($this->item, $this->collection->current());

        $this->collection->append($this->item);

        $this->assertCount(2, $this->collection);
        $this->assertSame($this->item, $this->collection->next());
    }

    public function testAppendWithNotTrueOnValidate(): void
    {
        $this->collection->shouldReceive('validate')->once()->andReturn(false);

        $this->collection->append($this->item);

        $this->assertCount(0, $this->collection);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAppendWithExceptionOnValidate(): void
    {
        $this->collection->shouldReceive('validate')->once()->andThrow(new \InvalidArgumentException());

        $this->collection->append($this->item);

        $this->assertCount(0, $this->collection);
    }

    public function testToString(): void
    {
        $this->collection->shouldReceive('validate')->twice()->andReturn(true);

        $this->collection->append($this->item);
        $this->assertStringEndsWith('(1)', (string) $this->collection);

        $this->collection->append($this->item);
        $this->assertStringEndsWith('(2)', (string) $this->collection);
    }
}
