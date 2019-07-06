<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 */
class Image
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
    private $path;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $originalName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $newName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mimeType;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ProfessionalUser", mappedBy="photo", cascade={"persist", "remove"})
     */
    private $professionalUser;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getProfessionalUser(): ?ProfessionalUser
    {
        return $this->professionalUser;
    }

    public function setProfessionalUser(?ProfessionalUser $professionalUser): self
    {
        $this->professionalUser = $professionalUser;

        // set (or unset) the owning side of the relation if necessary
        $newPhoto = $professionalUser === null ? null : $this;
        if ($newPhoto !== $professionalUser->getPhoto()) {
            $professionalUser->setPhoto($newPhoto);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNewName()
    {
        return $this->newName;
    }

    /**
     * @param mixed $newName
     */
    public function setNewName($newName): void
    {
        $this->newName = $newName;
    }
}
