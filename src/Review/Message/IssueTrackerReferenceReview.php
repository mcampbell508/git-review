<?php

namespace StaticReview\Review\Message;

use StaticReview\Reporter\ReporterInterface;
use StaticReview\Review\AbstractMessageReview;
use StaticReview\Review\ReviewableInterface;

class IssueTrackerReferenceReview extends AbstractMessageReview
{

    /**
     * @param ReporterInterface   $reporter
     * @param ReviewableInterface $subject
     */
    public function review(ReporterInterface $reporter, ReviewableInterface $subject)
    {
        var_dump($subject->getName());
    }
}