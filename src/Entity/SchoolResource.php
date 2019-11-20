<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Service\UploaderHelper;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolResourceRepository")
 */
class SchoolResource extends Image
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\School", inversedBy="schoolResources")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $school;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var File
     */
    private $file;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param File $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getPath()
    {
        return UploaderHelper::SCHOOL_RESOURCE.'/'.$this->getFileName();
    }
}
