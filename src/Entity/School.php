<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolRepository")
 */
class School
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Company", mappedBy="schools")
     */
    private $companies;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\ProfessionalUser", mappedBy="schools")
     */
    private $professionalUsers;

    public function __construct()
    {
        $this->companies = new ArrayCollection();
        $this->professionalUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Company[]
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies[] = $company;
            $company->addSchool($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): self
    {
        if ($this->companies->contains($company)) {
            $this->companies->removeElement($company);
            $company->removeSchool($this);
        }

        return $this;
    }

    /**
     * @return Collection|ProfessionalUser[]
     */
    public function getProfessionalUsers(): Collection
    {
        return $this->professionalUsers;
    }

    public function addProfessionalUser(ProfessionalUser $professionalUser): self
    {
        if (!$this->professionalUsers->contains($professionalUser)) {
            $this->professionalUsers[] = $professionalUser;
            $professionalUser->addSchool($this);
        }

        return $this;
    }

    public function removeProfessionalUser(ProfessionalUser $professionalUser): self
    {
        if ($this->professionalUsers->contains($professionalUser)) {
            $this->professionalUsers->removeElement($professionalUser);
            $professionalUser->removeSchool($this);
        }

        return $this;
    }
}
