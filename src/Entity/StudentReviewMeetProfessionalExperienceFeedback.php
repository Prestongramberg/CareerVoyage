<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StudentReviewCompanyExperienceFeedbackRepository")
 */
class StudentReviewMeetProfessionalExperienceFeedback extends Feedback
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StudentUser", inversedBy="studentReviewExperienceFeedback")
     * @ORM\JoinColumn(nullable=false)
     */
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StudentToMeetProfessionalExperience", inversedBy="studentReviewExperienceFeedback")
     * @ORM\JoinColumn(nullable=false)
     */
    private $studentToMeetProfessionalExperience;

    /**
     * @Assert\NotBlank(message="Interest In Working For Company cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="integer")
     */
    public $interestInWorkingForCompany = 0;

    public function getStudent(): ?StudentUser
    {
        return $this->student;
    }

    public function setStudent(?StudentUser $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getStudentToMeetProfessionalExperience(): ?StudentToMeetProfessionalExperience
    {
        return $this->studentToMeetProfessionalExperience;
    }

    public function setStudentToMeetProfessionalExperience(?StudentToMeetProfessionalExperience $studentToMeetProfessionalExperience): self
    {
        $this->studentToMeetProfessionalExperience = $studentToMeetProfessionalExperience;

        return $this;
    }

    public function getInterestInWorkingForCompany(): ?int
    {
        return $this->interestInWorkingForCompany;
    }

    public function setInterestInWorkingForCompany(int $interestInWorkingForCompany): self
    {
        $this->interestInWorkingForCompany = $interestInWorkingForCompany;

        return $this;
    }
}
