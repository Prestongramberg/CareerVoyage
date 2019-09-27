<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeachLessonExperienceRepository")
 */
class TeachLessonExperience extends Experience
{
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\TeachLessonRequest", inversedBy="teachLessonExperience", cascade={"persist", "remove"})
     */
    private $originalRequest;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProfessionalUser", inversedBy="teachLessonExperiences")
     */
    private $teacher;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\ManyToOne(targetEntity="App\Entity\School", inversedBy="teachLessonExperiences")
     */
    private $school;

    public function getOriginalRequest(): ?TeachLessonRequest
    {
        return $this->originalRequest;
    }

    public function setOriginalRequest(?TeachLessonRequest $originalRequest): self
    {
        $this->originalRequest = $originalRequest;

        return $this;
    }

    public function getTeacher(): ?ProfessionalUser
    {
        return $this->teacher;
    }

    public function setTeacher(?ProfessionalUser $teacher): self
    {
        $this->teacher = $teacher;

        return $this;
    }

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
