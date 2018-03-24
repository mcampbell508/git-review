<?php

namespace GitReview\Commit;

class SanitizeMessage
{
    public $message;

    public function __construct(string $message)
    {
        $this->message = $message;

        $this->sanitizeMessage();
    }

    public function getSanitizedMessage(): array
    {
        return $this->message;
    }

    private function sanitizeMessage(): void
    {
        $this->stripOutDiff();
        $this->removeComments();
        $this->splitMessageByNewLines();
    }

    /**
     *  Strip out the diff that is included in the commit message when
     *  using `git commit -v` as we should not check it as text.
     *
     */
    private function stripOutDiff(): void
    {
        $this->message = \preg_split('/# \-+ >8 \-+/', $this->message, 2);
    }

    /**
     * Remove all comment lines from the message.
     */
    private function removeComments(): void
    {
        [$message] = $this->message;
        $this->message = \preg_replace('/^#.*/m', '', $message);
    }

    private function splitMessageByNewLines(): void
    {
        $this->message = \preg_split('/(\r?\n)+/', \trim($this->message));
    }
}
