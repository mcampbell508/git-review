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

namespace GitReview\Commit;

use GitReview\Review\ReviewableInterface;

interface CommitMessageInterface extends ReviewableInterface
{
    public function getSubject();

    public function getBody();

    public function getHash();
}
