<?php

/*
 * This file is part of StaticReview
 *
 * Copyright (c) 2015 Woody Gilk <@shadowhand>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see http://github.com/sjparkinson/static-review/blob/master/LICENSE
 */

namespace GitReview\Tests\Unit;

use Mockery;
use PHPUnit\Framework\TestCase;

class AbstractReviewTest extends TestCase
{
    /**
     * @test
     */
    public function get_process(): void
    {
        $review = Mockery::mock('GitReview\Review\AbstractReview')->makePartial();

        $process = $review->getProcess('whoami');

        $this->assertInstanceOf('Symfony\Component\Process\Process', $process);
    }

    /**
     * @test
     */
    public function get_process_working_directory(): void
    {
        $review = Mockery::mock('GitReview\Review\AbstractReview')->makePartial();

        $process = $review->getProcess('whoami');

        // By default, the working directory should be the current directory.
        $this->assertSame(\getcwd(), $process->getWorkingDirectory());

        $cwd = \getcwd();

        // Move out of the current working directory, which should cause the
        // process root directory to match the original root rather than the
        // current directory. This is done to handle global installation.
        \chdir(\sys_get_temp_dir());

        $process = $review->getProcess('whoami');

        $this->assertSame($cwd, $process->getWorkingDirectory());
        $this->assertNotSame(\getcwd(), $process->getWorkingDirectory());

        // Restore original working directory.
        \chdir($cwd);
    }
}
