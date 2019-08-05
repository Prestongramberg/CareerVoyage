<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StateCoordinatorRepository")
 */
class StateCoordinator extends User
{
    /**
     * @Assert\NotBlank(message="Don't forget to select a state!", groups={"STATE_COORDINATOR"})
     * @ORM\ManyToOne(targetEntity="App\Entity\State", inversedBy="stateCoordinators")
     * @ORM\JoinColumn(nullable=true)
     */
    private $state;

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): self
    {
        $this->state = $state;

        return $this;
    }
}
