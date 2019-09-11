<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageReadStatusRepository")
 */
class MessageReadStatus
{
    /**
     * @Groups({"MESSAGE"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ChatMessage", inversedBy="messageReadStatuses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $chatMessage;

    /**
     * @Groups({"MESSAGE"})
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="messageReadStatuses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @Groups({"MESSAGE"})
     * @ORM\Column(type="boolean")
     */
    private $isRead = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChatMessage(): ?ChatMessage
    {
        return $this->chatMessage;
    }

    public function setChatMessage(?ChatMessage $chatMessage): self
    {
        $this->chatMessage = $chatMessage;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }
}
