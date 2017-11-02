<?php

namespace StaticReview\VersionControl;

interface GitBranchInterface
{
    public function getName();
    public function isInDetachedHeadState();
}