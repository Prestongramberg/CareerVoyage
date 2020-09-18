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

    // /**
    //  * @ORM\OneToMany(targetEntity="App\Entity\SchoolExperience", mappedBy="schoolContact", orphanRemoval=true)
    //  */
    // private $schoolExperiences;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="schoolAdministrators")
     */
    private $site;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    public function __construct()
    {
        parent::__construct();
        $this->schools = new ArrayCollection();
        $this->schoolExperiences = new ArrayCollection();
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

    public function removeAllSchools(): self
    {
        $this->schools->removeAll();
        return $this;
    }

    /**
     * @return Collection|SchoolExperience[]
     */
    public function getSchoolExperiences(): Collection
    {
        return $this->schoolExperiences;
    }

    public function addSchoolExperience(SchoolExperience $schoolExperience): self
    {
        if (!$this->schoolExperiences->contains($schoolExperience)) {
            $this->schoolExperiences[] = $schoolExperience;
            $schoolExperience->setSchoolContact($this);
        }

        return $this;
    }

    public function removeSchoolExperience(SchoolExperience $schoolExperience): self
    {
        if ($this->schoolExperiences->contains($schoolExperience)) {
            $this->schoolExperiences->removeElement($schoolExperience);
            // set the owning side to null (unless already changed)
            if ($schoolExperience->getSchoolContact() === $this) {
                $schoolExperience->setSchoolContact(null);
            }
        }

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function getSchoolsAsString($delimiter = ',') {
        $schools = [];
        foreach($this->getSchools() as $school) {
            $schools[] = $school->getName();
        }
        return implode($delimiter, $schools);
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }
}
