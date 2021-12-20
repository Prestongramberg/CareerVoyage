<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChatMessageRepository")
 * @ORM\HasLifecycleCallbacks()
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
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $sentFrom;

    /**
     * @Groups({"MESSAGE"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $body;

    /**
     * @Groups({"MESSAGE"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sentAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Chat", inversedBy="messages")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * 
     */
    private $chat;

    /**
     * @Groups({"MESSAGE"})
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="chatMessages")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $sentTo;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasBeenRead = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $emailSent = false;

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

    public function getSentTo(): ?User
    {
        return $this->sentTo;
    }

    public function setSentTo(?User $sentTo): self
    {
        $this->sentTo = $sentTo;

        return $this;
    }

    public function getHasBeenRead(): ?bool
    {
        return $this->hasBeenRead;
    }

    public function setHasBeenRead(bool $hasBeenRead): self
    {
        $this->hasBeenRead = $hasBeenRead;

        return $this;
    }

    public function getEmailSent(): ?bool
    {
        return $this->emailSent;
    }

    public function setEmailSent(bool $emailSent): self
    {
        $this->emailSent = $emailSent;

        return $this;
    }
}
