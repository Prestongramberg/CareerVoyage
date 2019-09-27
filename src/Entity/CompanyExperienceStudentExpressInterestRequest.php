<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyExperienceStudentExpressInterestRequestRepository")
 */
class CompanyExperienceStudentExpressInterestRequest extends Request
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StudentUser", inversedBy="companyExperienceStudentExpressInterestRequests")
     */
    private $studentUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompanyExperience", inversedBy="companyExperienceStudentExpressInterestRequests")
     */
    private $companyExperience;

    public function getStudentUser(): ?StudentUser
    {
        return $this->studentUser;
    }

    public function setStudentUser(?StudentUser $studentUser): self
    {
        $this->studentUser = $studentUser;

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
}
