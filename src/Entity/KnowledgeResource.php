<?php

namespace App\Entity;

use App\Repository\KnowledgeResourceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=KnowledgeResourceRepository::class)
 */
class KnowledgeResource extends Resource
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $tab;

    public function getTab(): ?string
    {
        return $this->tab;
    }

    public function setTab(?string $tab): self
    {
        $this->tab = $tab;

        return $this;
    }
}
