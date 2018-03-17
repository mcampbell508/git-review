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

abstract class AbstractFileReview extends AbstractReview
{
    protected function canReviewMessage(CommitMessageInterface $message)
    {
        return false;
    }
}
