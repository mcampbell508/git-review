<?php

namespace StaticReview\VersionControl;

use Symfony\Component\Process\Process;

class GitBranch implements GitBranchInterface
{
    /**
     * @var
     */
    private $basePath;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    public function getName()
    {
        $process = new Process('git branch | grep "*" | cut -d " " -f 2', $this->basePath);
        $process->run();

        return trim($process->getOutput());
    }

    public function isInDetachedHeadState()
    {
        return $this->getName() === '(detached';
    }

}