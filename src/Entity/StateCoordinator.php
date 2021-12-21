<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StateCoordinatorRepository")
 *
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email", groups={"STATE_COORDINATOR_EDIT"}, repositoryMethod="findByUniqueCriteria")
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username", groups={"STATE_COORDINATOR_EDIT"}, repositoryMethod="findByUniqueCriteria")
 */
class StateCoordinator extends User
{
    /**
     * @Assert\NotBlank(message="Don't forget to select a state!", groups={"STATE_COORDINATOR"})
     * @ORM\ManyToOne(targetEntity="App\Entity\State")
     * @ORM\JoinColumn(nullable=true)
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="stateCoordinators")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $site;

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): self
    {
        $this->state = $state;

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
