<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfessionalReviewTeachLessonExperienceFeedbackRepository")
 */
class ProfessionalReviewTeachLessonExperienceFeedback extends Feedback
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lesson")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $lesson;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProfessionalUser")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $professional;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TeachLessonExperience")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
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

    public function getProfessional(): ?ProfessionalUser
    {
        return $this->professional;
    }

    public function setProfessional(?ProfessionalUser $professional): self
    {
        $this->professional = $professional;

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
