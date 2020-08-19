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

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":true})
     */
    protected $educatorHasSeen = false;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":true})
     */
    protected $professionalHasSeen = false;

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

    public function getEducatorHasSeen(): ?bool
    {
        return $this->educatorHasSeen;
    }

    public function setEducatorHasSeen(?bool $educatorHasSeen): self
    {
        $this->educatorHasSeen = $educatorHasSeen;
        return $this;
    }

    public function getProfessionalHasSeen(): ?bool
    {
        return $this->professionalHasSeen;
    }

    public function setProfessionalHasSeen(?bool $professionalHasSeen): self
    {
        $this->professionalHasSeen = $professionalHasSeen;
        return $this;
    }
}