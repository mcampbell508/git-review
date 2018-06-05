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

namespace GitReview\Tests\Unit\Commit;

use GitReview\Commit\CommitMessage;
use PHPUnit\Framework\TestCase;

class CommitMessageTest extends TestCase
{
    /**
     * @var string Directory that contains fixtures
     */
    private $fixtures;

    public function setUp(): void
    {
        $this->fixtures = \realpath(__DIR__ . '/../../fixtures');
    }

    /**
     * @test
     */
    public function construction_subject_only(): void
    {
        $commit = new CommitMessage($this->message('subject-only'));

        $this->assertSame('Create a better commit message', $commit->getSubject());
        $this->assertSame('', $commit->getBody());
    }

    /**
     * @test
     */
    public function construction_subject_and_body(): void
    {
        $commit = new CommitMessage($this->message('subject-and-body'));

        $this->assertSame('Create a better commit message', $commit->getSubject());
        $this->assertSame('We have the tools.', $commit->getBody());
    }

    /**
     * @test
     */
    public function construction_subject_and_body_and_comments(): void
    {
        $commit = new CommitMessage($this->message('subject-and-body-and-comments'));

        // Nothing should be different, the comments should be stripped
        $this->assertSame('Create a better commit message', $commit->getSubject());
        $this->assertSame('We have the tools.', $commit->getBody());
    }

    /**
     * @test
     */
    public function construction_subject_and_body_and_diff(): void
    {
        $commit = new CommitMessage($this->message('subject-and-body-and-diff'));

        // Nothing should be different, the diff should be stripped
        $this->assertSame('Create a better commit message', $commit->getSubject());
        $this->assertSame('We have the tools.', $commit->getBody());
    }

    /**
     * Get a commit message fixture by name
     *
     * @param string $name
     *
     * @return string
     */
    private function message(string $name): string
    {
        return \file_get_contents($this->fixtures . '/commit-message-' . $name . '.txt');
    }
}
