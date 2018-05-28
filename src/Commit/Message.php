<?php

namespace GitReview\Commit;

class Message
{
    private $subject;
    private $body;

    public function __construct(string $subject, string $body)
    {
        $this->subject = $subject;
        $this->body = $body;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getSubjectLength(): int
    {
        return \mb_strlen($this->subject);
    }

    public function getBodyLength(): int
    {
        return \mb_strlen($this->body);
    }

    public function bodyContains(string $matches): bool
    {
        return preg_match("/^(See fb\d{1,6}|see \d{1,6}|https:\/\/shopworks.fogbugz.com\/f\/cases\/\d{1,6}|bugzid: \d{1,6}|fb \d{1,6}|fb\d{1,6})/i", $input_line, $output_array);
    }
}
