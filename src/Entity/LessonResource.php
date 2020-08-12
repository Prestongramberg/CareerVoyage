<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LessonResourceRepository")
 */
class LessonResource extends Image
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lesson", inversedBy="lessonResources")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $lesson;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @var File
     */
    private $file;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $linkToWebsite;

    public function getLesson()
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson)
    {
        $this->lesson = $lesson;

        return $this;
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

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
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
        return UploaderHelper::LESSON_RESOURCE.'/'.$this->getFileName();
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

}
