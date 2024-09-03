<?php

namespace WbmReviewVote\Exception;

class ReviewVoteInactiveException extends \Exception
{
    public function __construct(string $message = "Review Vote is inactive on this SalesChannel", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}