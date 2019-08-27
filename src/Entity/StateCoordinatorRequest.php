<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StateCoordinatorRequestRepository")
 */
class StateCoordinatorRequest extends Request
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\State", inversedBy="stateCoordinatorRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $state;

    protected $allowApprovalByActivationCode = true;

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
