<?php

namespace App\Entity;

use App\Repository\ExperienceResourceRepository;
use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExperienceResourceRepository::class)
 */
class ExperienceResource extends Resource
{

    /**
     * @ORM\ManyToOne(targetEntity=Experience::class, inversedBy="experienceResources")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
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

    public function getPath()
    {
        return UploaderHelper::EXPERIENCE_FILE.'/'.$this->getFileName();
    }
}
