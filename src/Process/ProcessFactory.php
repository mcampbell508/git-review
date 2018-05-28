<?php

namespace GitReview\Process;

class ProcessFactory implements ProcessFactoryInterface
{
    public function create(
        $commandLine,
        ?string $cwd = null,
        array $env = null,
        $input = null,
        $timeout = 60
    ): Process {
        $process = new Process(
            $commandLine,
            $cwd,
            $env,
            $input,
            $timeout
        );

        $process->run();

        return $process;
    }
}
