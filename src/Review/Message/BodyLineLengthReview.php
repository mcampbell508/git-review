<?php

/*
 * This file is part of StaticReview
 *
 * Copyright (c) 2015 Woody Gilk <@shadowhand>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see http://github.com/sjparkinson/static-review/blob/master/LICENSE
 */

namespace GitReview\Review\Message;

use GitReview\Reporter\ReporterInterface;
use GitReview\Review\AbstractMessageReview;
use GitReview\Review\ReviewableInterface;

/**
 * Rule 6: Wrap the body at 72 characters
 *
 * <http://chris.beams.io/posts/git-commit/#wrap-72>
 */
class BodyLineLengthReview extends AbstractMessageReview
{
    /**
     * @var int Allowed length limit.
     */
    protected $maximum = 72;

    /**
     * @var bool Allow long URLs to exceed the maximum.
     */
    protected $urls = true;

    public function setMaximumLength($length): void
    {
        $this->maximum = $length;
    }

    public function getMaximumLength()
    {
        return $this->maximum;
    }

    public function setAllowLongUrls($enable): void
    {
        $this->urls = (bool)$enable;
    }

    public function getAllowLongUrls()
    {
        return $this->urls;
    }

    public function review(ReporterInterface $reporter, ReviewableInterface $commit): void
    {
        $lines = \preg_split('/(\r?\n)+/', $commit->getBody());
        foreach ($lines as $line) {
            if ($this->isLineTooLong($line) && !$this->doesContainUrl($line)) {
                $message = \sprintf(
                    'Body line is greater than %d characters ( "%s ..." )',
                    $this->getMaximumLength(),
                    \mb_substr($line, 0, 16)
                );
                $reporter->error($message, $this, $commit);
            }
        }
    }

    private function isLineTooLong($line)
    {
        return \mb_strlen($line) > $this->getMaximumLength();
    }

    private function doesContainUrl($line)
    {
        if ($this->getAllowLongUrls()) {
            // It might contain a URL, but URL checking is disabled.
            return false;
        }

        return \mb_strpos($line, '://') !== false;
    }
}
