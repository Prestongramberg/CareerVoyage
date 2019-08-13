<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SingleChatRepository")
 */
class SingleChat extends Chat
{
    /**
     * @Groups({"CHAT"})
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="singleChats")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
