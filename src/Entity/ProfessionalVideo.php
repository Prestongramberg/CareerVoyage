<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfessionalVideoRepository")
 */
class ProfessionalVideo extends Video
{

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @ORM\ManyToOne(targetEntity="App\Entity\ProfessionalUser", inversedBy="professionalVideos")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $professional;

    public function getProfessional(): ?ProfessionalUser
    {
        return $this->professional;
    }

    public function setProfessional(?ProfessionalUser $professional): self
    {
        $this->professional = $professional;

        return $this;
    }
}
