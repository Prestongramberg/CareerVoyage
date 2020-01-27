<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RequestPossibleApproversRepository")
 */
class RequestPossibleApprovers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Request", inversedBy="requestPossibleApprovers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $request;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="requestPossibleApprovers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $possibleApprover;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setRequest(?Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function getPossibleApprover(): ?User
    {
        return $this->possibleApprover;
    }

    public function setPossibleApprover(?User $possibleApprover): self
    {
        $this->possibleApprover = $possibleApprover;

        return $this;
    }
}
