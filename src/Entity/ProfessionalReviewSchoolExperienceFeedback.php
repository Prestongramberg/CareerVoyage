<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfessionalReviewSchoolExperienceFeedbackRepository")
 */
class ProfessionalReviewSchoolExperienceFeedback extends Feedback
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProfessionalUser")
     * @ORM\JoinColumn(nullable=false)
     */
    private $professional;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolExperience")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schoolExperience;

    /**
     * @Assert\NotBlank(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="integer")
     */
    private $interestInWorkingForCompany = 0;

    public function getProfessional(): ?ProfessionalUser
    {
        return $this->professional;
    }

    public function setProfessional(?ProfessionalUser $professional): self
    {
        $this->professional = $professional;

        return $this;
    }

    public function getSchoolExperience(): ?SchoolExperience
    {
        return $this->schoolExperience;
    }

    public function setSchoolExperience(?SchoolExperience $schoolExperience): self
    {
        $this->schoolExperience = $schoolExperience;

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
