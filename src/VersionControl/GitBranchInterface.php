<?php

namespace GitReview\VersionControl;

interface GitBranchInterface
{
    public function getName();

    public function isInDetachedHeadState();
}
