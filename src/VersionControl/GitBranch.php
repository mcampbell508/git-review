<?php

namespace GitReview\VersionControl;

use Symfony\Component\Process\Process;

class GitBranch implements GitBranchInterface
{
    private $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function getName(): string
    {
        $process = new Process('git branch | grep "*" | cut -d " " -f 2', $this->basePath);
        $process->run();

        return trim($process->getOutput());
    }

    public function isInDetachedHeadState(): bool
    {
        return $this->getName() === '(detached';
    }
}
