<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChatMessageRepository")
 */
class ChatMessage
{
    use Timestampable;

    /**
     * @Groups({"MESSAGE"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"MESSAGE"})
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $sentFrom;

    /**
     * @Groups({"MESSAGE"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $body;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sentAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MessageReadStatus", mappedBy="chatMessage", orphanRemoval=true)
     */
    private $messageReadStatuses;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Chat", inversedBy="messages")
     */
    private $chat;

    public function __construct()
    {
        $this->messageReadStatuses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSentFrom(): ?User
    {
        return $this->sentFrom;
    }

    public function setSentFrom(?User $sentFrom): self
    {
        $this->sentFrom = $sentFrom;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeInterface $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * @return Collection|MessageReadStatus[]
     */
    public function getMessageReadStatuses(): Collection
    {
        return $this->messageReadStatuses;
    }

    public function addMessageReadStatus(MessageReadStatus $messageReadStatus): self
    {
        if (!$this->messageReadStatuses->contains($messageReadStatus)) {
            $this->messageReadStatuses[] = $messageReadStatus;
            $messageReadStatus->setChatMessage($this);
        }

        return $this;
    }

    public function removeMessageReadStatus(MessageReadStatus $messageReadStatus): self
    {
        if ($this->messageReadStatuses->contains($messageReadStatus)) {
            $this->messageReadStatuses->removeElement($messageReadStatus);
            // set the owning side to null (unless already changed)
            if ($messageReadStatus->getChatMessage() === $this) {
                $messageReadStatus->setChatMessage(null);
            }
        }

        return $this;
    }

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(?Chat $chat): self
    {
        $this->chat = $chat;

        return $this;
    }


    /**
     * @Groups({"MESSAGE"})
     */
    public function getFormattedSentDate() {

        if($this->sentAt) {
            return $this->sentAt->format('Y-m-d H:i:s');
        }

        return '';
    }
}
