<?php

namespace GitReview\Commit;

use GitReview\File\FileCollection;

class Commit
{
    private $hash;
    private $parents;
    private $author;
    private $commitMessage;
    private $fileCollection;

    public function __construct(
        string $hash,
        array $parents,
        Message $commitMessage,
        Author $author,
        FileCollection $fileCollection
    ) {
        $this->hash = $hash;
        $this->parents = $parents;
        $this->author = $author;
        $this->fileCollection = $fileCollection;
        $this->commitMessage = $commitMessage;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getCommitMessage(): Message
    {
        return $this->commitMessage;
    }

    public function getFileCollection(): FileCollection
    {
        return $this->fileCollection;
    }

    public function getAuthor(): Author
    {
        return $this->author;
    }

    public function isWorkInProgress(): bool
    {
        $message = strtolower($this->getCommitMessage()->getSubject());

        return substr($message, 0, 3 ) === "wip";
    }

    public function isFixup(): bool
    {
        $message = strtolower($this->getCommitMessage()->getSubject());

        return substr($message, 0, 7 ) === "fixup! ";
    }

    public function isDraft(): bool
    {
        return $this->isWorkInProgress() || $this->isFixup();
    }

    public function isEmpty(): bool
    {
        //return if commit is not merge commit and does not contain modified files
    }
}
