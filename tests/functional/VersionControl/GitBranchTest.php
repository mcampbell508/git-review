<?php

namespace GitReview\Tests\Functional\VersionControl;

use Faker\Factory;
use GitReview\Commit\Commit;
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
git init && git config user.email "git-review@example.com" && git config user.name "Git Review" &&        
touch master-file-a.txt && git add master-file-a.txt && git commit -m "master commit a" &&
git checkout -b {$this->topicBranchName} && touch test-branch-file-a.txt && git add test-branch-file-a.txt &&
git commit -m "test branch commit a" && touch test-branch-file-b.txt &&
git add test-branch-file-b.txt && git commit -m "test branch commit b" &&
git checkout master && touch master-file-b.txt && git add . && git commit -m "master commit b"
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

    /** @test */
    public function it_can_get_a_collection_of_commits_for_the_current_branch(): void
    {
        $this->runProcess("/usr/bin/git checkout -b feature-commits-collection");

        $branchName = $this->gitBranch->getName();

        $this->assertEquals("feature-commits-collection", $branchName);
        $command = <<<EOT
touch feature-commits-file-a.txt && git add . && git commit -m "commit subject a" -m "Some body message also" &&
echo 'test' > feature-commits-file-a.txt && git add feature-commits-file-a.txt && git commit -m "Commit subject b" &&
touch feature-commits-file-b.txt && git add . && git commit -m "Commit message that is way too long"
EOT;
        $this->runProcess($command);

        $commitsOnBranch = $this->gitBranch->getCommitsOnBranch();

        $this->assertEquals(3, $commitsOnBranch->count());

        $commits = $commitsOnBranch->getCommitCollection();

        /** @var Commit $commitOne */
        /** @var Commit $commitTwo */
        /** @var Commit $commitThree */
        $commitOne = $commits[0];
        $commitTwo = $commits[1];
        $commitThree = $commits[2];

        $this->assertEquals('commit subject a', $commitOne->getCommitMessage()->getSubject());
        $this->assertEquals('Some body message also', $commitOne->getCommitMessage()->getBody());
        $this->assertEquals('Git Review', $commitOne->getAuthor()->getAuthorName());
        $this->assertEquals('git-review@example.com', $commitOne->getAuthor()->getAuthorEmail());

        $this->assertEquals('Commit subject b', $commitTwo->getCommitMessage()->getSubject());
        $this->assertEquals('', $commitTwo->getCommitMessage()->getBody());
        $this->assertEquals('Git Review', $commitTwo->getAuthor()->getAuthorName());
        $this->assertEquals('git-review@example.com', $commitTwo->getAuthor()->getAuthorEmail());

        $this->assertEquals('Commit message that is way too long', $commitThree->getCommitMessage()->getSubject());
        $this->assertEquals('', $commitThree->getCommitMessage()->getBody());
        $this->assertEquals('Git Review', $commitThree->getAuthor()->getAuthorName());
        $this->assertEquals('git-review@example.com', $commitThree->getAuthor()->getAuthorEmail());
    }

    /** @test */
    public function it_gets_the_correct_parent_hash_for_a_branch_using_merge_commit_strategy_on_the_master_branch(): void
    {
        $this->runProcess("/usr/bin/git checkout -b feature-commits-collection");

        $branchName = $this->gitBranch->getName();

        $this->assertEquals("feature-commits-collection", $branchName);
        $command = <<<EOT
touch feature-commits-file-a.txt && git add . && git commit -m "commit subject a" -m "Some body message also" &&
git checkout master && git merge feature-commits-collection
EOT;
        $this->runProcess($command);

        $masterHash = \trim($this->runProcess("git rev-parse --verify HEAD")->getOutput());

        $command = <<<EOT
/usr/bin/git checkout -b second-branch && touch feature-commits-file-b.txt && git add . && 
git commit -m "commit subject b"
EOT;
        $this->runProcess($command);

        $this->assertEquals($masterHash, $this->gitBranch->getParentHash());
    }

    /**
     * @test
     */
    public function it_gets_the_correct_parent_hash_for_a_branch_using_rebase_strategy_on_the_master_branch(): void
    {
        $this->runProcess("/usr/bin/git checkout -b feature-commits-collection");

        $branchName = $this->gitBranch->getName();

        $this->assertEquals("feature-commits-collection", $branchName);
        $command = <<<EOT
touch feature-commits-file-a.txt && git add . && git commit -m "commit subject a" -m "Some body message also" &&
git checkout master
EOT;
        $this->runProcess($command);

        $masterHash = \trim($this->runProcess("git rev-parse --verify HEAD")->getOutput());

        $command = <<<EOT
/usr/bin/git checkout -b second-branch && touch feature-commits-file-b.txt && git add . && 
git commit -m "commit subject b"
EOT;
        $this->runProcess($command);

        $this->assertEquals($masterHash, $this->gitBranch->getParentHash());
    }

    private function checkoutBranch($branchName): void
    {
        $this->runProcess("/usr/bin/git checkout ${branchName}");
    }
}
