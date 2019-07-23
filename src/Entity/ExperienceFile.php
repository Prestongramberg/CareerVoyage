<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExperienceFileRepository")
 */
class ExperienceFile extends Image
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Experience", inversedBy="experienceFiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $experience;

    public function getExperience(): ?Experience
    {
        return $this->experience;
    }

    public function setExperience(?Experience $experience): self
    {
        $this->experience = $experience;

        return $this;
    }
}
