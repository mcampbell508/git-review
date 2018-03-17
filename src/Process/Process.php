<?php

namespace GitReview\Process;

use Symfony\Component\Process\Process as SymfonyProcess;

final class Process extends SymfonyProcess implements ProcessInterface
{
    public function getOutput()
    {
        return trim(parent::getOutput());
    }
}
