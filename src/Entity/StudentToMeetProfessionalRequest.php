<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StudentToMeetProfessionalRequestRepository")
 */
class StudentToMeetProfessionalRequest extends Request
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\RolesWillingToFulfill")
     */
    private $reasonToMeet;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateOptionOne;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateOptionTwo;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateOptionThree;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $confirmedDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProfessionalUser", inversedBy="studentToMeetProfessionalRequests")
     */
    private $professional;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StudentUser", inversedBy="studentToMeetProfessionalRequests")
     */
    private $student;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentToMeetProfessionalExperience", mappedBy="originalRequest")
     */
    private $studentToMeetProfessionalExperiences;

    public function __construct()
    {
        $this->studentToMeetProfessionalExperiences = new ArrayCollection();
    }

    public function getReasonToMeet(): ?RolesWillingToFulfill
    {
        return $this->reasonToMeet;
    }

    public function setReasonToMeet(?RolesWillingToFulfill $reasonToMeet): self
    {
        $this->reasonToMeet = $reasonToMeet;

        return $this;
    }

    public function getDateOptionOne(): ?\DateTimeInterface
    {
        return $this->dateOptionOne;
    }

    public function setDateOptionOne(?\DateTimeInterface $dateOptionOne): self
    {
        $this->dateOptionOne = $dateOptionOne;

        return $this;
    }

    public function getDateOptionTwo(): ?\DateTimeInterface
    {
        return $this->dateOptionTwo;
    }

    public function setDateOptionTwo(?\DateTimeInterface $dateOptionTwo): self
    {
        $this->dateOptionTwo = $dateOptionTwo;

        return $this;
    }

    public function getDateOptionThree(): ?\DateTimeInterface
    {
        return $this->dateOptionThree;
    }

    public function setDateOptionThree(?\DateTimeInterface $dateOptionThree): self
    {
        $this->dateOptionThree = $dateOptionThree;

        return $this;
    }

    public function getConfirmedDate(): ?\DateTimeInterface
    {
        return $this->confirmedDate;
    }

    public function setConfirmedDate(?\DateTimeInterface $confirmedDate): self
    {
        $this->confirmedDate = $confirmedDate;

        return $this;
    }

    public function getProfessional(): ?ProfessionalUser
    {
        return $this->professional;
    }

    public function setProfessional(?ProfessionalUser $professional): self
    {
        $this->professional = $professional;

        return $this;
    }

    public function getStudent(): ?StudentUser
    {
        return $this->student;
    }

    public function setStudent(?StudentUser $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function initializeForEducator(StudentUser $student, ProfessionalUser $professional, EducatorUser $teacher, RolesWillingToFulfill $reasonToMeet)
    {
        $this->setCreatedBy($student);
        $this->setProfessional($professional);
        $this->setStudent($student);
        $this->setReasonToMeet($reasonToMeet);
        $this->setNeedsApprovalBy($teacher);
    }

    public function initializeForProfessional(StudentUser $student, ProfessionalUser $professional, RolesWillingToFulfill $reasonToMeet)
    {
        $this->setCreatedBy($student);
        $this->setProfessional($professional);
        $this->setStudent($student);
        $this->setReasonToMeet($reasonToMeet);
        $this->setNeedsApprovalBy($professional);
    }

    public function initializeForStudent(StudentUser $student, ProfessionalUser $professional, RolesWillingToFulfill $reasonToMeet)
    {
        $this->setCreatedBy($professional);
        $this->setProfessional($professional);
        $this->setStudent($student);
        $this->setReasonToMeet($reasonToMeet);
        $this->setNeedsApprovalBy($student);
    }

    /**
     * @return Collection|StudentToMeetProfessionalExperience[]
     */
    public function getStudentToMeetProfessionalExperiences(): Collection
    {
        return $this->studentToMeetProfessionalExperiences;
    }

    public function addStudentToMeetProfessionalExperience(StudentToMeetProfessionalExperience $studentToMeetProfessionalExperience): self
    {
        if (!$this->studentToMeetProfessionalExperiences->contains($studentToMeetProfessionalExperience)) {
            $this->studentToMeetProfessionalExperiences[] = $studentToMeetProfessionalExperience;
            $studentToMeetProfessionalExperience->setOriginalRequest($this);
        }

        return $this;
    }

    public function removeStudentToMeetProfessionalExperience(StudentToMeetProfessionalExperience $studentToMeetProfessionalExperience): self
    {
        if ($this->studentToMeetProfessionalExperiences->contains($studentToMeetProfessionalExperience)) {
            $this->studentToMeetProfessionalExperiences->removeElement($studentToMeetProfessionalExperience);
            // set the owning side to null (unless already changed)
            if ($studentToMeetProfessionalExperience->getOriginalRequest() === $this) {
                $studentToMeetProfessionalExperience->setOriginalRequest(null);
            }
        }

        return $this;
    }

}
