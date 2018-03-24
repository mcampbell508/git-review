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

namespace GitReview\Tests\Unit;

use GitReview\Collection\FileCollection;
use GitReview\Collection\ReviewCollection;
use GitReview\GitReview;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class GitReviewTest extends TestCase
{
    protected $review;

    protected $reporter;

    protected $GitReview;

    public function setUp(): void
    {
        $this->reporter = Mockery::mock('GitReview\Reporter\ReporterInterface');
        $this->review = Mockery::mock('GitReview\Review\ReviewInterface');

        $this->GitReview = new GitReview($this->reporter);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetReporter(): void
    {
        $this->assertSame($this->reporter, $this->GitReview->getReporter());
    }

    public function testSetReporter(): void
    {
        $newReporter = Mockery::mock('GitReview\Reporter\ReporterInterface');

        $this->assertSame($this->GitReview, $this->GitReview->setReporter($newReporter));

        $this->assertSame($newReporter, $this->GitReview->getReporter());
    }

    public function testGetReviews(): void
    {
        $this->assertTrue($this->GitReview->getReviews() instanceof ReviewCollection);
        $this->assertCount(0, $this->GitReview->getReviews());

        $this->GitReview->addReview($this->review);
        $this->assertCount(1, $this->GitReview->getReviews());
    }

    public function testAddReview(): void
    {
        $this->assertCount(0, $this->GitReview->getReviews());

        $this->assertSame($this->GitReview, $this->GitReview->addReview($this->review));
        $this->assertCount(1, $this->GitReview->getReviews());
    }

    public function testAddReviews(): void
    {
        $this->assertCount(0, $this->GitReview->getReviews());

        $reviews = new ReviewCollection([$this->review, $this->review]);

        $this->assertSame($this->GitReview, $this->GitReview->addReviews($reviews));
        $this->assertCount(2, $this->GitReview->getReviews());
    }

    public function testReview(): void
    {
        $file = Mockery::mock('GitReview\File\FileInterface');

        $this->reporter->shouldReceive('progress')->once();
        $this->review->shouldReceive('review')->once();
        $this->review->shouldReceive('canReview')->once()->andReturn(true);

        $files = new FileCollection([$file]);
        $reviews = new ReviewCollection([$this->review]);

        $this->GitReview->addReviews($reviews);

        $this->assertSame($this->GitReview, $this->GitReview->files($files));
    }
}
