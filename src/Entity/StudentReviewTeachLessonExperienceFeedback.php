<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StudentReviewTeachLessonExperienceFeedbackRepository")
 */
class StudentReviewTeachLessonExperienceFeedback extends Feedback
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lesson", inversedBy="studentReviewTeachLessonExperienceFeedback")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lesson;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StudentUser", inversedBy="studentReviewTeachLessonExperienceFeedback")
     * @ORM\JoinColumn(nullable=false)
     */
    private $student;

    /**
     * @Assert\NotBlank(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="integer")
     */
    private $interestInWorkingInThisIndustry = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TeachLessonExperience", inversedBy="studentReviewTeachLessonExperienceFeedback")
     * @ORM\JoinColumn(nullable=false)
     */
    private $teachLessonExperience;

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): self
    {
        $this->lesson = $lesson;

        return $this;
    }

    public function getStudent(): ?StudentUser
    {
        return $this->student;
    }

    public function setStudent(?StudentUser $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getInterestInWorkingInThisIndustry(): ?int
    {
        return $this->interestInWorkingInThisIndustry;
    }

    public function setInterestInWorkingInThisIndustry(int $interestInWorkingInThisIndustry): self
    {
        $this->interestInWorkingInThisIndustry = $interestInWorkingInThisIndustry;

        return $this;
    }

    public function getTeachLessonExperience(): ?TeachLessonExperience
    {
        return $this->teachLessonExperience;
    }

    public function setTeachLessonExperience(?TeachLessonExperience $teachLessonExperience): self
    {
        $this->teachLessonExperience = $teachLessonExperience;

        return $this;
    }
}
