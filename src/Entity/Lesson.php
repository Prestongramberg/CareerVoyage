<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LessonRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Lesson
{
    use Timestampable;

    /**
     * @Groups({"LESSON_DATA", "ALL_USER_DATA"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"LESSON_DATA", "ALL_USER_DATA"})
     * @Assert\NotBlank(message="Don't forget a title!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @Assert\Count(
     *      min = 1,
     *      minMessage = "You must specify at least one grade.",
     *     groups={"CREATE", "EDIT"}
     * )
     * @Groups({"LESSON_DATA"})
     * @ORM\ManyToMany(targetEntity="App\Entity\Grade", inversedBy="lessons")
     */
    private $grades;

    /**
     * @Assert\NotNull(message="Don't forget to select a primary course!", groups={"CREATE", "EDIT"})
     * @Groups({"LESSON_DATA"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Course", inversedBy="lessons")
     * @ORM\JoinColumn(nullable=true)
     */
    private $primaryCourse;

    /**
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "Don't forget to select at least one secondary course",
     *     groups={"CREATE", "EDIT"}
     * )
     * @Groups({"LESSON_DATA"})
     * @ORM\ManyToMany(targetEntity="App\Entity\Course", inversedBy="lessons")
     */
    private $secondaryCourses;

    /**
     * @Assert\NotBlank(message="Don't forget a summary!", groups={"CREATE", "EDIT"})
     * @Groups({"LESSON_DATA", "ALL_USER_DATA"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $summary;

    /**
     * @Assert\NotBlank(message="Don't forget learning outcomes!", groups={"CREATE", "EDIT"})
     * @Groups({"LESSON_DATA", "ALL_USER_DATA"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $learningOutcomes;

    /**
     * @Groups({"LESSON_DATA", "ALL_USER_DATA"})
     * @ORM\Column(type="text", nullable=true)
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
     * @ORM\JoinColumn(onDelete="SET NULL")
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

    /**
     * @Groups({"LESSON_DATA"})
     * @Assert\NotBlank(message="Don't forget a short description!", groups={"CREATE", "EDIT"})
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $shortDescription;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LessonResource", mappedBy="lesson", orphanRemoval=true)
     */
    private $lessonResources;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Industry", inversedBy="lessons")
     */
    private $primaryIndustry;

    /**
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "You must specify at least one secondary industry",
     *     groups={"SECONDARY_INDUSTRY"}
     * )
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\SecondaryIndustry", inversedBy="lessons")
     */
    private $secondaryIndustries;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeachLessonRequest", mappedBy="lesson", orphanRemoval=true)
     */
    private $teachLessonRequests;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StudentReviewTeachLessonExperienceFeedback", mappedBy="lesson", orphanRemoval=true)
     */
    private $studentReviewTeachLessonExperienceFeedback;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EducatorReviewTeachLessonExperienceFeedback", mappedBy="lesson", orphanRemoval=true)
     */
    private $educatorReviewTeachLessonExperienceFeedback;


    public function __construct()
    {
        $this->grades = new ArrayCollection();
        $this->secondaryCourses = new ArrayCollection();
        $this->lessonFavorites = new ArrayCollection();
        $this->lessonTeachables = new ArrayCollection();
        $this->lessonResources = new ArrayCollection();
        $this->secondaryIndustries = new ArrayCollection();
        $this->teachLessonRequests = new ArrayCollection();
        $this->studentReviewTeachLessonExperienceFeedback = new ArrayCollection();
        $this->educatorReviewTeachLessonExperienceFeedback = new ArrayCollection();
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

    public function setPrimaryCourse(?Course $primaryCourse)
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

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * @return Collection|LessonResource[]
     */
    public function getLessonResources(): Collection
    {
        return $this->lessonResources;
    }

    public function addLessonResource(LessonResource $lessonResource): self
    {
        if (!$this->lessonResources->contains($lessonResource)) {
            $this->lessonResources[] = $lessonResource;
            $lessonResource->setLesson($this);
        }

        return $this;
    }

    public function removeLessonResource(LessonResource $lessonResource): self
    {
        if ($this->lessonResources->contains($lessonResource)) {
            $this->lessonResources->removeElement($lessonResource);
            // set the owning side to null (unless already changed)
            if ($lessonResource->getLesson() === $this) {
                $lessonResource->setLesson(null);
            }
        }

        return $this;
    }

    public function getPrimaryIndustry(): ?Industry
    {
        return $this->primaryIndustry;
    }

    public function setPrimaryIndustry(?Industry $primaryIndustry): self
    {
        $this->primaryIndustry = $primaryIndustry;

        return $this;
    }

    /**
     * @return Collection|SecondaryIndustry[]
     */
    public function getSecondaryIndustries(): Collection
    {
        return $this->secondaryIndustries;
    }

    public function addSecondaryIndustry(SecondaryIndustry $secondaryIndustry): self
    {
        if (!$this->secondaryIndustries->contains($secondaryIndustry)) {
            $this->secondaryIndustries[] = $secondaryIndustry;
        }

        return $this;
    }

    public function removeSecondaryIndustry(SecondaryIndustry $secondaryIndustry): self
    {
        if ($this->secondaryIndustries->contains($secondaryIndustry)) {
            $this->secondaryIndustries->removeElement($secondaryIndustry);
        }

        return $this;
    }

    public function isTeachableByUser(User $user)
    {
        return ($this->lessonTeachables->filter(
                function (LessonTeachable $lessonTeachable) use ($user) {
                    return $lessonTeachable->getUser()->getId() === $user->getId();
                }
            )->count() > 0);
    }

    public function isFavoritedByUser(User $user)
    {
        return ($this->lessonFavorites->filter(
                function (LessonFavorite $lessonFavorite) use ($user) {
                    return $lessonFavorite->getUser()->getId() === $user->getId();
                }
            )->count() > 0);
    }

    /**
     * @return Collection|TeachLessonRequest[]
     */
    public function getTeachLessonRequests(): Collection
    {
        return $this->teachLessonRequests;
    }

    public function addTeachLessonRequest(TeachLessonRequest $teachLessonRequest): self
    {
        if (!$this->teachLessonRequests->contains($teachLessonRequest)) {
            $this->teachLessonRequests[] = $teachLessonRequest;
            $teachLessonRequest->setLesson($this);
        }

        return $this;
    }

    public function removeTeachLessonRequest(TeachLessonRequest $teachLessonRequest): self
    {
        if ($this->teachLessonRequests->contains($teachLessonRequest)) {
            $this->teachLessonRequests->removeElement($teachLessonRequest);
            // set the owning side to null (unless already changed)
            if ($teachLessonRequest->getLesson() === $this) {
                $teachLessonRequest->setLesson(null);
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
            $studentReviewTeachLessonExperienceFeedback->setLesson($this);
        }

        return $this;
    }

    public function removeStudentReviewTeachLessonExperienceFeedback(StudentReviewTeachLessonExperienceFeedback $studentReviewTeachLessonExperienceFeedback): self
    {
        if ($this->studentReviewTeachLessonExperienceFeedback->contains($studentReviewTeachLessonExperienceFeedback)) {
            $this->studentReviewTeachLessonExperienceFeedback->removeElement($studentReviewTeachLessonExperienceFeedback);
            // set the owning side to null (unless already changed)
            if ($studentReviewTeachLessonExperienceFeedback->getLesson() === $this) {
                $studentReviewTeachLessonExperienceFeedback->setLesson(null);
            }
        }

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
            $educatorReviewTeachLessonExperienceFeedback->setLesson($this);
        }

        return $this;
    }

    public function removeEducatorReviewTeachLessonExperienceFeedback(EducatorReviewTeachLessonExperienceFeedback $educatorReviewTeachLessonExperienceFeedback): self
    {
        if ($this->educatorReviewTeachLessonExperienceFeedback->contains($educatorReviewTeachLessonExperienceFeedback)) {
            $this->educatorReviewTeachLessonExperienceFeedback->removeElement($educatorReviewTeachLessonExperienceFeedback);
            // set the owning side to null (unless already changed)
            if ($educatorReviewTeachLessonExperienceFeedback->getLesson() === $this) {
                $educatorReviewTeachLessonExperienceFeedback->setLesson(null);
            }
        }

        return $this;
    }

}
