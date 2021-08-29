<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StudentToMeetProfessionalExperienceRepository")
 */
class StudentToMeetProfessionalExperience extends Experience
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProfessionalReviewMeetStudentExperienceFeedback", mappedBy="studentToMeetProfessionalExperience")
     */
    private $professionalReviewMeetStudentExperienceFeedback;

    public function __construct()
    {
        parent::__construct();
        $this->professionalReviewMeetStudentExperienceFeedback = new ArrayCollection();
    }

    /**
     * @return Collection|ProfessionalReviewMeetStudentExperienceFeedback[]
     */
    public function getProfessionalReviewMeetStudentExperienceFeedback(): Collection
    {
        return $this->professionalReviewMeetStudentExperienceFeedback;
    }

    public function addProfessionalReviewMeetStudentExperienceFeedback(ProfessionalReviewMeetStudentExperienceFeedback $professionalReviewMeetStudentExperienceFeedback): self
    {
        if (!$this->professionalReviewMeetStudentExperienceFeedback->contains($professionalReviewMeetStudentExperienceFeedback)) {
            $this->professionalReviewMeetStudentExperienceFeedback[] = $professionalReviewMeetStudentExperienceFeedback;
            $professionalReviewMeetStudentExperienceFeedback->setStudentToMeetProfessionalExperience($this);
        }

        return $this;
    }

    public function removeProfessionalReviewMeetStudentExperienceFeedback(ProfessionalReviewMeetStudentExperienceFeedback $professionalReviewMeetStudentExperienceFeedback): self
    {
        if ($this->professionalReviewMeetStudentExperienceFeedback->contains($professionalReviewMeetStudentExperienceFeedback)) {
            $this->professionalReviewMeetStudentExperienceFeedback->removeElement($professionalReviewMeetStudentExperienceFeedback);
            // set the owning side to null (unless already changed)
            if ($professionalReviewMeetStudentExperienceFeedback->getStudentToMeetProfessionalExperience() === $this) {
                $professionalReviewMeetStudentExperienceFeedback->setStudentToMeetProfessionalExperience(null);
            }
        }

        return $this;
    }
}
