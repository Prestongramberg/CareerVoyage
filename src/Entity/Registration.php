<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="App\Repository\RegistrationRepository")
 * @ORM\Table(
 *      uniqueConstraints={@ORM\UniqueConstraint(columns={"user_id", "experience_id"})}
 * )
 * @UniqueEntity(
 *     fields={"user", "experience"}
 * )
 */
class Registration
{
    use Timestampable;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="registrations")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Experience", inversedBy="registrations")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $experience;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $approved = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getExperience(): ?Experience
    {
        return $this->experience;
    }

    public function setExperience(?Experience $experience): self
    {
        $this->experience = $experience;

        return $this;
    }

    public function getApproved(): ?bool
    {
        return $this->approved;
    }

    public function setApproved(?bool $approved): self
    {
        $this->approved = $approved;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusLabel(): ?string
    {
        switch ($this->status) {
            case Request::REQUEST_STATUS_DENIED:
                $statusLabel = 'Your registration for this experience has been denied.';
                break;
            case Request::REQUEST_STATUS_APPROVED:
                $statusLabel = 'Your registration for this experience has been approved.';
                break;
            default:
                $statusLabel = 'Your registration for this experience is pending approval.';
                break;
        }

        return $statusLabel;
    }

}
