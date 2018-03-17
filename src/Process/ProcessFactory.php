<?php

namespace GitReview\Process;

final class ProcessFactory implements ProcessFactoryInterface
{
    public function create(
        $commandLine,
        $cwd = null,
        array $env = null,
        $input = null,
        $timeout = 60,
        array $options = []
    ): Process {
        $process = new Process(
            $commandLine,
            $cwd,
            $env,
            $input,
            $timeout,
            $options
        );

        $process->run();

        return $process;
    }
}
