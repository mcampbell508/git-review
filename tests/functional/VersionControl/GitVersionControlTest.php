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

namespace StaticReview\Test\Functional\VersionControl;

use PHPUnit_Framework_TestCase as TestCase;

use StaticReview\VersionControl\GitVersionControl;
use Symfony\Component\Process\Process;

class GitVersionControlTest extends TestCase
{
    protected $directory;

    protected $testFileName;
    private $gitVersionControl;

    public function setUp()
    {
        $this->directory  = sys_get_temp_dir() . '/sjparkinson.static-review/function-tests/';

        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
        } else {
            // Clean up any created files.
            $this->runProcess('rm -rf ' . $this->directory . DIRECTORY_SEPARATOR . '*');
        }

        $this->directory = realpath($this->directory);
        $this->testFileName = 'test.txt';

        chdir($this->directory);

        $this->gitVersionControl = new GitVersionControl();
    }

    public function tearDown()
    {
        // Clean up any created files.
        $this->runProcess('rm -rf ' . $this->directory);
    }

    public function testGetStagedFilesWithNoGitRepo()
    {
        $collection = $this->gitVersionControl->getStagedFiles();

        $this->assertInstanceOf('StaticReview\Collection\FileCollection', $collection);
        $this->assertCount(0, $collection);
    }

    public function testGetStagedFilesWithGitRepo()
    {
        $cmd  = 'touch ' . $this->testFileName;
        $cmd .= ' && git init';

        $this->runProcess($cmd);

        $collection = $this->gitVersionControl->getStagedFiles();

        $this->assertInstanceOf('StaticReview\Collection\FileCollection', $collection);
        $this->assertCount(0, $collection);
    }

    public function testGetStagedFilesWithNewFile()
    {
        $cmd  = 'touch ' . $this->testFileName;
        $cmd .= ' && git init';
        $cmd .= ' && git add ' . $this->testFileName;

        $this->runProcess($cmd);

        $collection = $this->gitVersionControl->getStagedFiles();

        $this->assertInstanceOf('StaticReview\Collection\FileCollection', $collection);
        $this->assertCount(1, $collection);

        $file = $collection->current();

        $this->assertSame(basename($this->testFileName), $file->getFileName());
        $this->assertSame('A', $file->getStatus());
    }

    public function testGetStagedFilesWithModifiedFile()
    {
        $cmd  = 'touch ' . $this->testFileName;
        $cmd .= ' && git init';
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git commit -m \'test\'';
        $cmd .= ' && echo \'test\' > ' . $this->testFileName;
        $cmd .= ' && git add ' . $this->testFileName;

        $this->runProcess($cmd);

        $collection = $this->gitVersionControl->getStagedFiles();

        $this->assertInstanceOf('StaticReview\Collection\FileCollection', $collection);
        $this->assertCount(1, $collection);

        $file = $collection->current();

        $this->assertSame(basename($this->testFileName), $file->getFileName());
        $this->assertSame('M', $file->getStatus());
    }

    public function testGetStagedFilesWithPartiallyStagedFile()
    {
        $cmd  = 'touch ' . $this->testFileName;
        $cmd .= ' && git init';
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git commit -m \'test\'';
        $cmd .= ' && echo \'test\' > ' . $this->testFileName;
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && echo \'not staged\' >> ' . $this->testFileName;

        $this->runProcess($cmd);

        $collection = $this->gitVersionControl->getStagedFiles();

        $this->assertInstanceOf('StaticReview\Collection\FileCollection', $collection);
        $this->assertCount(1, $collection);

        $file = $collection->current();

        $this->assertSame(basename($this->testFileName), $file->getFileName());
        $this->assertSame('M', $file->getStatus());

        $process = $this->runProcess('cat ' . $file->getFullPath());

        $this->assertSame('test', trim($process->getOutput()));
    }

    /**
     * For some reason a file had to be added and committed, for this test to pass.
     * Also, I could not seem to use the ProcessBuilder.
     */
    public function testGetBranchName()
    {
        $cmd  = 'touch ' . $this->testFileName;
        $cmd .= ' && git init';
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git commit -m \'test\'';

        $this->runProcess($cmd);

        $this->assertSame('master', $this->gitVersionControl->getBranch()->getName());
    }

    public function testGetBranchNameAfterChangingBranches()
    {
        $cmd  = 'touch ' . $this->testFileName;
        $cmd .= ' && git init';
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git commit -m \'Commit message\'';

        $this->runProcess($cmd);

        $this->assertSame('master', $this->gitVersionControl->getBranch()->getName());

        $this->runProcess('git checkout -b develop');

        $this->assertSame('develop', $this->gitVersionControl->getBranch()->getName());
    }

    public function testGetBranchNameAccountsForDetachedHeadState()
    {
        $cmd  = 'touch ' . $this->testFileName;
        $cmd .= ' && git init';
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git commit -m \'Commit message\'';
        $cmd .= " && echo 'more text' >> $this->testFileName";
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git commit -m \'Second commit message\'';

        $this->runProcess($cmd);
        $this->causeDetachedHead();

        $this->assertSame('(HEAD', $this->gitVersionControl->getBranch()->getName());
    }

    /**
     * @param $command
     *
     * @return Process
     */
    private function runProcess($command)
    {
        $process = new Process($command, $this->directory);
        $process->run();

        return $process;
    }

    /**
     * Cause a detached HEAD scenario.
     */
    private function causeDetachedHead()
    {
        $this->runProcess('git checkout HEAD~1');
    }

    public function testGetStagedFilesWithMovedUnrenamedFile()
    {
        $testFolderName = 'test_folder';

        $cmd  = 'touch ' . $this->testFileName;
        $cmd .= ' && echo \'test\' > ' . $this->testFileName;
        $cmd .= ' && git init';
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git commit -m \'test\'';
        $cmd .= ' && mkdir ' . $testFolderName;
        $cmd .= ' && mv ' .  $this->testFileName . ' ' . $testFolderName;
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git add ' . $testFolderName;

        $process = new Process($cmd);
        $process->run();

        $git = new GitVersionControl();
        $collection = $git->getStagedFiles();

        $this->assertInstanceOf('StaticReview\Collection\FileCollection', $collection);
        $this->assertCount(1, $collection);

        $file = $collection->current();

        $this->assertSame(basename($this->testFileName), $file->getFileName());
        $this->assertStringStartsWith('R', $file->getStatus());
    }

    public function testGetStagedFilesWithMovedRenamedFile()
    {
        $testFolderName = 'test_folder';
        $newTestFileName = 'test_new.txt';

        $cmd  = 'touch ' . $this->testFileName;
        $cmd .= ' && echo \'test\' > ' . $this->testFileName;
        $cmd .= ' && git init';
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git commit -m \'test\'';
        $cmd .= ' && mkdir ' . $testFolderName;
        $cmd .= ' && mv ' .  $this->testFileName . ' ' . $testFolderName . DIRECTORY_SEPARATOR . $newTestFileName;
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git add ' . $testFolderName;

        $process = new Process($cmd);
        $process->run();

        $git = new GitVersionControl();
        $collection = $git->getStagedFiles();

        $this->assertInstanceOf('StaticReview\Collection\FileCollection', $collection);
        $this->assertCount(1, $collection);

        $file = $collection->current();

        $this->assertSame(basename($newTestFileName), $file->getFileName());
        $this->assertStringStartsWith('R', $file->getStatus());
    }
}
