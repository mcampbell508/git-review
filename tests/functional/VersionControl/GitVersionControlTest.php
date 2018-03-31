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

namespace GitReview\Tests\Functional\VersionControl;

use GitReview\Tests\Functional\FunctionalTestCase;
use GitReview\VersionControl\GitVersionControl;
use Symfony\Component\Process\Process;

class GitVersionControlTest extends FunctionalTestCase
{
    /** @var GitVersionControl */
    private $gitVersionControl;

    public function setUp(): void
    {
        parent::setUp();

        $this->gitVersionControl = new GitVersionControl();
    }

    public function tearDown(): void
    {
        // Clean up any created files.
        $this->runProcess('rm -rf ' . $this->directory);
    }

    /**
     * @test
     */
    public function get_staged_files_with_no_git_repo(): void
    {
        $collection = $this->gitVersionControl->getStagedFiles();

        $this->assertInstanceOf('GitReview\Collection\FileCollection', $collection);
        $this->assertCount(0, $collection);
    }

    /**
     * @test
     */
    public function get_staged_files_with_git_repo_saassa_saassa(): void
    {
        $cmd = 'touch ' . $this->testFileName;
        $cmd .= ' && git init';

        $this->runProcess($cmd);

        $collection = $this->gitVersionControl->getStagedFiles();

        $this->assertInstanceOf('GitReview\Collection\FileCollection', $collection);
        $this->assertCount(0, $collection);
    }

    /**
     * @test
     */
    public function get_staged_files_with_new_file(): void
    {
        $cmd = 'touch ' . $this->testFileName;
        $cmd .= ' && git init';
        $cmd .= ' && git add ' . $this->testFileName;

        $this->runProcess($cmd);

        $collection = $this->gitVersionControl->getStagedFiles();

        $this->assertInstanceOf('GitReview\Collection\FileCollection', $collection);
        $this->assertCount(1, $collection);

        $file = $collection->current();

        $this->assertSame(\basename($this->testFileName), $file->getFileName());
        $this->assertSame('A', $file->getStatus());
    }

    /**
     * @test
     */
    public function get_staged_files_with_modified_file(): void
    {
        $cmd = 'touch ' . $this->testFileName;
        $cmd .= ' && git init';
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git commit -m \'test\'';
        $cmd .= ' && echo \'test\' > ' . $this->testFileName;
        $cmd .= ' && git add ' . $this->testFileName;

        $this->runProcess($cmd);

        $collection = $this->gitVersionControl->getStagedFiles();

        $this->assertInstanceOf('GitReview\Collection\FileCollection', $collection);
        $this->assertCount(1, $collection);

        $file = $collection->current();

        $this->assertSame(\basename($this->testFileName), $file->getFileName());
        $this->assertSame('M', $file->getStatus());
    }

    /**
     * @test
     */
    public function get_staged_files_with_partially_staged_file(): void
    {
        $cmd = 'touch ' . $this->testFileName;
        $cmd .= ' && git init';
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git commit -m \'test\'';
        $cmd .= ' && echo \'test\' > ' . $this->testFileName;
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && echo \'not staged\' >> ' . $this->testFileName;

        $this->runProcess($cmd);

        $collection = $this->gitVersionControl->getStagedFiles();

        $this->assertInstanceOf('GitReview\Collection\FileCollection', $collection);
        $this->assertCount(1, $collection);

        $file = $collection->current();

        $this->assertSame(\basename($this->testFileName), $file->getFileName());
        $this->assertSame('M', $file->getStatus());

        $process = $this->runProcess('cat ' . $file->getFullPath());

        $this->assertSame('test', \trim($process->getOutput()));
    }

    /**
     * @test
     */
    public function get_staged_files_with_moved_unrenamed_file(): void
    {
        $testFolderName = 'test_folder';

        $cmd = 'touch ' . $this->testFileName;
        $cmd .= ' && echo \'test\' > ' . $this->testFileName;
        $cmd .= ' && git init';
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git commit -m \'test\'';
        $cmd .= ' && mkdir ' . $testFolderName;
        $cmd .= ' && git mv ' . $this->testFileName . ' ' . $testFolderName;
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git add ' . $testFolderName;

        $process = new Process($cmd);
        $process->run();

        $git = new GitVersionControl();
        $collection = $git->getStagedFiles();

        $this->assertInstanceOf('GitReview\Collection\FileCollection', $collection);
        $this->assertCount(1, $collection);

        $file = $collection->current();

        $this->assertSame(\basename($this->testFileName), $file->getFileName());
        $this->assertStringStartsWith('R', $file->getStatus());
    }

    /**
     * @test
     */
    public function get_staged_files_with_moved_renamed_file(): void
    {
        $testFolderName = 'test_folder';
        $newTestFileName = 'test_new.txt';

        $cmd = 'touch ' . $this->testFileName;
        $cmd .= ' && echo \'test\' > ' . $this->testFileName;
        $cmd .= ' && git init';
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git commit -m \'test\'';
        $cmd .= ' && mkdir ' . $testFolderName;
        $cmd .= ' && mv ' . $this->testFileName . ' ' . $testFolderName . DIRECTORY_SEPARATOR . $newTestFileName;
        $cmd .= ' && git add ' . $this->testFileName;
        $cmd .= ' && git add ' . $testFolderName;

        $process = new Process($cmd);
        $process->run();

        $git = new GitVersionControl();
        $collection = $git->getStagedFiles();

        $this->assertInstanceOf('GitReview\Collection\FileCollection', $collection);
        $this->assertCount(1, $collection);

        $file = $collection->current();

        $this->assertSame(\basename($newTestFileName), $file->getFileName());
        $this->assertStringStartsWith('R', $file->getStatus());
    }
}
