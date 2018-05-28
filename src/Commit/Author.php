<?php

namespace GitReview\Commit;

class Author
{
    private $authorName;
    private $authorDate;
    private $authorEmail;
    private $committerName;
    private $committerDate;
    private $committerEmail;

    public function __construct(
        string $authorName,
        string $authorDate,
        string $authorEmail,
        string $committerName,
        string $committerDate,
        string $committerEmail
    ) {
        $this->authorName = $authorName;
        $this->authorDate = $authorDate;
        $this->authorEmail = $authorEmail;
        $this->committerName = $committerName;
        $this->committerDate = $committerDate;
        $this->committerEmail = $committerEmail;
    }

    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    public function getAuthorDate(): string
    {
        return $this->authorDate;
    }

    public function getAuthorEmail(): string
    {
        return $this->authorEmail;
    }

    public function getCommitterName(): string
    {
        return $this->committerName;
    }

    public function getCommitterDate(): string
    {
        return $this->committerDate;
    }

    public function getCommitterEmail(): string
    {
        return $this->committerEmail;
    }

    public function presentNameAndEmail()
    {
        return $this->getAuthorName() . ' ' . $this->getAuthorEmail();
    }
}
