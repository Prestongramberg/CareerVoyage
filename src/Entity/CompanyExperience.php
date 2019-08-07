<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyExperienceRepository")
 */
class CompanyExperience extends Experience
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="companyExperiences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProfessionalUser", inversedBy="companyExperiences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $employeeContact;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getEmployeeContact(): ?ProfessionalUser
    {
        return $this->employeeContact;
    }

    public function setEmployeeContact(?ProfessionalUser $employeeContact): self
    {
        $this->employeeContact = $employeeContact;

        return $this;
    }
}
