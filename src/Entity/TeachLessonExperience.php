<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeachLessonExperienceRepository")
 */
class TeachLessonExperience extends Experience
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProfessionalUser", inversedBy="teachLessonExperiences")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $teacher;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\ManyToOne(targetEntity="App\Entity\School", inversedBy="teachLessonExperiences")
     * @ORM\JoinColumn(name="school_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $school;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EducatorReviewTeachLessonExperienceFeedback", mappedBy="teachLessonExperience")
     */
    private $educatorReviewTeachLessonExperienceFeedback;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentReviewTeachLessonExperienceFeedback", mappedBy="teachLessonExperience")
     */
    private $studentReviewTeachLessonExperienceFeedback;

    /**
     * @ORM\ManyToOne(targetEntity=Lesson::class, inversedBy="teachLessonExperiences")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $lesson;

    public function __construct()
    {
        parent::__construct();
        $this->educatorReviewTeachLessonExperienceFeedback = new ArrayCollection();
        $this->studentReviewTeachLessonExperienceFeedback = new ArrayCollection();
    }

    public function getTeacher(): ?ProfessionalUser
    {
        return $this->teacher;
    }

    public function setTeacher(?ProfessionalUser $teacher): self
    {
        $this->teacher = $teacher;

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

    /**
     * @return Collection|EducatorReviewTeachLessonExperienceFeedback[]
     */
    public function getEducatorReviewTeachLessonExperienceFeedback(): Collection
    {
        return $this->educatorReviewTeachLessonExperienceFeedback;
    }

    public function addEducatorReviewTeachLessonExperienceFeedback(EducatorReviewTeachLessonExperienceFeedback $educatorReviewTeachLessonExperienceFeedback): self
    {
        if (!$this->educatorReviewTeachLessonExperienceFeedback->contains($educatorReviewTeachLessonExperienceFeedback)) {
            $this->educatorReviewTeachLessonExperienceFeedback[] = $educatorReviewTeachLessonExperienceFeedback;
            $educatorReviewTeachLessonExperienceFeedback->setTeachLessonExperience($this);
        }

        return $this;
    }

    public function removeEducatorReviewTeachLessonExperienceFeedback(EducatorReviewTeachLessonExperienceFeedback $educatorReviewTeachLessonExperienceFeedback): self
    {
        if ($this->educatorReviewTeachLessonExperienceFeedback->contains($educatorReviewTeachLessonExperienceFeedback)) {
            $this->educatorReviewTeachLessonExperienceFeedback->removeElement($educatorReviewTeachLessonExperienceFeedback);
            // set the owning side to null (unless already changed)
            if ($educatorReviewTeachLessonExperienceFeedback->getTeachLessonExperience() === $this) {
                $educatorReviewTeachLessonExperienceFeedback->setTeachLessonExperience(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StudentReviewTeachLessonExperienceFeedback[]
     */
    public function getStudentReviewTeachLessonExperienceFeedback(): Collection
    {
        return $this->studentReviewTeachLessonExperienceFeedback;
    }

    public function addStudentReviewTeachLessonExperienceFeedback(StudentReviewTeachLessonExperienceFeedback $studentReviewTeachLessonExperienceFeedback): self
    {
        if (!$this->studentReviewTeachLessonExperienceFeedback->contains($studentReviewTeachLessonExperienceFeedback)) {
            $this->studentReviewTeachLessonExperienceFeedback[] = $studentReviewTeachLessonExperienceFeedback;
            $studentReviewTeachLessonExperienceFeedback->setTeachLessonExperience($this);
        }

        return $this;
    }

    public function removeStudentReviewTeachLessonExperienceFeedback(StudentReviewTeachLessonExperienceFeedback $studentReviewTeachLessonExperienceFeedback): self
    {
        if ($this->studentReviewTeachLessonExperienceFeedback->contains($studentReviewTeachLessonExperienceFeedback)) {
            $this->studentReviewTeachLessonExperienceFeedback->removeElement($studentReviewTeachLessonExperienceFeedback);
            // set the owning side to null (unless already changed)
            if ($studentReviewTeachLessonExperienceFeedback->getTeachLessonExperience() === $this) {
                $studentReviewTeachLessonExperienceFeedback->setTeachLessonExperience(null);
            }
        }

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

}
