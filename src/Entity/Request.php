<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RequestRepository")
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"newCompanyRequest" = "NewCompanyRequest", "joinCompanyRequest" = "JoinCompanyRequest"})
 */
abstract class Request
{
    use TimestampableEntity;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @Groups({"RESULTS_PAGE", "PROFESSIONAL_USER_DATA"})
     * @ORM\Column(type="boolean")
     */
    protected $approved = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="requests")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $created_by;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="requestsThatNeedMyApproval")
     */
    private $needsApprovalBy;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApproved(): ?bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->created_by;
    }

    public function setCreatedBy(?User $created_by): self
    {
        $this->created_by = $created_by;

        return $this;
    }

    public function getNeedsApprovalBy(): ?User
    {
        return $this->needsApprovalBy;
    }

    public function setNeedsApprovalBy(?User $needsApprovalBy): self
    {
        $this->needsApprovalBy = $needsApprovalBy;

        return $this;
    }

    public function getClassName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
