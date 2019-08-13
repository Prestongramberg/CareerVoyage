<?php

namespace App\Model;

use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Message
 * @package App\Model
 */
class Message
{
    /**
     * @Groups({"MESSAGE"})
     * @var User $user
     */
    protected $from;

    /**
     * @Groups({"MESSAGE"})
     * @var string $body
     */
    protected $body;

    /**
     * @Groups({"MESSAGE"})
     * @var \DateTime $sentAt
     */
    protected $sentAt;

    /**
     * @return User
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param User $from
     * @return Message
     */
    public function setFrom(User $from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return Message
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * @param \DateTime $sentAt
     * @return Message
     */
    public function setSentAt(\DateTime $sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * @Groups({"MESSAGE"})
     */
    public function getFormattedSentDate() {

        return $this->sentAt->format('Y-m-d H:i:s');

    }
}