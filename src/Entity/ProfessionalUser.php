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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phoneExt;

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
     *      minMessage = "Please select your profession(s)",
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
     * 
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "Please select at least one role",
     *     groups={"EDIT"}
     * )
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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentToMeetProfessionalRequest", mappedBy="professional")
     */
    private $studentToMeetProfessionalRequests;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AllowedCommunication", mappedBy="professionalUser")
     */
    private $allowedCommunications;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $longitude;

    /**
     * @Assert\NotBlank(message="Don't forget a street!", groups={"PROFESSIONAL_USER"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $street;

    /**
     * @Assert\NotBlank(message="Don't forget a city!", groups={"PROFESSIONAL_USER"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @Assert\NotBlank(message="Don't forget a zipcode!", groups={"PROFESSIONAL_USER"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $zipcode;

    /**
     * @Assert\NotBlank(message="Don't forget a state!", groups={"PROFESSIONAL_USER"})
     * @ORM\ManyToOne(targetEntity="App\Entity\State", inversedBy="professionalUsers")
     */
    private $state;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProfessionalReviewMeetStudentExperienceFeedback", mappedBy="professional")
     */
    private $professionalReviewMeetStudentExperienceFeedback;

    /**
	 * @var string
	 */
    private $geoRadius;

	/**
	 * @var string
	 */
    private $geoZipCode;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProfessionalVideo", mappedBy="professional")
     */
    private $professionalVideos;

    /**
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "Please select at least one region",
     *      groups={"EDIT"}
     * )
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Region", inversedBy="professionalUsers")
     */
    private $regions;

    
    public function __construct()
    {
        parent::__construct();
        $this->secondaryIndustries = new ArrayCollection();
        $this->schools = new ArrayCollection();
        $this->rolesWillingToFulfill = new ArrayCollection();
        $this->companyExperiences = new ArrayCollection();
        $this->teachLessonExperiences = new ArrayCollection();
        $this->studentToMeetProfessionalRequests = new ArrayCollection();
        $this->allowedCommunications = new ArrayCollection();
        $this->professionalReviewMeetStudentExperienceFeedback = new ArrayCollection();
        $this->professionalVideos = new ArrayCollection();
        $this->regions = new ArrayCollection();
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

    public function getPhoneExt(): ?string
    {
        return $this->phoneExt;
    }

    public function setPhoneExt(?string $phoneExt): self
    {
        $this->phoneExt = $phoneExt;

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

    public function isVirtual() {

        foreach($this->getRolesWillingToFulfill() as $rolesWillingToFulfill) {
            if (stripos(strtolower($rolesWillingToFulfill->getName()), 'virtual') !== false) {
                return true;
            }
        }

        return false;
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

    /**
     * @return Collection|StudentToMeetProfessionalRequest[]
     */
    public function getStudentToMeetProfessionalRequests(): Collection
    {
        return $this->studentToMeetProfessionalRequests;
    }

    public function addStudentToMeetProfessionalRequest(StudentToMeetProfessionalRequest $studentToMeetProfessionalRequest): self
    {
        if (!$this->studentToMeetProfessionalRequests->contains($studentToMeetProfessionalRequest)) {
            $this->studentToMeetProfessionalRequests[] = $studentToMeetProfessionalRequest;
            $studentToMeetProfessionalRequest->setProfessional($this);
        }

        return $this;
    }

    public function removeStudentToMeetProfessionalRequest(StudentToMeetProfessionalRequest $studentToMeetProfessionalRequest): self
    {
        if ($this->studentToMeetProfessionalRequests->contains($studentToMeetProfessionalRequest)) {
            $this->studentToMeetProfessionalRequests->removeElement($studentToMeetProfessionalRequest);
            // set the owning side to null (unless already changed)
            if ($studentToMeetProfessionalRequest->getProfessional() === $this) {
                $studentToMeetProfessionalRequest->setProfessional(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AllowedCommunication[]
     */
    public function getAllowedCommunications(): Collection
    {
        return $this->allowedCommunications;
    }

    public function addAllowedCommunication(AllowedCommunication $allowedCommunication): self
    {
        if (!$this->allowedCommunications->contains($allowedCommunication)) {
            $this->allowedCommunications[] = $allowedCommunication;
            $allowedCommunication->setProfessionalUser($this);
        }

        return $this;
    }

    public function removeAllowedCommunication(AllowedCommunication $allowedCommunication): self
    {
        if ($this->allowedCommunications->contains($allowedCommunication)) {
            $this->allowedCommunications->removeElement($allowedCommunication);
            // set the owning side to null (unless already changed)
            if ($allowedCommunication->getProfessionalUser() === $this) {
                $allowedCommunication->setProfessionalUser(null);
            }
        }

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode): self
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getFormattedAddress() {
        return sprintf("%s %s %s %s",
            $this->street,
            $this->city,
            $this->state ? $this->state->getAbbreviation() : '',
            $this->zipcode
        );
    }

    /**
     * @return Collection|ProfessionalReviewMeetStudentExperienceFeedback[]
     */
    public function getProfessionalReviewMeetStudentExperienceFeedback(): Collection
    {
        return $this->professionalReviewMeetStudentExperienceFeedback;
    }

    public function addProfessionalReviewMeetStudentExperienceFeedback(ProfessionalReviewMeetStudentExperienceFeedback $professionalReviewMeetStudentExperienceFeedback): self
    {
        if (!$this->professionalReviewMeetStudentExperienceFeedback->contains($professionalReviewMeetStudentExperienceFeedback)) {
            $this->professionalReviewMeetStudentExperienceFeedback[] = $professionalReviewMeetStudentExperienceFeedback;
            $professionalReviewMeetStudentExperienceFeedback->setProfessional($this);
        }

        return $this;
    }

    public function removeProfessionalReviewMeetStudentExperienceFeedback(ProfessionalReviewMeetStudentExperienceFeedback $professionalReviewMeetStudentExperienceFeedback): self
    {
        if ($this->professionalReviewMeetStudentExperienceFeedback->contains($professionalReviewMeetStudentExperienceFeedback)) {
            $this->professionalReviewMeetStudentExperienceFeedback->removeElement($professionalReviewMeetStudentExperienceFeedback);
            // set the owning side to null (unless already changed)
            if ($professionalReviewMeetStudentExperienceFeedback->getProfessional() === $this) {
                $professionalReviewMeetStudentExperienceFeedback->setProfessional(null);
            }
        }

        return $this;
    }


    /**
	 * @return string
	 */
	public function getGeoRadius(): ?string {
                                       		return $this->geoRadius;
                                       	}

	/**
	 * @param string $geoRadius
	 */
	public function setGeoRadius( ?string $geoRadius ): void {
                                       		$this->geoRadius = $geoRadius;
                                       	}

	/**
	 * @return string
	 */
	public function getGeoZipCode(): ?string {
                                       		return $this->geoZipCode;
                                       	}

	/**
	 * @param string $geoZipCode
	 */
	public function setGeoZipCode( ?string $geoZipCode ): void {
                                       		$this->geoZipCode = $geoZipCode;
                                       	}

    /**
     * @return Collection|ProfessionalVideo[]
     */
    public function getProfessionalVideos(): Collection
    {
        return $this->professionalVideos;
    }

    public function addProfessionalVideo(ProfessionalVideo $professionalVideo): self
    {
        if (!$this->professionalVideos->contains($professionalVideo)) {
            $this->professionalVideos[] = $professionalVideo;
            $professionalVideo->setProfessional($this);
        }

        return $this;
    }

    public function removeProfessionalVideo(ProfessionalVideo $professionalVideo): self
    {
        if ($this->professionalVideos->contains($professionalVideo)) {
            $this->professionalVideos->removeElement($professionalVideo);
            // set the owning side to null (unless already changed)
            if ($professionalVideo->getProfessional() === $this) {
                $professionalVideo->setProfessional(null);
            }
        }

        return $this;
    }


    public function getActive(): self
    {
        if ($this->user->deleted == false && $this->user->activated == true) {
            return $this;
        } else {
            return null;
        }
    }

    /**
     * @return Collection|Region[]
     */
    public function getRegions(): Collection
    {
        return $this->regions;
    }

    public function addRegion(Region $region): self
    {
        if (!$this->regions->contains($region)) {
            $this->regions[] = $region;
        }

        return $this;
    }

    public function removeRegion(Region $region): self
    {
        if ($this->regions->contains($region)) {
            $this->regions->removeElement($region);
        }

        return $this;
    }
}
