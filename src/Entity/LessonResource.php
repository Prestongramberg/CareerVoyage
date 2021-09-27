<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LessonResourceRepository")
 */
class LessonResource extends Resource
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lesson", inversedBy="lessonResources")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $lesson;

    public function getLesson()
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson)
    {
        $this->lesson = $lesson;

        return $this;
    }

    public function getPath()
    {
        return UploaderHelper::LESSON_RESOURCE.'/'.$this->getFileName();
    }
}
