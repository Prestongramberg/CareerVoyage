<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfessionalReviewMeetStudentExperienceFeedbackRepository")
 */
class ProfessionalReviewMeetStudentExperienceFeedback extends Feedback
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProfessionalUser", inversedBy="professionalReviewMeetStudentExperienceFeedback")
     */
    private $professional;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StudentToMeetProfessionalExperience", inversedBy="professionalReviewMeetStudentExperienceFeedback")
     */
    private $studentToMeetProfessionalExperience;

    public function getProfessional(): ?ProfessionalUser
    {
        return $this->professional;
    }

    public function setProfessional(?ProfessionalUser $professional): self
    {
        $this->professional = $professional;

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
}
