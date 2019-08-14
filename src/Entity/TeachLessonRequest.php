<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeachLessonRequestRepository")
 */
class TeachLessonRequest extends Request
{
    /**
     * @ORM\Column(type="boolean")
     */
    private $isFromProfessional = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lesson", inversedBy="teachLessonRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lesson;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateOptionOne;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateOptionTwo;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateOptionThree;

    public function getIsFromProfessional(): ?bool
    {
        return $this->isFromProfessional;
    }

    public function setIsFromProfessional(bool $isFromProfessional): self
    {
        $this->isFromProfessional = $isFromProfessional;

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

    public function getDateOptionOne(): ?\DateTimeInterface
    {
        return $this->dateOptionOne;
    }

    public function setDateOptionOne(?\DateTimeInterface $dateOptionOne): self
    {
        $this->dateOptionOne = $dateOptionOne;

        return $this;
    }

    public function getDateOptionTwo(): ?\DateTimeInterface
    {
        return $this->dateOptionTwo;
    }

    public function setDateOptionTwo(?\DateTimeInterface $dateOptionTwo): self
    {
        $this->dateOptionTwo = $dateOptionTwo;

        return $this;
    }

    public function getDateOptionThree(): ?\DateTimeInterface
    {
        return $this->dateOptionThree;
    }

    public function setDateOptionThree(?\DateTimeInterface $dateOptionThree): self
    {
        $this->dateOptionThree = $dateOptionThree;

        return $this;
    }
}
