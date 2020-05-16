<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfessionalReviewStudentToMeetProfessionalFeedbackRepository")
 */
class ProfessionalReviewStudentToMeetProfessionalFeedback extends Feedback
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProfessionalUser", inversedBy="professionalReviewStudentToMeetProfessionalFeedback")
     * @ORM\JoinColumn(nullable=false)
     */
    private $professional;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StudentToMeetProfessionalExperience", inversedBy="professionalReviewStudentToMeetProfessionalFeedback")
     * @ORM\JoinColumn(nullable=false)
     */
    private $studentToMeetProfessionalExperience;

    /**
     * @Assert\NotNull(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="boolean")
     */
    protected $showUp = true;

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

    public function getShowUp(): ?bool
    {
        return $this->showUp;
    }

    public function setShowUp(bool $showUp): self
    {
        $this->showUp = $showUp;

        return $this;
    }
}
