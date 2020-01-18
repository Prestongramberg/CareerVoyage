<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolVideoRepository")
 */
class SchoolVideo extends Video
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\School", inversedBy="schoolVideos")
     * @ORM\JoinColumn(name="school_id", referencedColumnName="id", onDelete="CASCADE")
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
