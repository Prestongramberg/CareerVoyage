<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SiteAdminUserRepository")
 */
class SiteAdminUser extends User
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="siteAdminUsers")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $site;

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function isAdminOfSite(Site $site) {

        if(!$this->getSite()) {
            return false;
        }

        return $this->getSite()->getId() === $site->getId();
    }
}
