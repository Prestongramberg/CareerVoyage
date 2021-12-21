<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EducatorReviewCompanyExperienceFeedbackRepository")
 */
class EducatorReviewCompanyExperienceFeedback extends Feedback
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompanyExperience", inversedBy="educatorReviewCompanyExperienceFeedback")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $companyExperience;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EducatorUser", inversedBy="educatorReviewCompanyExperienceFeedback")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $educator;

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

    public function getEducator(): ?EducatorUser
    {
        return $this->educator;
    }

    public function setEducator(?EducatorUser $educator): self
    {
        $this->educator = $educator;

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
