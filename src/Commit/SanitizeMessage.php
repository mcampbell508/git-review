<?php

namespace StaticReview\Commit;

class SanitizeMessage
{
    public $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function sanitizeMessage(): void
    {
        $this->stripOutDiff();
        $this->removeComments();
        $this->splitMessageByNewLines();
    }

    public function getSanitizedMessage()
    {
        return $this->message;
    }

    /**
     *  Strip out the diff that is included in the commit message when
     *  using `git commit -v` as we should not check it as text.
     *
     */
    private function stripOutDiff(): void
    {
        $this->message = preg_split('/# \-+ >8 \-+/', $this->message, 2);
    }

    /**
     * Remove all comment lines from the message.
     */
    private function removeComments(): void
    {
        list($message) = $this->message;
        $this->message = preg_replace('/^#.*/m', '', $message);
    }

    private function splitMessageByNewLines(): void
    {
        $this->message = preg_split('/(\r?\n)+/', trim($this->message));
    }
}
