<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RolesWillingToFulfillRepository")
 */
class RolesWillingToFulfill
{
    /**
     * @Groups({"PROFESSIONAL_USER_DATA", "ALL_USER_DATA"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Don't forget a name!", groups={"NEW"})
     *
     * @Groups({"PROFESSIONAL_USER_DATA", "ALL_USER_DATA"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\ProfessionalUser", mappedBy="rolesWillingToFulfill")
     */
    private $professionalUsers;

    /**
     * @Assert\NotBlank(message="Don't forget a description!", groups={"NEW"})
     *
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $eventName;

    /**
     * @ORM\Column(type="boolean")
     */
    private $inEventDropdown = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Experience", mappedBy="type")
     */
    private $experiences;

    /**
     * @ORM\Column(type="boolean")
     */
    private $inSchoolEventDropdown = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $inRoleDropdown = false;

    public function __construct()
    {
        $this->professionalUsers = new ArrayCollection();
        $this->experiences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|ProfessionalUser[]
     */
    public function getProfessionalUsers(): Collection
    {
        return $this->professionalUsers;
    }

    public function addProfessionalUser(ProfessionalUser $professionalUser): self
    {
        if (!$this->professionalUsers->contains($professionalUser)) {
            $this->professionalUsers[] = $professionalUser;
            $professionalUser->addRolesWillingToFulfill($this);
        }

        return $this;
    }

    public function removeProfessionalUser(ProfessionalUser $professionalUser): self
    {
        if ($this->professionalUsers->contains($professionalUser)) {
            $this->professionalUsers->removeElement($professionalUser);
            $professionalUser->removeRolesWillingToFulfill($this);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function getInEventDropdown(): ?bool
    {
        return $this->inEventDropdown;
    }

    public function setInEventDropdown(bool $inEventDropdown): self
    {
        $this->inEventDropdown = $inEventDropdown;

        return $this;
    }

    /**
     * @return Collection|Experience[]
     */
    public function getExperiences(): Collection
    {
        return $this->experiences;
    }

    public function addExperience(Experience $experience): self
    {
        if (!$this->experiences->contains($experience)) {
            $this->experiences[] = $experience;
            $experience->setType($this);
        }

        return $this;
    }

    public function removeExperience(Experience $experience): self
    {
        if ($this->experiences->contains($experience)) {
            $this->experiences->removeElement($experience);
            // set the owning side to null (unless already changed)
            if ($experience->getType() === $this) {
                $experience->setType(null);
            }
        }

        return $this;
    }

    public function getInSchoolEventDropdown(): ?bool
    {
        return $this->inSchoolEventDropdown;
    }

    public function setInSchoolEventDropdown(bool $inSchoolEventDropdown): self
    {
        $this->inSchoolEventDropdown = $inSchoolEventDropdown;

        return $this;
    }

    public function getInRoleDropdown(): ?bool
    {
        return $this->inRoleDropdown;
    }

    public function setInRoleDropdown(?bool $inRoleDropdown): self
    {
        $this->inRoleDropdown = $inRoleDropdown;

        return $this;
    }
}
