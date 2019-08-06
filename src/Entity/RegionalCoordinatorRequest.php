<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RegionalCoordinatorRequestRepository")
 */
class RegionalCoordinatorRequest extends Request
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Region", inversedBy="regionalCoordinatorRequests")
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
