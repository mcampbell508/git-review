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

class File implements FileInterface
{
    public const STATUS_ADDED = 'A';
    public const STATUS_COPIED = 'C';
    public const STATUS_MODIFIED = 'M';
    public const STATUS_RENAMED = 'R';
    public const STATUS_DELETED = 'D';
    public const STATUS_UNSTAGED = '??';

    private $filePath;
    private $fileStatus;
    private $projectPath;
    private $cachedPath;

    public function __construct(
        string $fileStatus,
        string $filePath,
        string $projectPath
    ) {
        $this->fileStatus = $fileStatus;
        $this->filePath = $filePath;
        $this->projectPath = $projectPath;
    }

    public function getFileName(): string
    {
        return \basename($this->filePath);
    }

    public function getRelativePath(): string
    {
        return \str_replace($this->projectPath . DIRECTORY_SEPARATOR, '', $this->filePath);
    }

    public function getFullPath(): string
    {
        if (\file_exists($this->getCachedPath())) {
            return $this->getCachedPath();
        }

        return $this->filePath;
    }

    public function getCachedPath(): ?string
    {
        return $this->cachedPath;
    }

    public function setCachedPath(string $path): self
    {
        $this->cachedPath = $path;

        return $this;
    }

    public function getExtension(): string
    {
        return \pathinfo($this->filePath, PATHINFO_EXTENSION);
    }

    /**
     * Returns the short hand git status of the file.
     */
    public function getStatus(): string
    {
        return $this->fileStatus;
    }

    /**
     * Returns the git status of the file as a word.
     *
     * @throws UnexpectedValueException
     */
    public function getFormattedStatus(): string
    {
        switch ($this->fileStatus) {
            case 'A':
                return 'added';
            case 'C':
                return 'copied';
            case 'M':
                return 'modified';
            case 'R':
                return 'renamed';
            case 'D':
                return 'deleted';
            case '??':
                return 'modified changes but not yet staged';
            default:
                throw new \UnexpectedValueException("Unknown file status: {$this->fileStatus}.");
        }
    }

    public function getMimeType(): string
    {
        // return mime type ala mimetype extension
        $finfo = \finfo_open(FILEINFO_MIME);

        $mime = \finfo_file($finfo, $this->getFullPath());

        return $mime;
    }

    /**
     * Get the relative path name as the reviewable name.
     */
    public function getName(): string
    {
        return $this->getRelativePath();
    }

    public function getBody(): ?string
    {
        return null;
    }

    public function getSubject(): ?string
    {
        return null;
    }
}
