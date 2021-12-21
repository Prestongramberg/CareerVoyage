<?php

namespace App\Entity;

use App\Repository\ReportVolunteerRegionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReportVolunteerRegionRepository::class)
 */
class ReportVolunteerRegion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ProfessionalUser::class, inversedBy="reportVolunteerRegions")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $professionalUser;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $regionName;

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

    public function getRegionName(): ?string
    {
        return $this->regionName;
    }

    public function setRegionName(?string $regionName): self
    {
        $this->regionName = $regionName;

        return $this;
    }
}
