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
class
EducatorRegisterStudentForCompanyExperienceRequest extends Request
{
    /**
     * @JoinTable(name="student_company_experience_request_registrations",
     *      joinColumns={@JoinColumn(name="request_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="student_user_id", referencedColumnName="id")}
     * )
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\StudentUser", inversedBy="educatorRegisterStudentForCompanyExperienceRequests")
     */
    private $studentUsers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompanyExperience", inversedBy="educatorRegisterStudentForCompanyExperienceRequests")
     * @ORM\JoinColumn(nullable=true)
     */
    private $companyExperience;

    public function __construct()
    {
        $this->studentUsers = new ArrayCollection();
    }

    /**
     * @return Collection|StudentUser[]
     */
    public function getStudentUsers(): Collection
    {
        return $this->studentUsers;
    }

    public function addStudentUser(StudentUser $studentUser): self
    {
        if (!$this->studentUsers->contains($studentUser)) {
            $this->studentUsers[] = $studentUser;
        }

        return $this;
    }

    public function removeStudentUser(StudentUser $studentUser): self
    {
        if ($this->studentUsers->contains($studentUser)) {
            $this->studentUsers->removeElement($studentUser);
        }

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