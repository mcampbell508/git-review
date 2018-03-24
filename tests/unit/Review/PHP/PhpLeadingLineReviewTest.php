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

namespace GitReview\Tests\Unit\Review\PHP;

use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class PhpLeadingLineReviewTest extends TestCase
{
    protected $file;

    protected $review;

    public function setUp(): void
    {
        $this->file = Mockery::mock('GitReview\File\FileInterface');
        $this->review = Mockery::mock('GitReview\Review\PHP\PhpLeadingLineReview[getProcess]');
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testCanReview(): void
    {
        $this->file->shouldReceive('getExtension')->once()->andReturn('php');

        $this->assertTrue($this->review->canReview($this->file));
    }

    public function testCanReviewWithInvalidExtension(): void
    {
        $this->file->shouldReceive('getExtension')->once()->andReturn('txt');

        $this->assertFalse($this->review->canReview($this->file));
    }

    public function testReviewWithBadBeginning(): void
    {
        $this->file->shouldReceive('getFullPath')->once()->andReturn(__FILE__);

        $process = Mockery::mock('Symfony\Component\Process\Process')->makePartial();
        $process->shouldReceive('run')->once();
        $process->shouldReceive('getOutput')->once()->andReturn(PHP_EOL . PHP_EOL);

        $this->review->shouldReceive('getProcess')->once()->andReturn($process);

        $reporter = Mockery::mock('GitReview\Reporter\ReporterInterface');
        $reporter->shouldReceive('error')->once();

        $this->assertNull($this->review->review($reporter, $this->file));
    }

    public function testReviewWithDefaultBeginning(): void
    {
        $this->file->shouldReceive('getFullPath')->once()->andReturn(__FILE__);

        $process = Mockery::mock('Symfony\Component\Process\Process')->makePartial();
        $process->shouldReceive('run')->once();
        $process->shouldReceive('getOutput')->once()->andReturn('<?php' . PHP_EOL);

        $this->review->shouldReceive('getProcess')->once()->andReturn($process);

        $reporter = Mockery::mock('GitReview\Reporter\ReporterInterface');

        $this->assertNull($this->review->review($reporter, $this->file));
    }

    public function testReviewWithScriptBeginning(): void
    {
        $this->file->shouldReceive('getFullPath')->once()->andReturn(__FILE__);

        $process = Mockery::mock('Symfony\Component\Process\Process')->makePartial();
        $process->shouldReceive('run')->once();
        $process->shouldReceive('getOutput')->once()->andReturn('#!/usr/bin/env php' . PHP_EOL);

        $this->review->shouldReceive('getProcess')->once()->andReturn($process);

        $reporter = Mockery::mock('GitReview\Reporter\ReporterInterface');

        $this->assertNull($this->review->review($reporter, $this->file));
    }
}
