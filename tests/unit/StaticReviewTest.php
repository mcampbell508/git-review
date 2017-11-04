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

namespace StaticReview\Test\Unit;

use Mockery;
use PHPUnit_Framework_TestCase as TestCase;
use StaticReview\Collection\FileCollection;
use StaticReview\Collection\ReviewCollection;
use StaticReview\StaticReview;

class StaticReviewTest extends TestCase
{
    protected $review;

    protected $reporter;

    protected $staticReview;

    public function setUp(): void
    {
        $this->reporter = Mockery::mock('StaticReview\Reporter\ReporterInterface');
        $this->review   = Mockery::mock('StaticReview\Review\ReviewInterface');

        $this->staticReview = new StaticReview($this->reporter);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetReporter(): void
    {
        $this->assertSame($this->reporter, $this->staticReview->getReporter());
    }

    public function testSetReporter(): void
    {
        $newReporter = Mockery::mock('StaticReview\Reporter\ReporterInterface');

        $this->assertSame($this->staticReview, $this->staticReview->setReporter($newReporter));

        $this->assertSame($newReporter, $this->staticReview->getReporter());
    }

    public function testGetReviews(): void
    {
        $this->assertTrue($this->staticReview->getReviews() instanceof ReviewCollection);
        $this->assertCount(0, $this->staticReview->getReviews());

        $this->staticReview->addReview($this->review);
        $this->assertCount(1, $this->staticReview->getReviews());
    }

    public function testAddReview(): void
    {
        $this->assertCount(0, $this->staticReview->getReviews());

        $this->assertSame($this->staticReview, $this->staticReview->addReview($this->review));
        $this->assertCount(1, $this->staticReview->getReviews());
    }

    public function testAddReviews(): void
    {
        $this->assertCount(0, $this->staticReview->getReviews());

        $reviews = new ReviewCollection([$this->review, $this->review]);

        $this->assertSame($this->staticReview, $this->staticReview->addReviews($reviews));
        $this->assertCount(2, $this->staticReview->getReviews());
    }

    public function testReview(): void
    {
        $file = Mockery::mock('StaticReview\File\FileInterface');

        $this->reporter->shouldReceive('progress')->once();
        $this->review->shouldReceive('review')->once();
        $this->review->shouldReceive('canReview')->once()->andReturn(true);

        $files = new FileCollection([$file]);
        $reviews = new ReviewCollection([$this->review]);

        $this->staticReview->addReviews($reviews);

        $this->assertSame($this->staticReview, $this->staticReview->files($files));
    }
}
