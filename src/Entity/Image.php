<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\EntityListeners({"App\EntityListener\ImageListener"})
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
     * @var UploadedFile
     */
    protected $file;

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
     * @return UploadedFile
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file): void
    {
        $this->file = $file;
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

    public function getOriginalFileName() {
        return pathinfo($this->file->getClientOriginalName(), PATHINFO_FILENAME);
    }

    public function getSafeFileName() {
        return $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $this->getOriginalFileName());
    }

    public function createNewFileName() {
        return $this->getSafeFileName().'-'.uniqid().'.'.$this->file->guessExtension();
    }

    public function preUpload() {
        $this->originalName = $this->getOriginalFileName();
        $this->newName = $this->createNewFileName();
        $this->mimeType = $this->getFile()->getMimeType();
    }
}
