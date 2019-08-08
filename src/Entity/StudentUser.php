<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StudentUserRepository")
 */
class StudentUser extends User
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\School", inversedBy="studentUsers")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $school;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Grade", inversedBy="studentUsers")
     */
    private $grade;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $schoolEmail;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $briefBio;


    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\SecondaryIndustry", inversedBy="studentUsers")
     */
    private $secondaryIndustries;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Company", inversedBy="studentUsers")
     */
    private $companiesInterestedIn;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $studentId;

    public function __construct()
    {
        parent::__construct();
        $this->secondaryIndustries = new ArrayCollection();
        $this->companiesInterestedIn = new ArrayCollection();
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

    public function getGrade(): ?Grade
    {
        return $this->grade;
    }

    public function setGrade(?Grade $grade): self
    {
        $this->grade = $grade;

        return $this;
    }

    public function getSchoolEmail(): ?string
    {
        return $this->schoolEmail;
    }

    public function setSchoolEmail(?string $schoolEmail): self
    {
        $this->schoolEmail = $schoolEmail;

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
     * @return Collection|Company[]
     */
    public function getCompaniesInterestedIn(): Collection
    {
        return $this->companiesInterestedIn;
    }

    public function addCompaniesInterestedIn(Company $companiesInterestedIn): self
    {
        if (!$this->companiesInterestedIn->contains($companiesInterestedIn)) {
            $this->companiesInterestedIn[] = $companiesInterestedIn;
        }

        return $this;
    }

    public function removeCompaniesInterestedIn(Company $companiesInterestedIn): self
    {
        if ($this->companiesInterestedIn->contains($companiesInterestedIn)) {
            $this->companiesInterestedIn->removeElement($companiesInterestedIn);
        }

        return $this;
    }

    public function getStudentId(): ?string
    {
        return $this->studentId;
    }

    public function setStudentId(string $studentId): self
    {
        $this->studentId = $studentId;

        return $this;
    }

    public function getTempUsername() {
        return strtoupper(
            sprintf("%s_%s_%s", $this->firstName, $this->lastName, $this->getStudentId())
        );
    }
}
