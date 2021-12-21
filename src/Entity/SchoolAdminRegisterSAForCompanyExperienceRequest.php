<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolAdminRegisterSAForCompanyExperienceRequestRepository")
 */
class SchoolAdminRegisterSAForCompanyExperienceRequest extends Request
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompanyExperience", inversedBy="schoolAdminRegisterSAForCompanyExperienceRequests")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $companyExperience;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolAdministrator", inversedBy="schoolAdminRegisterSAForCompanyExperienceRequests")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $schoolAdminUser;

    public function getCompanyExperience(): ?CompanyExperience
    {
        return $this->companyExperience;
    }

    public function setCompanyExperience(?CompanyExperience $companyExperience): self
    {
        $this->companyExperience = $companyExperience;

        return $this;
    }

    public function getSchoolAdminUser(): ?SchoolAdministrator
    {
        return $this->schoolAdminUser;
    }

    public function setSchoolAdminUser(?SchoolAdministrator $schoolAdminUser): self
    {
        $this->schoolAdminUser = $schoolAdminUser;

        return $this;
    }
}
