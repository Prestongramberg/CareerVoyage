<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyFavoriteRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class CompanyFavorite
{
    use Timestampable;

    /**
     * @Groups({"RESULTS_PAGE", "ALL_USER_DATA"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"RESULTS_PAGE"})
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="companyFavorites")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @Groups({"RESULTS_PAGE", "ALL_USER_DATA"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="companyFavorites")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }
}
