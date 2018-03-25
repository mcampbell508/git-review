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

namespace GitReview\File;

use GitReview\Review\ReviewableInterface;

interface FileInterface extends ReviewableInterface
{
    public function getFileName();

    public function getRelativePath();

    public function getFullPath();

    public function getCachedPath(): ?string;

    public function setCachedPath(string $path): File;

    public function getExtension();

    public function getStatus();

    public function getFormattedStatus();

    public function getMimeType();
}
