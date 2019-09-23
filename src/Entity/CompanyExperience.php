<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyExperienceRepository")
 */
class CompanyExperience extends Experience
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="companyExperiences")
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $company;

    /**
     * @Assert\NotBlank(message="Don't forget an event coordinator!", groups={"CREATE", "EDIT"})
     * @ORM\ManyToOne(targetEntity="App\Entity\ProfessionalUser", inversedBy="companyExperiences")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $employeeContact;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EducatorRegisterStudentForCompanyExperienceRequest", mappedBy="companyExperience", orphanRemoval=true)
     */
    private $educatorRegisterStudentForCompanyExperienceRequests;

    public function __construct()
    {
        parent::__construct();
        $this->educatorRegisterStudentForCompanyExperienceRequests = new ArrayCollection();
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

    /**
     * @return Collection|EducatorRegisterStudentForCompanyExperienceRequest[]
     */
    public function getEducatorRegisterStudentForCompanyExperienceRequests(): Collection
    {
        return $this->educatorRegisterStudentForCompanyExperienceRequests;
    }

    public function addEducatorRegisterStudentForCompanyExperienceRequest(EducatorRegisterStudentForCompanyExperienceRequest $educatorRegisterStudentForCompanyExperienceRequest): self
    {
        if (!$this->educatorRegisterStudentForCompanyExperienceRequests->contains($educatorRegisterStudentForCompanyExperienceRequest)) {
            $this->educatorRegisterStudentForCompanyExperienceRequests[] = $educatorRegisterStudentForCompanyExperienceRequest;
            $educatorRegisterStudentForCompanyExperienceRequest->setCompanyExperience($this);
        }

        return $this;
    }

    public function removeEducatorRegisterStudentForCompanyExperienceRequest(EducatorRegisterStudentForCompanyExperienceRequest $educatorRegisterStudentForCompanyExperienceRequest): self
    {
        if ($this->educatorRegisterStudentForCompanyExperienceRequests->contains($educatorRegisterStudentForCompanyExperienceRequest)) {
            $this->educatorRegisterStudentForCompanyExperienceRequests->removeElement($educatorRegisterStudentForCompanyExperienceRequest);
            // set the owning side to null (unless already changed)
            if ($educatorRegisterStudentForCompanyExperienceRequest->getCompanyExperience() === $this) {
                $educatorRegisterStudentForCompanyExperienceRequest->setCompanyExperience(null);
            }
        }

        return $this;
    }
}
