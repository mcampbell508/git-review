<?php

namespace GitReview\Tests\Functional\VersionControl;

use Faker\Factory;
use GitReview\File\File;
use GitReview\Tests\Functional\FunctionalTestCase;
use GitReview\VersionControl\GitBranch;

class GitBranchTest extends FunctionalTestCase
{
    /** @var GitBranch $gitBranch */
    private $gitBranch;
    private $topicBranchName;

    public function setUp(): void
    {
        parent::setUp();

        $this->topicBranchName = (Factory::create())->word;

        $command = <<<EOT
git init && touch master-file-a.txt && git add master-file-a.txt && git commit -m "master commit a" &&
\git checkout -b {$this->topicBranchName} && touch test-branch-file-a.txt && git add test-branch-file-a.txt &&
\git commit -m "test branch commit a" && touch test-branch-file-b.txt &&
\git add test-branch-file-b.txt && git commit -m "test branch commit b" &&
\git checkout master && touch master-file-b.txt && git add . && git commit -m "master commit b"
EOT;
        $this->runProcess($command);

        $this->gitBranch = new GitBranch($this->directory);

        $this->assertEquals("master", $this->gitBranch->getName());
        $this->assertFalse($this->gitBranch->isDirty());
    }

    /**
     * @test
     */
    public function it_can_retrieve_branch_name(): void
    {
        $branchName = $this->gitBranch->getName();

        $this->assertEquals("master", $branchName);

        $this->checkoutBranch($this->topicBranchName);

        $branchName = $this->gitBranch->getName();

        $this->assertEquals($this->topicBranchName, $branchName);
    }

    /**
     * @test
     */
    public function it_can_see_if_branch_is_dirty(): void
    {
        $this->runProcess("touch modified-file.txt");

        $this->assertTrue($this->gitBranch->isDirty());

        $this->runProcess("rm modified-file.txt");

        $this->assertFalse($this->gitBranch->isDirty());
    }

    /**
     * @test
     */
    public function it_can_get_parent_hash_at_pointer_to_master(): void
    {
        $masterCommitId = \trim($this->runProcess("git log --grep='master commit a' --format='%H'")->getOutput());

        $this->checkoutBranch($this->topicBranchName);

        $this->assertEquals($masterCommitId, $this->gitBranch->getParentHash());
    }

    /**
     * @test
     */
    public function it_can_get_all_changed_files_on_branch_including_uncommitted(): void
    {
        $this->checkoutBranch($this->topicBranchName);

        $changedFiles = $this->gitBranch->getChangedFiles();

        $this->assertCount(2, $changedFiles);

        $this->assertTrue($changedFiles->contains(function (File $file) {
            return $file->getName() === 'test-branch-file-a.txt' && $file->getExtension() === 'txt';
        }));

        $this->assertTrue($changedFiles->contains(function (File $file) {
            return $file->getName() === 'test-branch-file-b.txt' && $file->getExtension() === 'txt';
        }));
    }

    private function checkoutBranch($branchName): void
    {
        $this->runProcess("/usr/bin/git checkout ${branchName}");
    }
}
