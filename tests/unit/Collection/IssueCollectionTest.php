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

use GitReview\Collection\IssueCollection;
use GitReview\Issue\Issue;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class IssueCollectionTest extends TestCase
{
    protected $collection;

    public function setUp(): void
    {
        $this->collection = new IssueCollection();
    }

    /**
     * @test
     */
    public function validate_with_valid_object(): void
    {
        $object = Mockery::mock('GitReview\Issue\IssueInterface');

        $this->assertTrue($this->collection->validate($object));
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @test
     */
    public function validate_with_invalid_object(): void
    {
        $object = 'Test';

        $this->collection->validate($object);
    }

    /**
     * @test
     */
    public function select_with_true_callback(): void
    {
        $issue = Mockery::mock('GitReview\Issue\IssueInterface');

        $this->collection->append($issue);

        $filter = function () {
            return true;
        };

        $issues = $this->collection->select($filter);

        $this->assertCount(1, $issues);
    }

    /**
     * @test
     */
    public function select_with_false_callback(): void
    {
        $issue = Mockery::mock('GitReview\Issue\IssueInterface');

        $this->collection->append($issue);

        $filter = function () {
            return false;
        };

        $issues = $this->collection->select($filter);

        $this->assertCount(0, $issues);
    }

    /**
     * @test
     */
    public function select_with_empty_collection(): void
    {
        $filter = function () {
            return true;
        };

        $this->assertEquals(new IssueCollection(), $this->collection->select($filter));
    }

    /**
     * @test
     */
    public function for_level_with_matching_level(): void
    {
        $issue = Mockery::mock('GitReview\Issue\IssueInterface');
        $issue->shouldReceive('matches')->once()->andReturn(true);

        $this->collection->append($issue);

        $issues = $this->collection->forLevel(Issue::LEVEL_INFO);

        $this->assertCount(1, $issues);
        $this->assertSame($issue, $issues->current());
    }

    /**
     * @test
     */
    public function for_level_with_non_matching_level(): void
    {
        $issue = Mockery::mock('GitReview\Issue\IssueInterface');
        $issue->shouldReceive('matches')->once()->andReturn(false);

        $this->collection->append($issue);

        $issues = $this->collection->forLevel(Issue::LEVEL_INFO);

        $this->assertCount(0, $issues);
    }
}
