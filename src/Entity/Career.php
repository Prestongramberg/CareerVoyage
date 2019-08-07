<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CareerRepository")
 */
class Career
{
    public static $types= [
        'Agriculture, Food & Natural Resources',
        'Architecture & Construction',
        'Arts, A/V Technology & Communications',
        'Business Management & Administration',
        'Education & Training',
        'Finance',
        'Government & Public Administration',
        'Health Science',
        'Hospitality & Tourism',
        'Human Services',
        'Information Technology',
        'Law, Public Safety, Corrections & Security',
        'Manufacturing',
        'Marketing',
        'Science, Technology, Engineering & Mathematics',
        'Transportation, Distribution & Logistics'
    ];

    /**
     * @Groups({"LESSON_DATA", "EXPERIENCE_DATA"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"LESSON_DATA", "EXPERIENCE_DATA"})
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Lesson", mappedBy="careers")
     */
    private $lessons;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Experience", mappedBy="careers")
     */
    private $experiences;

    public function __construct()
    {
        $this->lessons = new ArrayCollection();
        $this->experiences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|Lesson[]
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function addLesson(Lesson $lesson): self
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons[] = $lesson;
            $lesson->addCareer($this);
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): self
    {
        if ($this->lessons->contains($lesson)) {
            $this->lessons->removeElement($lesson);
            $lesson->removeCareer($this);
        }

        return $this;
    }

    /**
     * @return Collection|Experience[]
     */
    public function getExperiences(): Collection
    {
        return $this->experiences;
    }

    public function addExperience(Experience $experience): self
    {
        if (!$this->experiences->contains($experience)) {
            $this->experiences[] = $experience;
            $experience->addCareer($this);
        }

        return $this;
    }

    public function removeExperience(Experience $experience): self
    {
        if ($this->experiences->contains($experience)) {
            $this->experiences->removeElement($experience);
            $experience->removeCareer($this);
        }

        return $this;
    }
}
