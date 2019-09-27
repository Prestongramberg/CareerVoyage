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
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="professionalUsers")
     * @JoinColumn(name="company_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $company;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $interests;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @ORM\OneToOne(targetEntity="App\Entity\Company", mappedBy="owner")
     */
    private $ownedCompany;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @Assert\NotBlank(message="Don't forget a primary industry!", groups={"CREATE", "EDIT"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Industry", inversedBy="professionalUsers")
     */
    private $primaryIndustry;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "You must select at least one career field",
     *     groups={"SECONDARY_INDUSTRY"}
     * )
     * @ORM\ManyToMany(targetEntity="App\Entity\SecondaryIndustry", inversedBy="professionalUsers")
     */
    private $secondaryIndustries;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\School", inversedBy="professionalUsers")
     */
    private $schools;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @ORM\ManyToMany(targetEntity="App\Entity\RolesWillingToFulfill", inversedBy="professionalUsers")
     */
    private $rolesWillingToFulfill;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompanyExperience", mappedBy="employeeContact")
     */
    private $companyExperiences;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeachLessonExperience", mappedBy="teacher")
     */
    private $teachLessonExperiences;

    public function __construct()
    {
        parent::__construct();
        $this->secondaryIndustries = new ArrayCollection();
        $this->schools = new ArrayCollection();
        $this->rolesWillingToFulfill = new ArrayCollection();
        $this->companyExperiences = new ArrayCollection();
        $this->teachLessonExperiences = new ArrayCollection();
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

    /**
     * @return Collection|RolesWillingToFulfill[]
     */
    public function getRolesWillingToFulfill(): Collection
    {
        return $this->rolesWillingToFulfill;
    }

    public function addRolesWillingToFulfill(RolesWillingToFulfill $rolesWillingToFulfill): self
    {
        if (!$this->rolesWillingToFulfill->contains($rolesWillingToFulfill)) {
            $this->rolesWillingToFulfill[] = $rolesWillingToFulfill;
        }

        return $this;
    }

    public function removeRolesWillingToFulfill(RolesWillingToFulfill $rolesWillingToFulfill): self
    {
        if ($this->rolesWillingToFulfill->contains($rolesWillingToFulfill)) {
            $this->rolesWillingToFulfill->removeElement($rolesWillingToFulfill);
        }

        return $this;
    }

    /**
     * @return Collection|CompanyExperience[]
     */
    public function getCompanyExperiences(): Collection
    {
        return $this->companyExperiences;
    }

    public function addCompanyExperience(CompanyExperience $companyExperience): self
    {
        if (!$this->companyExperiences->contains($companyExperience)) {
            $this->companyExperiences[] = $companyExperience;
            $companyExperience->setEmployeeContact($this);
        }

        return $this;
    }

    public function removeCompanyExperience(CompanyExperience $companyExperience): self
    {
        if ($this->companyExperiences->contains($companyExperience)) {
            $this->companyExperiences->removeElement($companyExperience);
            // set the owning side to null (unless already changed)
            if ($companyExperience->getEmployeeContact() === $this) {
                $companyExperience->setEmployeeContact(null);
            }
        }

        return $this;
    }

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @return string
     */
    public function getPhoneAfterPrivacySettingsApplied() {
        if(!$this->isPhoneHiddenFromProfile) {
            return $this->phone;
        }
        return '';
    }

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @return string
     */
    public function getEmailAfterPrivacySettingsApplied() {
        if(!$this->isEmailHiddenFromProfile) {
            return $this->email;
        }
        return '';
    }

    /**
     * @return Collection|TeachLessonExperience[]
     */
    public function getTeachLessonExperiences(): Collection
    {
        return $this->teachLessonExperiences;
    }

    public function addTeachLessonExperience(TeachLessonExperience $teachLessonExperience): self
    {
        if (!$this->teachLessonExperiences->contains($teachLessonExperience)) {
            $this->teachLessonExperiences[] = $teachLessonExperience;
            $teachLessonExperience->setTeacher($this);
        }

        return $this;
    }

    public function removeTeachLessonExperience(TeachLessonExperience $teachLessonExperience): self
    {
        if ($this->teachLessonExperiences->contains($teachLessonExperience)) {
            $this->teachLessonExperiences->removeElement($teachLessonExperience);
            // set the owning side to null (unless already changed)
            if ($teachLessonExperience->getTeacher() === $this) {
                $teachLessonExperience->setTeacher(null);
            }
        }

        return $this;
    }
}
