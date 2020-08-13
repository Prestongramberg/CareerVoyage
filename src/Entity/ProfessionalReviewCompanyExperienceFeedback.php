<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfessionalReviewCompanyExperienceFeedbackRepository")
 */
class ProfessionalReviewCompanyExperienceFeedback extends Feedback
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompanyExperience", inversedBy="professionalReviewCompanyExperienceFeedback")
     * @ORM\JoinColumn(nullable=false)
     */
    private $companyExperience;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProfessionalUser", inversedBy="professionalReviewCompanyExperienceFeedback")
     * @ORM\JoinColumn(nullable=false)
     */
    private $professional;

    /**
     * @Assert\NotBlank(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="integer")
     */
    private $awarenessOfCareerOpportunities = 0;

    public function getCompanyExperience(): ?CompanyExperience
    {
        return $this->companyExperience;
    }

    public function setCompanyExperience(?CompanyExperience $companyExperience): self
    {
        $this->companyExperience = $companyExperience;

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

    public function getAwarenessOfCareerOpportunities(): ?int
    {
        return $this->awarenessOfCareerOpportunities;
    }

    public function setAwarenessOfCareerOpportunities(int $awarenessOfCareerOpportunities): self
    {
        $this->awarenessOfCareerOpportunities = $awarenessOfCareerOpportunities;

        return $this;
    }
}
