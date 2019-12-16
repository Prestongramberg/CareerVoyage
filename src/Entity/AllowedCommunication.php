<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AllowedCommunicationRepository")
 */
class AllowedCommunication
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StudentUser", inversedBy="allowedCommunications")
     */
    private $studentUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProfessionalUser", inversedBy="allowedCommunications")
     */
    private $professionalUser;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudentUser(): ?StudentUser
    {
        return $this->studentUser;
    }

    public function setStudentUser(?StudentUser $studentUser): self
    {
        $this->studentUser = $studentUser;

        return $this;
    }

    public function getProfessionalUser(): ?ProfessionalUser
    {
        return $this->professionalUser;
    }

    public function setProfessionalUser(?ProfessionalUser $professionalUser): self
    {
        $this->professionalUser = $professionalUser;

        return $this;
    }
}
