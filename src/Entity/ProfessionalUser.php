<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfessionalUserRepository")
 */
class ProfessionalUser extends User
{

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $briefBio;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @Assert\Regex(
     *     pattern="/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/",
     *     match=true,
     *     message="The phone number needs to be in this format: xxxxxxxxxx",
     *     groups={"CREATE", "EDIT"}
     * )
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $linkedinProfile;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $rolesWillingToFulfill = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="professionalUsers")
     * @JoinColumn(name="company_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $company;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $interests;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @ORM\Column(type="boolean")
     */
    private $deactivated = 0;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Company", mappedBy="owner", cascade={"persist", "remove"})
     */
    private $ownedCompany;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Experience", mappedBy="employeeContact", cascade={"persist", "remove"})
     */
    private $experience;

    /**
     * @Assert\NotBlank(message="Don't forget a primary industry!", groups={"CREATE", "EDIT"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Industry", inversedBy="professionalUsers")
     */
    private $primaryIndustry;

    /**
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "You must specify at least one secondary industry",
     *     groups={"SECONDARY_INDUSTRY"}
     * )
     * @ORM\ManyToMany(targetEntity="App\Entity\SecondaryIndustry", inversedBy="professionalUsers")
     */
    private $secondaryIndustries;

    /**
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "You must specify at least one school",
     *     groups={"EDIT"}
     * )
     * @ORM\ManyToMany(targetEntity="App\Entity\School", inversedBy="professionalUsers")
     */
    private $schools;

    public function __construct()
    {
        parent::__construct();
        $this->secondaryIndustries = new ArrayCollection();
        $this->schools = new ArrayCollection();
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

    public function getInterests(): ?string
    {
        return $this->interests;
    }

    public function setInterests(?string $interests): self
    {
        $this->interests = $interests;

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

    public function getPhotoPath()
    {
        return UploaderHelper::PROFILE_PHOTO.'/'.$this->getPhoto();
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo): void
    {
        $this->photo = $photo;
    }

    public function getOwnedCompany(): ?Company
    {
        return $this->ownedCompany;
    }

    public function setOwnedCompany(?Company $ownedCompany): self
    {
        $this->ownedCompany = $ownedCompany;

        // set (or unset) the owning side of the relation if necessary
        $newOwner = $ownedCompany === null ? null : $this;
        if ($newOwner !== $ownedCompany->getOwner()) {
            $ownedCompany->setOwner($newOwner);
        }

        return $this;
    }

    public function getExperience(): ?Experience
    {
        return $this->experience;
    }

    public function setExperience(Experience $experience): self
    {
        $this->experience = $experience;

        // set the owning side of the relation if necessary
        if ($this !== $experience->getEmployeeContact()) {
            $experience->setEmployeeContact($this);
        }

        return $this;
    }

    public function isOwner(Company $company) {
        return $this->getId() === $company->getOwner()->getId();
    }

    public function getPrimaryIndustry(): ?Industry
    {
        return $this->primaryIndustry;
    }

    public function setPrimaryIndustry(?Industry $primaryIndustry): self
    {
        $this->primaryIndustry = $primaryIndustry;

        return $this;
    }

    /**
     * @return Collection|SecondaryIndustry[]
     */
    public function getSecondaryIndustries(): Collection
    {
        return $this->secondaryIndustries;
    }

    public function addSecondaryIndustry(SecondaryIndustry $secondaryIndustry): self
    {
        if (!$this->secondaryIndustries->contains($secondaryIndustry)) {
            $this->secondaryIndustries[] = $secondaryIndustry;
        }

        return $this;
    }

    public function removeSecondaryIndustry(SecondaryIndustry $secondaryIndustry): self
    {
        if ($this->secondaryIndustries->contains($secondaryIndustry)) {
            $this->secondaryIndustries->removeElement($secondaryIndustry);
        }

        return $this;
    }

    /**
     * @return Collection|School[]
     */
    public function getSchools(): Collection
    {
        return $this->schools;
    }

    public function addSchool(School $school): self
    {
        if (!$this->schools->contains($school)) {
            $this->schools[] = $school;
        }

        return $this;
    }

    public function removeSchool(School $school): self
    {
        if ($this->schools->contains($school)) {
            $this->schools->removeElement($school);
        }

        return $this;
    }

}
