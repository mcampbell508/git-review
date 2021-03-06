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

namespace GitReview\Review\PHP;

use GitReview\File\FileInterface;
use GitReview\Reporter\ReporterInterface;
use GitReview\Review\AbstractFileReview;
use GitReview\Review\ReviewableInterface;

class PhpLeadingLineReview extends AbstractFileReview
{
    /**
     * Determins if the given file should be revewed.
     *
     * @param  FileInterface $file
     * @return bool
     */
    public function canReviewFile(FileInterface $file)
    {
        return $file->getExtension() === 'php';
    }

    /**
     * Checks if the set file starts with the correct character sequence, which
     * helps to stop any rouge whitespace making it in before the first php tag.
     *
     * @link http://stackoverflow.com/a/2440685
     */
    public function review(ReporterInterface $reporter, ReviewableInterface $file): void
    {
        $cmd = \sprintf('read -r LINE < %s && echo $LINE', $file->getFullPath());

        $process = $this->getProcess($cmd);
        $process->run();

        if (!\in_array(\trim($process->getOutput()), ['<?php', '#!/usr/bin/env php'])) {
            $message = 'File must begin with `<?php` or `#!/usr/bin/env php`';
            $reporter->error($message, $this, $file);
        }
    }
}
