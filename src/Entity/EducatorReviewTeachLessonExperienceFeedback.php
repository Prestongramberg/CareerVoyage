<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EducatorReviewTeachLessonExperienceFeedbackRepository")
 */
class EducatorReviewTeachLessonExperienceFeedback extends Feedback
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lesson", inversedBy="educatorReviewTeachLessonExperienceFeedback")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lesson;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EducatorUser", inversedBy="educatorReviewTeachLessonExperienceFeedback")
     * @ORM\JoinColumn(nullable=false)
     */
    private $educator;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TeachLessonExperience", inversedBy="educatorReviewTeachLessonExperienceFeedback")
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

    public function getEducator(): ?EducatorUser
    {
        return $this->educator;
    }

    public function setEducator(?EducatorUser $educator): self
    {
        $this->educator = $educator;

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
