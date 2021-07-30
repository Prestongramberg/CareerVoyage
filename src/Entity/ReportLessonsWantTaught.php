<?php

namespace App\Entity;

use App\Repository\ReportLessonsWantTaughtRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReportLessonsWantTaughtRepository::class)
 */
class ReportLessonsWantTaught
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Lesson::class)
     */
    private $lesson;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lessonName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity=EducatorUser::class, inversedBy="reportLessonsWantTaughts")
     */
    private $educatorUser;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLessonName(): ?string
    {
        return $this->lessonName;
    }

    public function setLessonName(?string $lessonName): self
    {
        $this->lessonName = $lessonName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEducatorUser(): ?EducatorUser
    {
        return $this->educatorUser;
    }

    public function setEducatorUser(?EducatorUser $educatorUser): self
    {
        $this->educatorUser = $educatorUser;

        return $this;
    }
}
