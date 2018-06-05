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

namespace GitReview\Tests\Unit\Collection;

use GitReview\Issue\IssueInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    protected $collection;

    protected $item;

    public function setUp(): void
    {
        $this->collection = Mockery::mock('GitReview\Collection\Collection')->makePartial();

        $this->item = 'Example Item';
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function constructor_with_argument(): void
    {
        $items = [
            Mockery::mock(IssueInterface::class),
            Mockery::mock(IssueInterface::class),
            Mockery::mock(IssueInterface::class),
        ];

        $this->collection->shouldReceive('validate')
            ->times(\count($items))
            ->andReturn(true);

        $this->collection->__construct($items);

        for ($i = 0; $i > \count($this->collection); $i++) {
            $this->assertSame($items[$i], $this->collection[$i]);
        }

        $this->assertCount(3, $this->collection);
    }

    /**
     * @test
     */
    public function constructor_without_argument(): void
    {
        $this->collection->shouldReceive('validate')->never()->andReturn(true);

        $this->collection->__construct();

        $this->assertCount(0, $this->collection);
    }

    /**
     * @test
     */
    public function append_with_valid_item(): void
    {
        $this->collection->shouldReceive('validate')->twice()->andReturn(true);

        $mock = Mockery::mock(IssueInterface::class);
        $this->collection->append($mock);

        $this->assertCount(1, $this->collection);
        $this->assertSame($mock, $this->collection->current());

        $this->collection->append($mock);

        $this->assertCount(2, $this->collection);
        $this->assertSame($mock, $this->collection->next());
    }

    /**
     * @test
     */
    public function append_with_not_true_on_validate(): void
    {
        $this->collection->shouldReceive('validate')->once()->andReturn(false);
        $mock = Mockery::mock(IssueInterface::class);

        $this->collection->append($mock);

        $this->assertCount(0, $this->collection);
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @test
     */
    public function append_with_exception_on_validate(): void
    {
        $this->collection->shouldReceive('validate')->once()->andThrow(new \InvalidArgumentException());
        $mock = Mockery::mock(IssueInterface::class);

        $this->collection->append($mock);

        $this->assertCount(0, $this->collection);
    }

    /**
     * @test
     */
    public function to_string(): void
    {
        $this->collection->shouldReceive('validate')->twice()->andReturn(true);
        $mock = Mockery::mock(IssueInterface::class);

        $this->collection->append($mock);
        $this->assertStringEndsWith('(1)', (string)$this->collection);

        $this->collection->append($mock);
        $this->assertStringEndsWith('(2)', (string)$this->collection);
    }
}
