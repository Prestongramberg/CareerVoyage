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
class StudentReviewCompanyExperienceFeedback extends Feedback
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StudentUser", inversedBy="studentReviewCompanyExperienceFeedback")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompanyExperience", inversedBy="studentReviewExperienceFeedback")
     * @ORM\JoinColumn(nullable=false)
     */
    private $companyExperience;

    /**
     * @Assert\NotBlank(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="integer")
     */
    private $interestInWorkingForCompany = 0;

    public function getStudent(): ?StudentUser
    {
        return $this->student;
    }

    public function setStudent(?StudentUser $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getCompanyExperience(): ?CompanyExperience
    {
        return $this->companyExperience;
    }

    public function setCompanyExperience(?CompanyExperience $companyExperience): self
    {
        $this->companyExperience = $companyExperience;

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

    public function student() {
        return $this->student;
    }

    public function get_student() {
        return $this->student;
    }
}
