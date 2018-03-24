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

namespace GitReview\Test\Unit\Issue;

use GitReview\Issue\Issue;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class IssueTest extends TestCase
{
    protected $issue;

    protected $issueCheck;
    protected $issueLevel;
    protected $issueMessage;
    protected $issueFile;

    protected $levels = [Issue::LEVEL_INFO, Issue::LEVEL_WARNING, Issue::LEVEL_ERROR];

    public function setUp(): void
    {
        $this->issueLevel = Issue::LEVEL_INFO;
        $this->issueMessage = 'Test';
        $this->issueReview = Mockery::mock('GitReview\Review\ReviewInterface');
        $this->issueFile = Mockery::mock('GitReview\File\FileInterface');

        $this->issue = new Issue(
            $this->issueLevel,
            $this->issueMessage,
            $this->issueReview,
            $this->issueFile
        );

        $this->assertNotNull($this->issue);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetLevel(): void
    {
        $this->assertSame($this->issueLevel, $this->issue->getLevel());
    }

    public function testGetMessage(): void
    {
        $this->assertSame($this->issueMessage, $this->issue->getMessage());
    }

    public function testGetReviewName(): void
    {
        // Mocked classes doesn't have a namespace so just expect the full class name.
        $expected = \get_class($this->issueReview);

        $this->assertSame($expected, $this->issue->getReviewName());
    }

    public function testGetReviewNameWithNamespace(): void
    {
        $review = new \GitReview\Review\General\NoCommitTagReview();

        $issue = new Issue(
            $this->issueLevel,
            $this->issueMessage,
            $review,
            $this->issueFile
        );

        $this->assertSame('NoCommitTagReview', $issue->getReviewName());
    }

    public function testGetSubject(): void
    {
        $this->assertSame($this->issueFile, $this->issue->getSubject());
    }

    public function testGetLevelName(): void
    {
        foreach ($this->levels as $level) {
            $issue = new Issue(
                $level,
                $this->issueMessage,
                $this->issueReview,
                $this->issueFile
            );

            $this->assertInternalType('string', $issue->getLevelName());
        }
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testGetLevelNameWithInvalidInput(): void
    {
        $issue = new Issue(
            Issue::LEVEL_ALL,
            $this->issueMessage,
            $this->issueReview,
            $this->issueFile
        );

        $this->assertNull($issue->getLevelName());
    }

    public function testGetColour(): void
    {
        foreach ($this->levels as $level) {
            $issue = new Issue(
                $level,
                $this->issueMessage,
                $this->issueReview,
                $this->issueFile
            );

            $this->assertInternalType('string', $issue->getColour());
        }
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testGetColourWithInvalidInput(): void
    {
        $issue = Mockery::mock(
            'GitReview\Issue\Issue[getLevel]',
            [
                Issue::LEVEL_ALL,
                $this->issueMessage,
                $this->issueReview,
                $this->issueFile,
            ]
        );

        $issue->shouldReceive('getLevel')->once()->andReturn(Issue::LEVEL_ALL);

        $this->assertNull($issue->getColour());
    }

    public function testMatches(): void
    {
        $shouldMatch = [
            Issue::LEVEL_INFO,
            Issue::LEVEL_INFO | Issue::LEVEL_WARNING,
            Issue::LEVEL_INFO | Issue::LEVEL_ERROR,
            Issue::LEVEL_ALL,
        ];

        $shouldNotMatch = [
            Issue::LEVEL_WARNING,
            Issue::LEVEL_ERROR,
            Issue::LEVEL_WARNING | Issue::LEVEL_ERROR,
            Issue::LEVEL_ALL & ~Issue::LEVEL_INFO,
        ];

        foreach ($shouldMatch as $option) {
            $this->assertTrue($this->issue->matches($option));
        }

        foreach ($shouldNotMatch as $option) {
            $this->assertFalse($this->issue->matches($option));
        }
    }

    public function testToString(): void
    {
        $file = $this->issue->getSubject();

        $file->shouldReceive('getRelativePath')
            ->andReturn('/Test');

        $file->shouldReceive('getName')
            ->andReturn($file->getRelativePath());

        $issueString = (string)$this->issue;

        // Replace common punctuation with spaces for a better explode.
        $issueStringTokens = \explode(' ', \str_replace([',', '.', ':', ';'], ' ', $issueString));

        $this->assertContains($this->issue->getReviewName(), $issueStringTokens);
        $this->assertContains($this->issue->getLevelName(), $issueStringTokens);
        $this->assertContains($this->issue->getMessage(), $issueStringTokens);
        $this->assertContains($this->issue->getSubject()->getRelativePath(), $issueStringTokens);
    }
}
