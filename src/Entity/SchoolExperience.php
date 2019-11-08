<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolExperienceRepository")
 */
class SchoolExperience extends Experience
{

	public static $types = [
		'School Event' => 'IN_SCHOOL',
	];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\School", inversedBy="schoolExperiences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $school;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolAdministrator", inversedBy="schoolExperiences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schoolContact;

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getSchoolContact(): ?SchoolAdministrator
    {
        return $this->schoolContact;
    }

    public function setSchoolContact(?SchoolAdministrator $schoolContact): self
    {
        $this->schoolContact = $schoolContact;

        return $this;
    }
}
