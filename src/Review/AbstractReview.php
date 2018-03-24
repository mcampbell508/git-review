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

namespace GitReview\Review;

use GitReview\Commit\CommitMessageInterface;
use GitReview\File\FileInterface;
use Symfony\Component\Process\Process;

abstract class AbstractReview implements ReviewInterface
{
    /**
     * Determine if the subject can be reviewed.
     *
     * @param  ReviewableInterface $subject
     * @return bool
     */
    public function canReview(ReviewableInterface $subject)
    {
        if ($subject instanceof FileInterface) {
            return $this->canReviewFile($subject);
        }
        if ($subject instanceof CommitMessageInterface) {
            return $this->canReviewMessage($subject);
        }

        return false;
    }

    /**
     * @param string      $commandline
     * @param null|string $cwd
     * @param null|array  $env
     * @param null|string $input
     * @param int         $timeout
     * @param array       $options
     *
     * @return Process
     */
    public function getProcess(
        $commandline,
        $cwd = null,
        array $env = null,
        $input = null,
        $timeout = 60,
        array $options = []
    ) {
        if (null === $cwd) {
            $cwd = $this->getRootDirectory();
        }

        return new Process($commandline, $cwd, $env, $input, $timeout, $options);
    }

    abstract protected function canReviewFile(FileInterface $file);

    abstract protected function canReviewMessage(CommitMessageInterface $message);

    /**
     * Get the root directory for a process command.
     *
     * @return string
     */
    private function getRootDirectory()
    {
        static $root;

        if (!$root) {
            $working = \getcwd();
            $myself = __DIR__;

            if (0 === \mb_strpos($myself, $working)) {
                // Local installation, the working directory is the root
                $root = $working;
            } else {
                // Global installation, back up above the vendor/ directory
                $root = \realpath($myself . '/../../../../../');
            }
        }

        return $root;
    }
}
