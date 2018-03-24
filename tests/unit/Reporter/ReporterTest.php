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

namespace GitReview\Tests\Unit\Reporter;

use GitReview\Issue\Issue;
use GitReview\Reporter\Reporter;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class ReporterTest extends TestCase
{
    protected $review;

    protected $file;

    protected $reporter;

    public function setUp(): void
    {
        $this->review = Mockery::mock('GitReview\Review\ReviewInterface');
        $this->file = Mockery::mock('GitReview\File\FileInterface');

        $this->reporter = new Reporter();
    }

    public function testReport(): void
    {
        $this->reporter->report(Issue::LEVEL_INFO, 'Test', $this->review, $this->file);

        $this->assertCount(1, $this->reporter->getIssues());
    }

    public function testInfo(): void
    {
        $this->reporter->info('Test', $this->review, $this->file);

        $issues = $this->reporter->getIssues();

        $this->assertCount(1, $issues);

        $this->assertSame(Issue::LEVEL_INFO, $issues->current()->getLevel());
    }

    public function testWarning(): void
    {
        $this->reporter->warning('Test', $this->review, $this->file);

        $issues = $this->reporter->getIssues();

        $this->assertCount(1, $issues);

        $this->assertSame(Issue::LEVEL_WARNING, $issues->current()->getLevel());
    }

    public function testError(): void
    {
        $this->reporter->error('Test', $this->review, $this->file);

        $issues = $this->reporter->getIssues();

        $this->assertCount(1, $issues);

        $this->assertSame(Issue::LEVEL_ERROR, $issues->current()->getLevel());
    }

    public function testHasIssues(): void
    {
        $this->reporter->info('Test', $this->review, $this->file);

        $this->assertTrue($this->reporter->hasIssues());
    }

    public function testHasIssuesWithNoIssues(): void
    {
        $this->assertFalse($this->reporter->hasIssues());
    }

    public function testGetIssues(): void
    {
        $this->reporter->info('Test', $this->review, $this->file);

        $this->assertCount(1, $this->reporter->getIssues());

        $this->reporter->warning('Test', $this->review, $this->file);

        $this->assertCount(2, $this->reporter->getIssues());

        $this->reporter->error('Test', $this->review, $this->file);

        $this->assertCount(3, $this->reporter->getIssues());

        foreach ($this->reporter->getIssues() as $issue) {
            $this->assertSame('Test', $issue->getMessage());
        }
    }
}
