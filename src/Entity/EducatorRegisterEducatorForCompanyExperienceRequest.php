<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EducatorRegisterEducatorForCompanyExperienceRequestRepository")
 */
class EducatorRegisterEducatorForCompanyExperienceRequest extends Request
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompanyExperience", inversedBy="educatorRegisterEducatorForCompanyExperienceRequests")
     * @ORM\JoinColumn(nullable=true)
     */
    private $companyExperience;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EducatorUser", inversedBy="educatorRegisterEducatorForCompanyExperienceRequests")
     */
    private $educatorUser;

    public function getCompanyExperience(): ?CompanyExperience
    {
        return $this->companyExperience;
    }

    public function setCompanyExperience(?CompanyExperience $companyExperience): self
    {
        $this->companyExperience = $companyExperience;

        return $this;
    }

    public function getEducatorUser(): ?EducatorUser
    {
        return $this->educatorUser;
    }

    public function setEducatorUser(?EducatorUser $educatorUser): self
    {
        $this->educatorUser = $educatorUser;

        return $this;
    }
}
