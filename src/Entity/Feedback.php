<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FeedbackRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"feedback" = "Feedback", "studentReviewCompanyExperienceFeedback" = "StudentReviewCompanyExperienceFeedback", "studentReviewTeachLessonExperienceFeedback" = "StudentReviewTeachLessonExperienceFeedback", "educatorReviewCompanyExperienceFeedback" = "EducatorReviewCompanyExperienceFeedback", "educatorReviewTeachLessonExperienceFeedback" = "EducatorReviewTeachLessonExperienceFeedback", "professionalReviewStudentToMeetProfessionalFeedback" = "ProfessionalReviewStudentToMeetProfessionalFeedback", "professionalReviewMeetStudentExperienceFeedback" = "ProfessionalReviewMeetStudentExperienceFeedback", "studentReviewMeetProfessionalExperienceFeedback" = "StudentReviewMeetProfessionalExperienceFeedback"})
 *
 */
class Feedback
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @Assert\NotBlank(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="integer")
     */
    protected $rating = 0;

    /**
     * @Assert\NotNull(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="boolean")
     */
    protected $providedCareerInsight = false;

    /**
     * @Assert\NotNull(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="boolean")
     */
    protected $wasEnjoyableAndEngaging = false;

    /**
     * @Assert\NotNull(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="boolean")
     */
    protected $learnSomethingNew = false;

    /**
     * @Assert\NotBlank(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="integer")
     */
    protected $likelihoodToRecommendToFriend = 7;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $additionalFeedback;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="feedback")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Experience", inversedBy="feedback")
     */
    protected $experience;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getProvidedCareerInsight(): ?bool
    {
        return $this->providedCareerInsight;
    }

    public function setProvidedCareerInsight(bool $providedCareerInsight): self
    {
        $this->providedCareerInsight = $providedCareerInsight;

        return $this;
    }

    public function getWasEnjoyableAndEngaging(): ?bool
    {
        return $this->wasEnjoyableAndEngaging;
    }

    public function setWasEnjoyableAndEngaging(bool $wasEnjoyableAndEngaging): self
    {
        $this->wasEnjoyableAndEngaging = $wasEnjoyableAndEngaging;

        return $this;
    }

    public function getLearnSomethingNew(): ?bool
    {
        return $this->learnSomethingNew;
    }

    public function setLearnSomethingNew(bool $learnSomethingNew): self
    {
        $this->learnSomethingNew = $learnSomethingNew;

        return $this;
    }

    public function getLikelihoodToRecommendToFriend(): ?int
    {
        return $this->likelihoodToRecommendToFriend;
    }

    public function setLikelihoodToRecommendToFriend(int $likelihoodToRecommendToFriend): self
    {
        $this->likelihoodToRecommendToFriend = $likelihoodToRecommendToFriend;

        return $this;
    }

    public function getAdditionalFeedback(): ?string
    {
        return $this->additionalFeedback;
    }

    public function setAdditionalFeedback(?string $additionalFeedback): self
    {
        $this->additionalFeedback = $additionalFeedback;

        return $this;
    }

    public function getClassName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getExperience(): ?Experience
    {
        return $this->experience;
    }

    public function setExperience(?Experience $experience): self
    {
        $this->experience = $experience;

        return $this;
    }
}
