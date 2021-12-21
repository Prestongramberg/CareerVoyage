<?php

namespace App\Entity;

use App\Repository\ReportVolunteerRoleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReportVolunteerRoleRepository::class)
 */
class ReportVolunteerRole
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ProfessionalUser::class, inversedBy="reportVolunteerRoles")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $professionalUser;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $roleName;

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

    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    public function setRoleName(?string $roleName): self
    {
        $this->roleName = $roleName;

        return $this;
    }
}
