<?php

namespace GitReview\Commit;

use Tightenco\Collect\Support\Collection;

class CommitCollection
{
    private $commitCollection;

    public function __construct()
    {
        $this->commitCollection = new Collection([]);
    }

    public function add(Commit $commit): void
    {
        $this->commitCollection[] = $commit;
    }

    public function getCommitCollection(): Collection
    {
        return $this->commitCollection;
    }

    public function count(): int
    {
        return $this->getCommitCollection()->count();
    }

    public function getFiles(): void
    {
    }

    public function getAuthorNames(): Collection
    {
        return $this->getCommitCollection()->reduce(function (Collection $authors, Commit $commit) {
            return $authors->push($commit->getAuthor()->getAuthorName());
        }, collect())->unique();
    }
}
