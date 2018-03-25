<?php

namespace GitReview\VersionControl;

use GitReview\File\FileCollection;
use GitReview\Process\ProcessFactory;
use Tightenco\Collect\Support\Collection;

class GitBranch implements GitBranchInterface
{
    private $currentWorkingDirectory;
    private $gitBinary;
    private $processFactory;

    public function __construct(string $currentWorkingDirectory, string $gitBinary = '/usr/bin/git')
    {
        $this->currentWorkingDirectory = $currentWorkingDirectory;
        $this->gitBinary = $gitBinary;
        $this->processFactory = new ProcessFactory();
    }

    public function getName(): string
    {
        return $this->processFactory->create("git rev-parse --abbrev-ref HEAD")->getOutput();
    }

    public function getChangedFiles(): Collection
    {
        $fileCollection = new FileCollection($this->currentWorkingDirectory);

        $branchName = $this->getName();

        $committedFilesProcess = $this->processFactory
            ->create(
                "git log --name-status --pretty=format: {$this->getParentHash()}..${branchName}" .
                " | grep -E '^[A-Z]\b' | sort | uniq"
            );

        if (!$committedFilesProcess->isSuccessful()) {
            return new Collection();
        }

        $fileCollection->addFiles(\array_filter(\explode("\n", $committedFilesProcess->getOutput())));

        return $fileCollection->getFileCollection();
    }

    public function getParentHash()
    {
        if ($this->getName() === 'master') {
            return "Branch is already on master";
        }

        return $this->processFactory
            ->create("git rev-list --boundary {$this->getName()}...master | grep \"^-\" | cut -c2-")
            ->getOutput();
    }

    public function isDirty(): bool
    {
        return !empty($this->processFactory->create("git status --short")->getOutput());
    }

    private function getProjectBase(): void
    {
        $this->processFactory->create('git rev-parse --show-toplevel')->getOutput();
    }
}
