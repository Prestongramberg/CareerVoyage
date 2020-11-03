<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExperienceFileRepository")
 */
class ExperienceFile extends Image
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Experience", inversedBy="experienceFiles")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $experience;

    /**
     * @var File
     */
    private $file;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;


    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\Column(type="string", nullable=true)
     */
    private $linkToWebsite;
    

    public function getExperience(): ?Experience
    {
        return $this->experience;
    }

    public function setExperience(?Experience $experience): self
    {
        $this->experience = $experience;

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

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getLinkToWebsite()
    {
        return $this->linkToWebsite;
    }

    public function setLinkToWebsite($linkToWebsite)
    {
        $this->linkToWebsite = $linkToWebsite;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getPath()
    {
        return UploaderHelper::EXPERIENCE_FILE.'/'.$this->getFileName();
    }

    /**
     * @Groups({"EXPERIENCE_DATA"})
     */
    public function getResource() {
        if($this->getFileName()) {
            return '/uploads/experience_file/' . $this->getFileName();
        }
        return '';
    }
}
