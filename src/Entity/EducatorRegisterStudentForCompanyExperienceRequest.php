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
     * @ORM\JoinColumn(nullable=true)
     */
    private $companyExperience;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StudentUser", inversedBy="educatorRegisterStudentForCompanyExperienceRequests")
     */
    private $studentUser;


    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":true})
     */
    protected $studentHasSeen = false;

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

    public function getStudentHasSeen(): ?bool
    {
        return $this->studentHasSeen;
    }

    public function setStudentHasSeen(?bool $studentHasSeen): self
    {
        $this->studentHasSeen = $studentHasSeen;
        return $this;
    }
}