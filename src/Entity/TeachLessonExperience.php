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
     * @ORM\OneToOne(targetEntity="App\Entity\TeachLessonRequest", inversedBy="teachLessonExperience", cascade={"persist", "remove"})
     */
    private $originalRequest;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProfessionalUser", inversedBy="teachLessonExperiences")
     */
    private $teacher;

    /**
     * @Groups({"EXPERIENCE_DATA"})
     * @ORM\ManyToOne(targetEntity="App\Entity\School", inversedBy="teachLessonExperiences")
     */
    private $school;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EducatorReviewTeachLessonExperienceFeedback", mappedBy="teachLessonExperience", orphanRemoval=true)
     */
    private $educatorReviewTeachLessonExperienceFeedback;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentReviewTeachLessonExperienceFeedback", mappedBy="teachLessonExperience", orphanRemoval=true)
     */
    private $studentReviewTeachLessonExperienceFeedback;

    public function __construct()
    {
        parent::__construct();
        $this->educatorReviewTeachLessonExperienceFeedback = new ArrayCollection();
        $this->studentReviewTeachLessonExperienceFeedback = new ArrayCollection();
    }

    public function getOriginalRequest(): ?TeachLessonRequest
    {
        return $this->originalRequest;
    }

    public function setOriginalRequest(?TeachLessonRequest $originalRequest): self
    {
        $this->originalRequest = $originalRequest;

        return $this;
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

    /**
     * @Groups({"EXPERIENCE_DATA", "ALL_USER_DATA"})
     * @return string
     */
    public function getFriendlyEventName() {
        return 'Teach Lesson Event';
    }
}
