<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RegionalCoordinatorRepository")
 *
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email", groups={"REGIONAL_COORDINATOR_EDIT"}, repositoryMethod="findByUniqueCriteria")
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username", groups={"REGIONAL_COORDINATOR_EDIT"}, repositoryMethod="findByUniqueCriteria")
 */
class RegionalCoordinator extends User
{
    /**
     * @Assert\NotBlank(message="Don't forget to select a region!", groups={"REGIONAL_COORDINATOR"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Region", inversedBy="regionalCoordinators")
     * @ORM\JoinColumn(nullable=false)
     */
    private $region;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="regionalCoordinators")
     */
    private $site;

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }
}
