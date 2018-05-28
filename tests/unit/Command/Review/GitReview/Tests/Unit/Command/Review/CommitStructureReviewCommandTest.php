<?php

namespace GitReview\Tests\Unit\Command\Review;

use GitReview\Command\Review\CommitStructureReviewCommand;
use GitReview\Commit\CommitCollection;
use GitReview\VersionControl\GitBranch;
use Mockery;
use OndraM\CiDetector\CiDetector;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommitStructureReviewCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var CommitStructureReviewCommand $command */
    private $command;
    /** @var  CommandTester $commandTester */
    private $commandTester;
    private $gitBranch;
    private $ciDetector;
    private $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application();
        $this->gitBranch = Mockery::mock(GitBranch::class, ['./']);
        $this->ciDetector = new CiDetector();

        $this->command = $this->app->add(new CommitStructureReviewCommand(
            $this->gitBranch,
            $this->ciDetector
        ));

        $this->commandTester = new CommandTester($this->command);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        \putenv("TRAVIS");
        \putenv("TRAVIS_PULL_REQUEST");
        \putenv("TRAVIS_BRANCH");
        \putenv("GITLAB_CI");
        \putenv("CI_BUILD_REF_NAME");
        \putenv("JENKINS_URL");
        \putenv("GIT_BRANCH");

        parent::tearDown();
    }

    /** @test */
    public function it_returns_early_and_if_current_checked_out_branch_is_master_on_non_continuous_integration_environments(): void
    {
        $this->gitBranch->shouldReceive('getName')->times(1)->andReturn('master');

        $this->commandTester->execute(['command' => $this->command->getName()]);

        $output = $this->commandTester->getDisplay();

        $this->assertContains(
            "[OK] Currently checked out branch is `master`, nothing to check!",
            $output
        );
    }

    /**
     * @test
     */
    public function it_returns_early_and_if_current_checked_out_branch_is_master_on_travis_ci_continuous_integration_environment(): void
    {
        \putenv("TRAVIS=true");
        \putenv("TRAVIS_PULL_REQUEST=false");
        \putenv("TRAVIS_BRANCH=master");

        $this->gitBranch->shouldReceive('getName')->times(0);

        $this->commandTester->execute(['command' => $this->command->getName()]);

        $output = $this->commandTester->getDisplay();

        $this->assertContains(
            "[OK] Currently checked out branch is `master`, nothing to check!",
            $output
        );
    }

    /**
     * @test
     */
    public function it_returns_early_and_if_current_checked_out_branch_is_master_on_gitlab_continuous_integration_environment(): void
    {
        \putenv("GITLAB_CI=true");
        \putenv("CI_BUILD_REF_NAME=master");

        $this->commandTester->execute(['command' => $this->command->getName()]);

        $output = $this->commandTester->getDisplay();

        $this->assertContains(
            "[OK] Currently checked out branch is `master`, nothing to check!",
            $output
        );
    }

    /**
     * @test
     */
    public function it_returns_early_and_if_current_checked_out_branch_is_master_on_jenkins_continuous_integration_environment(): void
    {
        \putenv("JENKINS_URL=true");
        \putenv("GIT_BRANCH=master");

        $this->commandTester->execute(['command' => $this->command->getName()]);

        $output = $this->commandTester->getDisplay();

        $this->assertContains(
            "[OK] Currently checked out branch is `master`, nothing to check!",
            $output
        );
    }

    /**
     * @test
     */
    public function it_handles_when_there_are_no_commits_to_review_on_a_topic_branch(): void
    {
        $this->gitBranch->shouldReceive('getName')->times(1)->andReturn('feature-pineapples');
        $this->gitBranch->shouldReceive('getCommitsOnBranch')->andReturn(new CommitCollection());

        $this->commandTester->execute(['command' => $this->command->getName()]);

        $output = $this->commandTester->getDisplay();

        $this->assertContains(
            "[OK] There are currently no commits to review on the currently checkout out branch `feature-pineapples`",
            $output
        );
    }
}
