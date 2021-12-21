<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EducatorRegisterStudentForExperienceRequestRepository")
 */
class EducatorRegisterStudentForCompanyExperienceRequest extends Request
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompanyExperience", inversedBy="educatorRegisterStudentForCompanyExperienceRequests")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $companyExperience;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StudentUser", inversedBy="educatorRegisterStudentForCompanyExperienceRequests")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $studentUser;


    public function getCompanyExperience(): ?CompanyExperience
    {
        return $this->companyExperience;
    }

    public function setCompanyExperience(?CompanyExperience $companyExperience): self
    {
        $this->companyExperience = $companyExperience;

        return $this;
    }

    public function getStudentUser(): ?StudentUser
    {
        return $this->studentUser;
    }

    public function setStudentUser(?StudentUser $studentUser): self
    {
        $this->studentUser = $studentUser;

        return $this;
    }
}