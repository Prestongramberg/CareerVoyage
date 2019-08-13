<?php

namespace App\Entity;

use App\Model\Message;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChatRepository")
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"singleChat" = "SingleChat"})
 */
abstract class Chat
{
    /**
     * @Groups({"CHAT"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @Groups({"CHAT"})
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="initializedChats")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $initializedBy;

    /**
     * @Groups({"CHAT"})
     * @var Message|[]
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $messages;


    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInitializedBy(): ?User
    {
        return $this->initializedBy;
    }

    public function setInitializedBy(?User $initializedBy): self
    {
        $this->initializedBy = $initializedBy;

        return $this;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @param Message $message
     * @return $this
     */
    public function addMessage(Message $message)
    {
        $this->messages->add($message);
        return $this;
    }
}
