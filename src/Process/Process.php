<?php

namespace GitReview\Process;

use Symfony\Component\Process\Process as SymfonyProcess;

class Process extends SymfonyProcess implements ProcessInterface
{
    public function __construct(
        $commandline,
        ?string $cwd = null,
        ?array $env = null,
        $input = null,
        $timeout = 60
    ) {
        parent::__construct($commandline, $cwd, $env, $input, $timeout);
    }

    public function getOutput()
    {
        return \trim(parent::getOutput());
    }

    /**
     * Gets the options for proc_open.
     *
     * @return array The current options
     */
    public function getOptions()
    {
        // TODO: Implement getOptions() method.
    }

    /**
     * Sets the options for proc_open.
     *
     * @param array $options The new options
     *
     * @return ProcessInterface
     */
    public function setOptions(array $options)
    {
        // TODO: Implement setOptions() method.
    }
}
