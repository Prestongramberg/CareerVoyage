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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="schoolExperiences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schoolContact;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserRegisterForSchoolExperienceRequest", mappedBy="schoolExperience", orphanRemoval=true)
     */
    private $userRegisterForSchoolExperienceRequests;

    /**
     * @ORM\Column(type="boolean")
     */
    private $canViewFeedback;

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

    public function getSchoolContact(): ?User
    {
        return $this->schoolContact;
    }

    public function setSchoolContact(?User $schoolContact): self
    {
        $this->schoolContact = $schoolContact;

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
     * @Groups({"EXPERIENCE_DATA"})
     */
    public function getExperienceListTitle() {

        if($this->getSchool()) {
            return $this->getSchool()->getName();
        }

        return '';
    }

    public function getCanViewFeedback()
    {
        return $this->canViewFeedback;
    }

    public function setCanViewFeedback($canViewFeedback): self
    {
        $this->canViewFeedback = $canViewFeedback;

        return $this;
    }
}
