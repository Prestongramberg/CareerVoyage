<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SystemUserRepository")
 */
class SystemUser extends User
{
    const EXPERIENCE_NOTIFY = 'EXPERIENCE_NOTIFY';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
