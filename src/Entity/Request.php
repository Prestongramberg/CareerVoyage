<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RequestRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "newCompanyRequest" = "NewCompanyRequest",
 *     "joinCompanyRequest" = "JoinCompanyRequest",
 *     "teachLessonRequest" = "TeachLessonRequest",
 *     "educatorRegisterStudentForCompanyExperienceRequest" = "EducatorRegisterStudentForCompanyExperienceRequest",
 *     "educatorRegisterEducatorForCompanyExperienceRequest" = "EducatorRegisterEducatorForCompanyExperienceRequest",
 *     "studentToMeetProfessionalRequest" = "StudentToMeetProfessionalRequest",
 *     "userRegisterForSchoolExperienceRequest" = "UserRegisterForSchoolExperienceRequest"
 
 * })
 */
abstract class Request
{
    use Timestampable;

    const BECOME_STATE_COORDINATOR = 'BECOME_STATE_COORDINATOR';

    /**
     * @Groups({"RESULTS_PAGE", "REQUEST"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     *
     * @Groups({"RESULTS_PAGE", "PROFESSIONAL_USER_DATA", "REQUEST"})
     * @ORM\Column(type="boolean")
     */
    protected $approved = false;

    /**
     * @Groups({"REQUEST"})
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="requests")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $created_by;

    /**
     * @Groups({"REQUEST"})
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="requestsThatNeedMyApproval")
     */
    protected $needsApprovalBy;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $denied = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $activationCode;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $allowApprovalByActivationCode = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RequestPossibleApprovers", mappedBy="request", orphanRemoval=true)
     */
    private $requestPossibleApprovers;


    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":true})
     */
    protected $studentHasSeen = false;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":true})
     */
    protected $educatorHasSeen = false;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":true})
     */
    protected $professionalHasSeen = false;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":true})
     */
    protected $schoolAdministratorHasSeen = false;



    public function __construct()
    {
        $this->requestPossibleApprovers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApproved(): ?bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->created_by;
    }

    public function setCreatedBy(?User $created_by): self
    {
        $this->created_by = $created_by;

        return $this;
    }

    public function getNeedsApprovalBy(): ?User
    {
        return $this->needsApprovalBy;
    }

    public function setNeedsApprovalBy(?User $needsApprovalBy): self
    {
        $this->needsApprovalBy = $needsApprovalBy;

        return $this;
    }

    public function getClassName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function wasCreatedByUser(User $user) {
        return $user->getId() === $this->created_by->getId();
    }

    public function needsApprovalByUser(User $user) {
        return $user->getId() === $this->getNeedsApprovalBy()->getId();
    }

    public function getDenied(): ?bool
    {
        return $this->denied;
    }

    public function setDenied(bool $denied): self
    {
        $this->denied = $denied;

        return $this;
    }

    public function getActivationCode(): ?string
    {
        return $this->activationCode;
    }

    public function setActivationCode(?string $activationCode): self
    {
        $this->activationCode = $activationCode;

        return $this;
    }

    public function getAllowApprovalByActivationCode(): ?bool
    {
        return $this->allowApprovalByActivationCode;
    }

    public function setAllowApprovalByActivationCode(bool $allowApprovalByActivationCode): self
    {
        $this->allowApprovalByActivationCode = $allowApprovalByActivationCode;

        return $this;
    }


    public function getStudentHasSeen(): ?bool
    {
        return $this->studentHasSeen;
    }

    public function setStudentHasSeen(?bool $studentHasSeen): self
    {
        $this->studentHasSeen = $studentHasSeen;
        return $this;
    }

    public function getEducatorHasSeen(): ?bool
    {
        return $this->educatorHasSeen;
    }

    public function setEducatorHasSeen(?bool $educatorHasSeen): self
    {
        $this->educatorHasSeen = $educatorHasSeen;
        return $this;
    }

    public function getProfessionalHasSeen(): ?bool
    {
        return $this->professionalHasSeen;
    }

    public function setProfessionalHasSeen(?bool $professionalHasSeen): self
    {
        $this->professionalHasSeen = $professionalHasSeen;
        return $this;
    }

    public function getSchoolAdministratorHasSeen(): ?bool
    {
        return $this->schoolAdministratorHasSeen;
    }

    public function setSchoolAdministratorHasSeen(?bool $schoolAdministratorHasSeen): self
    {
        $this->schoolAdministratorHasSeen = $schoolAdministratorHasSeen;
        return $this;
    }


    public function initializeRequest()
    {
        $activationCode = bin2hex(random_bytes(32));
        $this->setActivationCode($activationCode);
    }

    /**
     * @return Collection|RequestPossibleApprovers[]
     */
    public function getRequestPossibleApprovers(): Collection
    {
        return $this->requestPossibleApprovers;
    }

    public function addRequestPossibleApprover(RequestPossibleApprovers $requestPossibleApprover): self
    {
        if (!$this->requestPossibleApprovers->contains($requestPossibleApprover)) {
            $this->requestPossibleApprovers[] = $requestPossibleApprover;
            $requestPossibleApprover->setRequest($this);
        }

        return $this;
    }

    public function removeRequestPossibleApprover(RequestPossibleApprovers $requestPossibleApprover): self
    {
        if ($this->requestPossibleApprovers->contains($requestPossibleApprover)) {
            $this->requestPossibleApprovers->removeElement($requestPossibleApprover);
            // set the owning side to null (unless already changed)
            if ($requestPossibleApprover->getRequest() === $this) {
                $requestPossibleApprover->setRequest(null);
            }
        }

        return $this;
    }
}
