<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

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

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA"})
     * @return string
     */
    public function getFriendlyEventName() {
        return 'Student/Professional Meeting';
    }
}
