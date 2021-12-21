<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LessonTeachableRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class LessonTeachable
{
    use Timestampable;

    /**
     * @Groups({"ALL_USER_DATA"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="lessonTeachables")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @Groups({"ALL_USER_DATA"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Lesson", inversedBy="lessonTeachables")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $lesson;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reportLessonName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): self
    {
        $this->lesson = $lesson;

        return $this;
    }

    public function getReportLessonName(): ?string
    {
        return $this->reportLessonName;
    }

    public function setReportLessonName(?string $reportLessonName): self
    {
        $this->reportLessonName = $reportLessonName;

        return $this;
    }
}
