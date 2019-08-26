<?php

namespace App\Entity;

use App\Model\Message;
use App\Util\RandomStringGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChatRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"singleChat" = "SingleChat"})
 */
abstract class Chat
{
    use RandomStringGenerator;
    use Timestampable;

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
     * @ORM\Column(type="string", length=255)
     */
    private $uid;

    /**
     * @Groups({"CHAT"})
     * @ORM\OneToMany(targetEntity="App\Entity\ChatMessage", mappedBy="chat")
     */
    private $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->uid = $this->generateRandomString(40);
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

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * @return Collection|ChatMessage[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(ChatMessage $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setChat($this);
        }

        return $this;
    }

    public function removeMessage(ChatMessage $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getChat() === $this) {
                $message->setChat(null);
            }
        }

        return $this;
    }
}
