<?php

namespace App\Message;


class NewEventNotificationMessage
{
    private $eventId;

    public function __construct(int $eventId)
    {
        $this->eventId = $eventId;
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }
}