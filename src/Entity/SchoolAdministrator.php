<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolAdministratorRepository")
 */
class SchoolAdministrator extends User
{
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\School", mappedBy="schoolAdministrators")
     */
    private $schools;

    public function __construct()
    {
        parent::__construct();
        $this->schools = new ArrayCollection();
    }

    /**
     * @return Collection|School[]
     */
    public function getSchools(): Collection
    {
        return $this->schools;
    }

    public function addSchool(School $school): self
    {
        if (!$this->schools->contains($school)) {
            $this->schools[] = $school;
            $school->addSchoolAdministrator($this);
        }

        return $this;
    }

    public function removeSchool(School $school): self
    {
        if ($this->schools->contains($school)) {
            $this->schools->removeElement($school);
            $school->removeSchoolAdministrator($this);
        }

        return $this;
    }
}
