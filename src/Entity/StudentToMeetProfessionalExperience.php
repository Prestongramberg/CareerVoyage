<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StudentToMeetProfessionalExperienceRepository")
 */
class StudentToMeetProfessionalExperience extends Experience
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StudentToMeetProfessionalRequest", inversedBy="studentToMeetProfessionalExperiences")
     */
    private $originalRequest;

    public function getOriginalRequest(): ?StudentToMeetProfessionalRequest
    {
        return $this->originalRequest;
    }

    public function setOriginalRequest(?StudentToMeetProfessionalRequest $originalRequest): self
    {
        $this->originalRequest = $originalRequest;

        return $this;
    }
}
