<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HelpVideoRepository")
 */
class HelpVideo extends Video
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $userRole;

    /**
     * @ORM\Column(type="integer")
     */
    protected $position = 0;
    

    public function getUserRole(): ?string
    {
        return $this->$userRole;
    }

    public function setUserRole(?string $userRole): self
    {
        $this->userRole = $userRole;
        
        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
