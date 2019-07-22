<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LessonRepository")
 */
class Lesson
{
    /**
     * @Groups({"LESSON_DATA"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"LESSON_DATA"})
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @Groups({"LESSON_DATA"})
     * @ORM\ManyToMany(targetEntity="App\Entity\Career", inversedBy="lessons")
     */
    private $careers;

    /**
     * @Groups({"LESSON_DATA"})
     * @ORM\ManyToMany(targetEntity="App\Entity\Grade", inversedBy="lessons")
     */
    private $grades;

    /**
     * @Groups({"LESSON_DATA"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Course", inversedBy="lessons")
     * @ORM\JoinColumn(nullable=true)
     */
    private $primaryCourse;

    /**
     * @Groups({"LESSON_DATA"})
     * @ORM\ManyToMany(targetEntity="App\Entity\Course", inversedBy="lessons")
     */
    private $secondaryCourses;

    /**
     * @Groups({"LESSON_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $summary;

    /**
     * @Groups({"LESSON_DATA"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $learningOutcomes;

    /**
     * @Groups({"LESSON_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $educationalStandards;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $thumbnailImage;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $featuredImage;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LessonFavorite", mappedBy="lesson", orphanRemoval=true)
     */
    private $lessonFavorites;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="lessons")
     */
    private $user;

    /**
     * @var boolean
     */
    private $isFavorite;

    /**
     * @var boolean
     */
    private $isTeachable;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LessonTeachable", mappedBy="lesson", orphanRemoval=true)
     */
    private $lessonTeachables;

    public function __construct()
    {
        $this->careers = new ArrayCollection();
        $this->grades = new ArrayCollection();
        $this->secondaryCourses = new ArrayCollection();
        $this->lessonFavorites = new ArrayCollection();
        $this->lessonTeachables = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|Career[]
     */
    public function getCareers()
    {
        return $this->careers;
    }

    public function addCareer(Career $career)
    {
        if (!$this->careers->contains($career)) {
            $this->careers[] = $career;
        }

        return $this;
    }

    public function removeCareer(Career $career)
    {
        if ($this->careers->contains($career)) {
            $this->careers->removeElement($career);
        }

        return $this;
    }

    /**
     * @return Collection|Grade[]
     */
    public function getGrades()
    {
        return $this->grades;
    }

    public function addGrade(Grade $grade)
    {
        if (!$this->grades->contains($grade)) {
            $this->grades[] = $grade;
        }

        return $this;
    }

    public function removeGrade(Grade $grade)
    {
        if ($this->grades->contains($grade)) {
            $this->grades->removeElement($grade);
        }

        return $this;
    }

    public function getPrimaryCourse()
    {
        return $this->primaryCourse;
    }

    public function setPrimaryCourse(Course $primaryCourse)
    {
        $this->primaryCourse = $primaryCourse;

        return $this;
    }

    /**
     * @return Collection|Course[]
     */
    public function getSecondaryCourses()
    {
        return $this->secondaryCourses;
    }

    public function addSecondaryCourse(Course $secondaryCourse)
    {
        if (!$this->secondaryCourses->contains($secondaryCourse)) {
            $this->secondaryCourses[] = $secondaryCourse;
        }

        return $this;
    }

    public function removeSecondaryCourse(Course $secondaryCourse)
    {
        if ($this->secondaryCourses->contains($secondaryCourse)) {
            $this->secondaryCourses->removeElement($secondaryCourse);
        }

        return $this;
    }

    public function getSummary()
    {
        return $this->summary;
    }

    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    public function getLearningOutcomes()
    {
        return $this->learningOutcomes;
    }

    public function setLearningOutcomes($learningOutcomes)
    {
        $this->learningOutcomes = $learningOutcomes;

        return $this;
    }

    public function getEducationalStandards()
    {
        return $this->educationalStandards;
    }

    public function setEducationalStandards($educationalStandards)
    {
        $this->educationalStandards = $educationalStandards;

        return $this;
    }

    public function getThumbnailImage()
    {
        return $this->thumbnailImage;
    }

    public function setThumbnailImage($thumbnailImage)
    {
        $this->thumbnailImage = $thumbnailImage;

        return $this;
    }

    public function getFeaturedImage()
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage($featuredImage)
    {
        $this->featuredImage = $featuredImage;

        return $this;
    }

    /**
     * @return Collection|LessonFavorite[]
     */
    public function getLessonFavorites()
    {
        return $this->lessonFavorites;
    }

    public function addLessonFavorite(LessonFavorite $lessonFavorite)
    {
        if (!$this->lessonFavorites->contains($lessonFavorite)) {
            $this->lessonFavorites[] = $lessonFavorite;
            $lessonFavorite->setLesson($this);
        }

        return $this;
    }

    public function removeLessonFavorite(LessonFavorite $lessonFavorite)
    {
        if ($this->lessonFavorites->contains($lessonFavorite)) {
            $this->lessonFavorites->removeElement($lessonFavorite);
            // set the owning side to null (unless already changed)
            if ($lessonFavorite->getLesson() === $this) {
                $lessonFavorite->setLesson(null);
            }
        }

        return $this;
    }

    public function getFeaturedImagePath()
    {
        return UploaderHelper::LESSON_FEATURED.'/'.$this->getFeaturedImage();
    }

    public function getThumbnailImagePath()
    {
        return UploaderHelper::LESSON_THUMBNAIL.'/'.$this->getThumbnailImage();
    }

    /**
     * @Groups({"LESSON_DATA"})
     */
    public function getThumbnailImageURL() {
        if($this->getThumbnailImage()) {
            return '/media/cache/squared_thumbnail_small/uploads/' . $this->getThumbnailImagePath();
        }
        return '';
    }

    /**
     * @Groups({"LESSON_DATA"})
     */
    public function getFeaturedImageURL() {
        if($this->getFeaturedImage()) {
            return '/uploads/' . $this->getFeaturedImagePath();
        }
        return '';
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @Groups({"LESSON_DATA"})
     * @return bool
     */
    public function isFavorite()
    {
        return $this->isFavorite;
    }

    /**
     * @param bool $isFavorite
     */
    public function setIsFavorite($isFavorite)
    {
        $this->isFavorite = $isFavorite;
    }

    /**
     * @Groups({"LESSON_DATA"})
     * @return bool
     */
    public function isTeachable()
    {
        return $this->isTeachable;
    }

    /**
     * @param bool $isTeachable
     */
    public function setIsTeachable($isTeachable)
    {
        $this->isTeachable = $isTeachable;
    }

    /**
     * @return Collection|LessonTeachable[]
     */
    public function getLessonTeachables(): Collection
    {
        return $this->lessonTeachables;
    }

    public function addLessonTeachable(LessonTeachable $lessonTeachable): self
    {
        if (!$this->lessonTeachables->contains($lessonTeachable)) {
            $this->lessonTeachables[] = $lessonTeachable;
            $lessonTeachable->setLesson($this);
        }

        return $this;
    }

    public function removeLessonTeachable(LessonTeachable $lessonTeachable): self
    {
        if ($this->lessonTeachables->contains($lessonTeachable)) {
            $this->lessonTeachables->removeElement($lessonTeachable);
            // set the owning side to null (unless already changed)
            if ($lessonTeachable->getLesson() === $this) {
                $lessonTeachable->setLesson(null);
            }
        }

        return $this;
    }
}
