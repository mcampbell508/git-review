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

namespace GitReview\Test\Unit\Review\PHP;

use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class PhpCodeSnifferReviewTest extends TestCase
{
    protected $file;

    protected $review;

    public function setUp(): void
    {
        $this->file = Mockery::mock('GitReview\File\FileInterface');
        $this->review = Mockery::mock('GitReview\Review\PHP\PhpCodeSnifferReview[getProcess]');
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetOption(): void
    {
        $this->review->setOption('standard', 'PSR2');

        $this->assertSame('PSR2', $this->review->getOption('standard'));
    }

    public function testGetOptionForConsole(): void
    {
        $this->review->setOption('standard', 'PSR2');

        $this->assertSame('--standard=PSR2 ', $this->review->getOptionsForConsole());
    }

    public function testSetOption(): void
    {
        $this->review->setOption('standard', 'PSR2');

        $this->assertSame('PSR2', $this->review->getOption('standard'));

        $this->review->setOption('test', 'value');

        $this->assertSame('value', $this->review->getOption('test'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testSetOptionWithReportOption(): void
    {
        $this->review->setOption('report', 'value');
    }

    public function testSetOptionWithOverwrite(): void
    {
        $this->review->setOption('standard', 'PSR2');

        $this->assertSame('PSR2', $this->review->getOption('standard'));

        $this->review->setOption('standard', 'PEAR');

        $this->assertSame('PEAR', $this->review->getOption('standard'));
    }

    public function testSetOptionReturnsReview(): void
    {
        $this->assertInstanceOf(\get_class($this->review), $this->review->setOption('test', 'test'));
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

    public function testReviewWithPsr2Standard(): void
    {
        $this->file->shouldReceive('getFullPath')->once()->andReturn(__FILE__);

        $process = Mockery::mock('Symfony\Component\Process\Process')->makePartial();
        $process->shouldReceive('run')->once();
        $process->shouldReceive('isSuccessful')->once()->andReturn(true);
        $process->shouldReceive('getOutput')->never();

        $this->review->shouldReceive('getProcess')
            ->once()
            ->with('vendor/bin/phpcs --report=json --standard=PSR2 ' . __FILE__)
            ->andReturn($process);

        $reporter = Mockery::mock('GitReview\Reporter\ReporterInterface');

        $this->review->setOption('standard', 'PSR2');

        $this->assertNull($this->review->review($reporter, $this->file));
    }

    public function testReviewWithViolations(): void
    {
        $this->file->shouldReceive('getFullPath')->once()->andReturn(__FILE__);

        $process = Mockery::mock('Symfony\Component\Process\Process')->makePartial();
        $process->shouldReceive('run')->once();
        $process->shouldReceive('isSuccessful')->once()->andReturn(false);

        $testOutput = '{"files":{"test.php":{"errors":1,"warnings":0,"messages":[{"message":"Message","line":2}]}}}';

        $process->shouldReceive('getOutput')->once()->andReturn($testOutput);

        $this->review->shouldReceive('getProcess')->once()->andReturn($process);

        $reporter = Mockery::mock('GitReview\Reporter\ReporterInterface');
        $reporter->shouldReceive('warning')->once()->with('Message on line 2', $this->review, $this->file);

        $this->assertNull($this->review->review($reporter, $this->file));
    }
}
