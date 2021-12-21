<?php

namespace App\Entity;

use App\Repository\ReportVolunteerSchoolRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReportVolunteerSchoolRepository::class)
 */
class ReportVolunteerSchool
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ProfessionalUser::class, inversedBy="reportVolunteerSchools")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $professionalUser;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $schoolName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $schoolPhone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $schoolEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $schoolAddress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $schoolWebsite;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSchoolName(): ?string
    {
        return $this->schoolName;
    }

    public function setSchoolName(?string $schoolName): self
    {
        $this->schoolName = $schoolName;

        return $this;
    }

    public function getSchoolPhone(): ?string
    {
        return $this->schoolPhone;
    }

    public function setSchoolPhone(?string $schoolPhone): self
    {
        $this->schoolPhone = $schoolPhone;

        return $this;
    }

    public function getSchoolEmail(): ?string
    {
        return $this->schoolEmail;
    }

    public function setSchoolEmail(?string $schoolEmail): self
    {
        $this->schoolEmail = $schoolEmail;

        return $this;
    }

    public function getSchoolAddress(): ?string
    {
        return $this->schoolAddress;
    }

    public function setSchoolAddress(?string $schoolAddress): self
    {
        $this->schoolAddress = $schoolAddress;

        return $this;
    }

    public function getSchoolWebsite(): ?string
    {
        return $this->schoolWebsite;
    }

    public function setSchoolWebsite(?string $schoolWebsite): self
    {
        $this->schoolWebsite = $schoolWebsite;

        return $this;
    }
}
