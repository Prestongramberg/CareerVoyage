<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfessionalUserRepository")
 */
class ProfessionalUser extends User
{

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $briefBio;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $linkedinProfile;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $rolesWillingToFulfill = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="professionalUsers")
     */
    private $company;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Image", inversedBy="professionalUser", cascade={"persist","remove"})
     */
    private $photo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $interests;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    /**
     * @Assert\Callback(groups={"EDIT"})
     * @param ExecutionContextInterface $context
     * @param $payload
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if($this->phone && !preg_match("/^[0-9]{3}-[0-9]{4}-[0-9]{4}$/", $this->phone)) {
            $context->buildViolation('The phone number needs to be in this format: xxx-xxx-xxx')
                ->atPath('phone')
                ->addViolation();
        }
    }

    /**
     * @ORM\Column(type="boolean")
     */
    private $deactivated;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBriefBio(): ?string
    {
        return $this->briefBio;
    }

    public function setBriefBio(?string $briefBio): self
    {
        $this->briefBio = $briefBio;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getLinkedinProfile(): ?string
    {
        return $this->linkedinProfile;
    }

    public function setLinkedinProfile(?string $linkedinProfile): self
    {
        $this->linkedinProfile = $linkedinProfile;

        return $this;
    }

    public function getRolesWillingToFulfill(): ?array
    {
        return $this->rolesWillingToFulfill;
    }

    public function setRolesWillingToFulfill(?array $rolesWillingToFulfill): self
    {
        $this->rolesWillingToFulfill = $rolesWillingToFulfill;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getPhoto(): ?Image
    {
        return $this->photo;
    }

    public function setPhoto(?Image $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getInterests(): ?string
    {
        return $this->interests;
    }

    public function setInterests(?string $interests): self
    {
        $this->interests = $interests;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDeactivated(): ?bool
    {
        return $this->deactivated;
    }

    public function setDeactivated(bool $deactivated): self
    {
        $this->deactivated = $deactivated;

        return $this;
    }
}
