<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolAdministratorRequestRepository")
 */
class SchoolAdministratorRequest extends Request
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\School", inversedBy="schoolAdministratorRequests")
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
