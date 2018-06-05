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

namespace GitReview\Tests\Unit\Review\Composer;

use Mockery;
use PHPUnit\Framework\TestCase;

class ComposerLintReviewTest extends TestCase
{
    protected $review;

    public function setUp(): void
    {
        $this->review = Mockery::mock('GitReview\Review\Composer\ComposerLintReview[getProcess]');
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
        $composerFile = Mockery::mock('GitReview\File\FileInterface');
        $composerFile->shouldReceive('getFileName')->once()->andReturn('composer.json');

        $this->assertTrue($this->review->canReview($composerFile));

        $normalFile = Mockery::mock('GitReview\File\FileInterface');
        $normalFile->shouldReceive('getFileName')->once()->andReturn('somefile.php');

        $this->assertFalse($this->review->canReview($normalFile));
    }

    /**
     * @test
     */
    public function review(): void
    {
        $composerFile = Mockery::mock('GitReview\File\FileInterface');
        $composerFile->shouldReceive('getFullPath')->once()->andReturn('/some/path/composer.json');

        $process = Mockery::mock('Symfony\Component\Process\Process')->makePartial();
        $process->shouldReceive('run')->once();
        $process->shouldReceive('isSuccessful')->once()->andReturn(false);

        $this->review->shouldReceive('getProcess')->once()->andReturn($process);

        $reporter = Mockery::mock('GitReview\Reporter\ReporterInterface');
        $reporter->shouldReceive('error')->once();

        $this->assertNull($this->review->review($reporter, $composerFile));
    }
}
