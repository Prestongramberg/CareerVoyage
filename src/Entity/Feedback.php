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
 * @ORM\DiscriminatorMap({"studentReviewCompanyExperienceFeedback" = "StudentReviewCompanyExperienceFeedback", "studentReviewTeachLessonExperienceFeedback" = "StudentReviewTeachLessonExperienceFeedback", "educatorReviewCompanyExperienceFeedback" = "EducatorReviewCompanyExperienceFeedback", "educatorReviewTeachLessonExperienceFeedback" = "EducatorReviewTeachLessonExperienceFeedback"})
 */
abstract class Feedback
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
    protected $rating = 4;

    /**
     * @Assert\NotNull(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="boolean")
     */
    protected $providedCareerInsight = true;

    /**
     * @Assert\NotNull(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="boolean")
     */
    protected $wasEnjoyableAndEngaging = true;

    /**
     * @Assert\NotNull(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="boolean")
     */
    protected $learnSomethingNew = true;

    /**
     * @Assert\NotBlank(message="This cannot be blank!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="integer")
     */
    protected $likelihoodToRecommendToFriend = 4;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $additionalFeedback;

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
}
