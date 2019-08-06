<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RegionalCoordinatorRepository")
 */
class RegionalCoordinator extends User
{
    /**
     * @Assert\NotBlank(message="Don't forget to select a region!", groups={"REGIONAL_COORDINATOR"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Region", inversedBy="regionalCoordinators")
     * @ORM\JoinColumn(nullable=false)
     */
    private $region;

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }
}
