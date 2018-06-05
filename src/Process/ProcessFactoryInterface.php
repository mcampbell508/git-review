<?php

namespace GitReview\Process;

interface ProcessFactoryInterface
{
    public function create(
        $commandLine,
        ?string $cwd = null,
        array $env = null,
        $input = null,
        $timeout = 60
    ): Process;
}
