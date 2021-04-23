<?php

namespace App\Entity;

use App\Repository\ShareRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShareRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Share
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="sentFromShares")
     */
    private $sentFrom;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="sentToShares")
     */
    private $sentTo;

    /**
     * @ORM\ManyToOne(targetEntity=Experience::class, inversedBy="shares")
     */
    private $experience;

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

    public function getSentTo(): ?User
    {
        return $this->sentTo;
    }

    public function setSentTo(?User $sentTo): self
    {
        $this->sentTo = $sentTo;

        return $this;
    }

    public function getExperience(): ?Experience
    {
        return $this->experience;
    }

    public function setExperience(?Experience $experience): self
    {
        $this->experience = $experience;

        return $this;
    }
}
