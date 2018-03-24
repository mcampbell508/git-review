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

namespace GitReview\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class HookRunCommand extends Command
{
    public const ARGUMENT_HOOK = 'hook';

    protected function configure(): void
    {
        $this->setName('hook:run');

        $this->setDescription('Run the specified hook.');

        $this->addArgument(self::ARGUMENT_HOOK, InputArgument::REQUIRED, 'The hook file to run.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $hookArg = $input->getArgument(self::ARGUMENT_HOOK);
        $path = $this->getTargetPath($hookArg, $output);

        if (\file_exists($path)) {
            $cmd = 'php ' . $path;

            $process = new Process($cmd);

            $process->run(function ($type, $buffer) use ($output): void {
                $output->write($buffer);
            });
        }
    }

    protected function getTargetPath(string $hookArgument, OutputInterface $output): string
    {
        if (\file_exists($hookArgument)) {
            $target = \realpath($hookArgument);
        } else {
            $path = '%s/%s.php';
            $target = \sprintf($path, \realpath(__DIR__ . '/../../hooks/'), $hookArgument);
        }

        if (!\file_exists($target)) {
            $error = \sprintf('<error>The hook %s does not exist!</error>', $target);
            $output->writeln($error);
            exit(1);
        }

        return $target;
    }
}
