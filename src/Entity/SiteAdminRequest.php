<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SiteAdminRequestRepository")
 */
class SiteAdminRequest extends Request
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="siteAdminRequests")
     * @ORM\JoinColumn(nullable=false)
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
}
