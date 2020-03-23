<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolExperienceRepository")
 */
class SchoolExperience extends Experience
{

	public static $types = [
	    'School Event' => 'IN_SCHOOL',
    ];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\School", inversedBy="schoolExperiences")
     * @ORM\JoinColumn(name="school_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $school;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolAdministrator", inversedBy="schoolExperiences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schoolContact;

    /**
     * @Assert\Positive(message="Don't forget a total number of available student spaces!", groups={"SCHOOL_EXPERIENCE"})
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="integer")
     */
    private $availableStudentSpaces = 0;

    /**
     * @Assert\Positive(message="Don't forget a total number of available professional spaces!", groups={"SCHOOL_EXPERIENCE"})
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="integer")
     */
    private $availableProfessionalSpaces =  0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserRegisterForSchoolExperienceRequest", mappedBy="schoolExperience", orphanRemoval=true)
     */
    private $userRegisterForSchoolExperienceRequests;

    public function __construct()
    {
        parent::__construct();
        $this->userRegisterForSchoolExperienceRequests = new ArrayCollection();
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getSchoolContact(): ?SchoolAdministrator
    {
        return $this->schoolContact;
    }

    public function setSchoolContact(?SchoolAdministrator $schoolContact): self
    {
        $this->schoolContact = $schoolContact;

        return $this;
    }

    public function getAvailableStudentSpaces(): ?int
    {
        return $this->availableStudentSpaces;
    }

    public function setAvailableStudentSpaces(?int $availableStudentSpaces): self
    {
        $this->availableStudentSpaces = $availableStudentSpaces;

        return $this;
    }

    public function getAvailableProfessionalSpaces(): ?int
    {
        return $this->availableProfessionalSpaces;
    }

    public function setAvailableProfessionalSpaces(?int $availableProfessionalSpaces): self
    {
        $this->availableProfessionalSpaces = $availableProfessionalSpaces;

        return $this;
    }

    /**
     * @return Collection|UserRegisterForSchoolExperienceRequest[]
     */
    public function getUserRegisterForSchoolExperienceRequests(): Collection
    {
        return $this->userRegisterForSchoolExperienceRequests;
    }

    public function addUserRegisterForSchoolExperienceRequest(UserRegisterForSchoolExperienceRequest $userRegisterForSchoolExperienceRequest): self
    {
        if (!$this->userRegisterForSchoolExperienceRequests->contains($userRegisterForSchoolExperienceRequest)) {
            $this->userRegisterForSchoolExperienceRequests[] = $userRegisterForSchoolExperienceRequest;
            $userRegisterForSchoolExperienceRequest->setSchoolExperience($this);
        }

        return $this;
    }

    public function removeUserRegisterForSchoolExperienceRequest(UserRegisterForSchoolExperienceRequest $userRegisterForSchoolExperienceRequest): self
    {
        if ($this->userRegisterForSchoolExperienceRequests->contains($userRegisterForSchoolExperienceRequest)) {
            $this->userRegisterForSchoolExperienceRequests->removeElement($userRegisterForSchoolExperienceRequest);
            // set the owning side to null (unless already changed)
            if ($userRegisterForSchoolExperienceRequest->getSchoolExperience() === $this) {
                $userRegisterForSchoolExperienceRequest->setSchoolExperience(null);
            }
        }

        return $this;
    }

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA"})
     * @return string
     */
    public function getFriendlyEventName() {
        return 'School Event';
    }
}
