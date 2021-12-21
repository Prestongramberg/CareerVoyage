<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EducatorVideoRepository")
 */
class EducatorVideo extends Video
{
    /**
     * @Groups({"EDUCATOR_USER_DATA"})
     * @ORM\ManyToOne(targetEntity="App\Entity\EducatorUser", inversedBy="educatorVideos")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $educator;

    public function getEducator(): ?EducatorUser
    {
        return $this->educator;
    }

    public function setEducator(?EducatorUser $educator): self
    {
        $this->educator = $educator;

        return $this;
    }
}
