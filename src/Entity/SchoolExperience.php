<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolExperienceRepository")
 */
class SchoolExperience extends Experience
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\School", inversedBy="schoolExperiences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $school;

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

        return $this;
    }
}
