<?php

namespace GitReview\Tests\Unit\VersionControl;

use GitReview\Process\ProcessFactory;
use GitReview\VersionControl\GitBranch;
use Mockery;

class GitBranchTest extends \PHPUnit_Framework_TestCase
{
    private $gitBranch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gitBranch = new GitBranch(
            \getcwd(),
            '/usr/bin/git',
            Mockery::mock(ProcessFactory::class)
        );
    }

    public function it_can_get_the_commits_on_a_branch(): void
    {
    }
}
