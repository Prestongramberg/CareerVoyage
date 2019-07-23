<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExperienceWaverRepository")
 */
class ExperienceWaver extends Image
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Experience", inversedBy="experienceWavers")
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
