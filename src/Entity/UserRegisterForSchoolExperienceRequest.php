<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRegisterForSchoolExperienceRequestRepository")
 */
class UserRegisterForSchoolExperienceRequest extends Request
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolExperience", inversedBy="userRegisterForSchoolExperienceRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schoolExperience;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userRegisterForSchoolExperienceRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getSchoolExperience(): ?SchoolExperience
    {
        return $this->schoolExperience;
    }

    public function setSchoolExperience(?SchoolExperience $schoolExperience): self
    {
        $this->schoolExperience = $schoolExperience;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
