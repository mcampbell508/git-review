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

namespace GitReview\Review;

use GitReview\Commit\CommitMessageInterface;
use GitReview\File\FileInterface;

abstract class AbstractMessageReview extends AbstractReview
{
    protected function canReviewFile(FileInterface $file)
    {
        return false;
    }

    protected function canReviewMessage(CommitMessageInterface $message)
    {
        return true;
    }
}
