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

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\TeachLessonExperience", mappedBy="originalRequest", cascade={"persist", "remove"})
     */
    private $teachLessonExperience;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\School", inversedBy="teachLessonRequests")
     * @ORM\JoinColumn(name="school_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $school;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $message;

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

    public function getTeachLessonExperience(): ?TeachLessonExperience
    {
        return $this->teachLessonExperience;
    }

    public function setTeachLessonExperience(?TeachLessonExperience $teachLessonExperience): self
    {
        $this->teachLessonExperience = $teachLessonExperience;

        // set (or unset) the owning side of the relation if necessary
        $newOriginalRequest = $teachLessonExperience === null ? null : $this;
        if ($newOriginalRequest !== $teachLessonExperience->getOriginalRequest()) {
            $teachLessonExperience->setOriginalRequest($newOriginalRequest);
        }

        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage(?string $message)
    {
        $this->message = $message;

        return $this;
    }
}
