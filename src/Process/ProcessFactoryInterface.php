<?php

namespace GitReview\Process;

interface ProcessFactoryInterface
{
    public function create(
        $commandLine,
        $cwd = null,
        array $env = null,
        $input = null,
        $timeout = 60,
        array $options = []
    ): Process;
}
