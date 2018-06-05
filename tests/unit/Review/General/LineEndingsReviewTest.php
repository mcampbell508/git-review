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

namespace GitReview\Tests\Unit\Review\General;

use Mockery;
use PHPUnit\Framework\TestCase;

class LineEndingsReviewTest extends TestCase
{
    protected $file;

    protected $review;

    public function setUp(): void
    {
        $this->file = Mockery::mock('GitReview\File\FileInterface');
        $this->review = Mockery::mock('GitReview\Review\General\LineEndingsReview[getProcess]');
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function can_review(): void
    {
        $this->file->shouldReceive('getMimeType')->once()->andReturn('text');

        $this->assertTrue($this->review->canReview($this->file));
    }

    /**
     * @test
     */
    public function review(): void
    {
        $this->file->shouldReceive('getFullPath')->once()->andReturn(__FILE__);

        $process = Mockery::mock('Symfony\Component\Process\Process')->makePartial();
        $process->shouldReceive('run')->once();
        $process->shouldReceive('isSuccessful')->once()->andReturn(true);

        $this->review->shouldReceive('getProcess')->once()->andReturn($process);

        $reporter = Mockery::mock('GitReview\Reporter\ReporterInterface');
        $reporter->shouldReceive('error')->once();

        $this->assertNull($this->review->review($reporter, $this->file));
    }
}
