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

namespace GitReview\Test\Unit\Review\Composer;

use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class ComposerSecurityReviewTest extends TestCase
{
    protected $review;

    public function setUp()
    {
        $this->review = Mockery::mock('GitReview\Review\Composer\ComposerSecurityReview[getProcess]');
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testCanReview()
    {
        $composerFile = Mockery::mock('GitReview\File\FileInterface');
        $composerFile->shouldReceive('getFileName')->once()->andReturn('composer.lock');

        $this->assertTrue($this->review->canReview($composerFile));

        $normalFile = Mockery::mock('GitReview\File\FileInterface');
        $normalFile->shouldReceive('getFileName')->once()->andReturn('somefile.php');

        $this->assertFalse($this->review->canReview($normalFile));
    }

    public function testReview()
    {
        $composerFile = Mockery::mock('GitReview\File\FileInterface');
        $composerFile->shouldReceive('getFullPath')->once()->andReturn('/some/path/composer.lock');

        $process = Mockery::mock('Symfony\Component\Process\Process')->makePartial();
        $process->shouldReceive('run')->once();
        $process->shouldReceive('isSuccessful')->once()->andReturn(false);

        $this->review->shouldReceive('getProcess')->once()->andReturn($process);

        $reporter = Mockery::mock('GitReview\Reporter\ReporterInterface');
        $reporter->shouldReceive('error')->once();

        $this->assertNull($this->review->review($reporter, $composerFile));
    }
}
