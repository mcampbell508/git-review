<?php

namespace GitReview\VersionControl;

use Tightenco\Collect\Support\Collection;

interface GitBranchInterface
{
    public function getName(): string;

    public function getChangedFiles(): Collection;

    public function getParentHash();

    public function isDirty():bool;
}
