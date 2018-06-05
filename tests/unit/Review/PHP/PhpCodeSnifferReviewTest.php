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
use PHPUnit\Framework\TestCase;

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

    /**
     * @test
     */
    public function get_option(): void
    {
        $this->review->setOption('standard', 'PSR2');

        $this->assertSame('PSR2', $this->review->getOption('standard'));
    }

    /**
     * @test
     */
    public function get_option_for_console(): void
    {
        $this->review->setOption('standard', 'PSR2');

        $this->assertSame('--standard=PSR2 ', $this->review->getOptionsForConsole());
    }

    /**
     * @test
     */
    public function set_option(): void
    {
        $this->review->setOption('standard', 'PSR2');

        $this->assertSame('PSR2', $this->review->getOption('standard'));

        $this->review->setOption('test', 'value');

        $this->assertSame('value', $this->review->getOption('test'));
    }

    /**
     * @expectedException RuntimeException
     *
     * @test
     */
    public function set_option_with_report_option(): void
    {
        $this->review->setOption('report', 'value');
    }

    /**
     * @test
     */
    public function set_option_with_overwrite(): void
    {
        $this->review->setOption('standard', 'PSR2');

        $this->assertSame('PSR2', $this->review->getOption('standard'));

        $this->review->setOption('standard', 'PEAR');

        $this->assertSame('PEAR', $this->review->getOption('standard'));
    }

    /**
     * @test
     */
    public function set_option_returns_review(): void
    {
        $this->assertInstanceOf(\get_class($this->review), $this->review->setOption('test', 'test'));
    }

    /**
     * @test
     */
    public function can_review(): void
    {
        $this->file->shouldReceive('getExtension')->once()->andReturn('php');

        $this->assertTrue($this->review->canReview($this->file));
    }

    /**
     * @test
     */
    public function can_review_with_invalid_extension(): void
    {
        $this->file->shouldReceive('getExtension')->once()->andReturn('txt');

        $this->assertFalse($this->review->canReview($this->file));
    }

    /**
     * @test
     */
    public function review_with_psr2_standard(): void
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

    /**
     * @test
     */
    public function review_with_violations(): void
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
