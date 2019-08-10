<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EducatorUserRepository")
 *
 * @UniqueEntity(
 *     fields={"school", "educatorId"},
 *     errorPath="educatorId",
 *     message="This educator Id already belongs to another user at this school",
 *     groups={"EDUCATOR_USER"}
 * )
 *
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email", groups={"EDUCATOR_USER"}, repositoryMethod="findByUniqueCriteria")
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username", groups={"EDUCATOR_USER"}, repositoryMethod="findByUniqueCriteria")
 */
class EducatorUser extends User
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\School", inversedBy="educatorUsers")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $school;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $briefBio;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $linkedinProfile;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\SecondaryIndustry", inversedBy="educatorUsers")
     */
    private $secondaryIndustries;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $interests;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $displayName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SchoolExperience", mappedBy="schoolContact")
     */
    private $schoolExperiences;

    /**
     * @Groups({"EDUCATOR_USER"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $educatorId;

    public function __construct()
    {
        parent::__construct();
        $this->secondaryIndustries = new ArrayCollection();
        $this->schoolExperiences = new ArrayCollection();
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

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

    public function getLinkedinProfile(): ?string
    {
        return $this->linkedinProfile;
    }

    public function setLinkedinProfile(?string $linkedinProfile): self
    {
        $this->linkedinProfile = $linkedinProfile;

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

    public function getInterests(): ?string
    {
        return $this->interests;
    }

    public function setInterests(?string $interests): self
    {
        $this->interests = $interests;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * @return Collection|SchoolExperience[]
     */
    public function getSchoolExperiences(): Collection
    {
        return $this->schoolExperiences;
    }

    public function addSchoolExperience(SchoolExperience $schoolExperience): self
    {
        if (!$this->schoolExperiences->contains($schoolExperience)) {
            $this->schoolExperiences[] = $schoolExperience;
            $schoolExperience->setSchoolContact($this);
        }

        return $this;
    }

    public function removeSchoolExperience(SchoolExperience $schoolExperience): self
    {
        if ($this->schoolExperiences->contains($schoolExperience)) {
            $this->schoolExperiences->removeElement($schoolExperience);
            // set the owning side to null (unless already changed)
            if ($schoolExperience->getSchoolContact() === $this) {
                $schoolExperience->setSchoolContact(null);
            }
        }

        return $this;
    }

    public function getEducatorId()
    {
        return $this->educatorId;
    }

    public function setEducatorId($educatorId): self
    {
        $this->educatorId = $educatorId;

        return $this;
    }

    /**
     * first name - period - last name
     * @return string
     */
    public function getTempUsername() {
        return strtolower(sprintf("%s.%s",
            $this->firstName,
            $this->lastName
        ));
    }

    /**
     * first 3 letters of last name followed by their unique educator ID
     * followed by an explanation point
     *
     * @return string
     */
    public function getTempPassword() {
        return strtolower(sprintf("%s%s!",
            substr($this->lastName, 0, 3),
            $this->getEducatorId()
        ));
    }
}
