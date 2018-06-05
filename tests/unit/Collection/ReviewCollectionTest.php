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

use GitReview\Collection\ReviewCollection;
use Mockery;
use PHPUnit\Framework\TestCase;

class ReviewCollectionTest extends TestCase
{
    protected $collection;

    public function setUp(): void
    {
        $this->collection = new ReviewCollection();
    }

    /**
     * @test
     */
    public function validate_with_valid_object(): void
    {
        $object = Mockery::mock('GitReview\Review\ReviewInterface');

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
        $review = Mockery::mock('GitReview\Review\ReviewInterface');

        $this->collection->append($review);

        $filter = function () {
            return true;
        };

        $reviews = $this->collection->select($filter);

        $this->assertCount(1, $reviews);
    }

    /**
     * @test
     */
    public function select_with_false_callback(): void
    {
        $review = Mockery::mock('GitReview\Review\ReviewInterface');

        $this->collection->append($review);

        $filter = function () {
            return false;
        };

        $reviews = $this->collection->select($filter);

        $this->assertCount(0, $reviews);
    }

    /**
     * @test
     */
    public function select_with_empty_collection(): void
    {
        $filter = function () {
            return true;
        };

        $this->assertEquals(new ReviewCollection(), $this->collection->select($filter));
    }

    /**
     * @test
     */
    public function for_file_with_matching_file(): void
    {
        $review = Mockery::mock('GitReview\Review\ReviewInterface');
        $review->shouldReceive('canReview')->once()->andReturn(true);

        $file = Mockery::mock('GitReview\File\FileInterface');

        $this->collection->append($review);

        $reviews = $this->collection->forFile($file);

        $this->assertCount(1, $reviews);
        $this->assertSame($review, $reviews->current());
    }

    /**
     * @test
     */
    public function for_file_with_non_matching_file(): void
    {
        $review = Mockery::mock('GitReview\Review\ReviewInterface');
        $review->shouldReceive('canReview')->once()->andReturn(false);

        $file = Mockery::mock('GitReview\File\FileInterface');

        $this->collection->append($review);

        $reviews = $this->collection->forFile($file);

        $this->assertCount(0, $reviews);
    }
}
