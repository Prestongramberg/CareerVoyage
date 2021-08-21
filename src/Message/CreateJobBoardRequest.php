<?php

namespace App\Message;

class CreateJobBoardRequest
{
    private $requestId;

    public function __construct(int $requestId)
    {
        $this->requestId = $requestId;
    }

    public function getRequestId(): int
    {
        return $this->requestId;
    }
}