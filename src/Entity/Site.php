<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SiteRepository")
 */
class Site
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SiteAdminUser", mappedBy="site")
     */
    private $siteAdminUsers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SiteAdminRequest", mappedBy="site")
     */
    private $siteAdminRequests;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $baseUrl;

    public function __construct()
    {
        $this->siteAdminUsers = new ArrayCollection();
        $this->siteAdminRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|SiteAdminUser[]
     */
    public function getSiteAdminUsers(): Collection
    {
        return $this->siteAdminUsers;
    }

    public function addSiteAdminUser(SiteAdminUser $siteAdminUser): self
    {
        if (!$this->siteAdminUsers->contains($siteAdminUser)) {
            $this->siteAdminUsers[] = $siteAdminUser;
            $siteAdminUser->setSite($this);
        }

        return $this;
    }

    public function removeSiteAdminUser(SiteAdminUser $siteAdminUser): self
    {
        if ($this->siteAdminUsers->contains($siteAdminUser)) {
            $this->siteAdminUsers->removeElement($siteAdminUser);
            // set the owning side to null (unless already changed)
            if ($siteAdminUser->getSite() === $this) {
                $siteAdminUser->setSite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SiteAdminRequest[]
     */
    public function getSiteAdminRequests(): Collection
    {
        return $this->siteAdminRequests;
    }

    public function addSiteAdminRequest(SiteAdminRequest $siteAdminRequest): self
    {
        if (!$this->siteAdminRequests->contains($siteAdminRequest)) {
            $this->siteAdminRequests[] = $siteAdminRequest;
            $siteAdminRequest->setSite($this);
        }

        return $this;
    }

    public function removeSiteAdminRequest(SiteAdminRequest $siteAdminRequest): self
    {
        if ($this->siteAdminRequests->contains($siteAdminRequest)) {
            $this->siteAdminRequests->removeElement($siteAdminRequest);
            // set the owning side to null (unless already changed)
            if ($siteAdminRequest->getSite() === $this) {
                $siteAdminRequest->setSite(null);
            }
        }

        return $this;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }
}
