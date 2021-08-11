<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RequestRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "request" = "Request",
 *     "teachLessonRequest" = "TeachLessonRequest",
 *     "educatorRegisterStudentForCompanyExperienceRequest" = "EducatorRegisterStudentForCompanyExperienceRequest",
 *     "educatorRegisterEducatorForCompanyExperienceRequest" = "EducatorRegisterEducatorForCompanyExperienceRequest",
 *     "schoolAdminRegisterSAForCompanyExperienceRequest" = "SchoolAdminRegisterSAForCompanyExperienceRequest",
 *     "studentToMeetProfessionalRequest" = "StudentToMeetProfessionalRequest",
 *     "userRegisterForSchoolExperienceRequest" = "UserRegisterForSchoolExperienceRequest",
 * })
 */
class Request
{
    use Timestampable;

    const BECOME_STATE_COORDINATOR = 'BECOME_STATE_COORDINATOR';

    const OPPORTUNITY_TYPE_VIRTUAL              = 'VIRTUAL';
    const OPPORTUNITY_TYPE_IN_PERSON            = 'IN_PERSON';
    const OPPORTUNITY_TYPE_VIRTUAL_OR_IN_PERSON = 'VIRTUAL_OR_IN_PERSON';
    const OPPORTUNITY_TYPE_TO_BE_DETERMINED     = 'TO_BE_DETERMINED';

    const REQUEST_TYPE_JOB_BOARD      = 'JOB_BOARD';
    const REQUEST_TYPE_NEW_COMPANY    = 'NEW_COMPANY';
    const REQUEST_TYPE_JOIN_COMPANY   = 'JOIN_COMPANY';
    const REQUEST_TYPE_COMPANY_INVITE = 'COMPANY_INVITE';
    const REQUEST_TYPE_NOTIFICATION   = 'NOTIFICATION';

    public static $opportunityTypes = [
        'Virtual' => self::OPPORTUNITY_TYPE_VIRTUAL,
        'In person' => self::OPPORTUNITY_TYPE_IN_PERSON,
        'Virtual or in person' => self::OPPORTUNITY_TYPE_VIRTUAL_OR_IN_PERSON,
        'To be determined' => self::OPPORTUNITY_TYPE_TO_BE_DETERMINED,
    ];

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
    protected $schoolAdminHasSeen = false;

    /**
     * @Assert\NotBlank(message="Please enter a short summary", groups={"CREATE_REQUEST", "EDIT_REQUEST"})
     * @Assert\Length(
     *      max = 70,
     *      maxMessage = "Your summary cannot be longer than {{ limit }} characters",
     *      groups={"CREATE_REQUEST", "EDIT_REQUEST"}
     * )
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $summary;

    /**
     * @Assert\NotBlank(message="Please enter a description", groups={"CREATE_REQUEST", "EDIT_REQUEST"})
     * @Assert\Length(
     *      max = 280,
     *      maxMessage = "Your description cannot be longer than {{ limit }} characters",
     *      groups={"CREATE_REQUEST", "EDIT_REQUEST"}
     * )
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "Please select volunteer role(s)",
     *      groups={"CREATE_REQUEST", "EDIT_REQUEST"}
     * )
     *
     * @ORM\ManyToMany(targetEntity=RolesWillingToFulfill::class, inversedBy="requests")
     */
    protected $volunteerRoles;

    /**
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "Please select career sector(s)",
     *      groups={"CREATE_REQUEST", "EDIT_REQUEST"}
     * )
     *
     * @ORM\ManyToMany(targetEntity=Industry::class, inversedBy="requests")
     */
    protected $primaryIndustries;

    /**
     * @Assert\NotBlank(message="Pleas select an opportunity type", groups={"CREATE_REQUEST", "EDIT_REQUEST"})
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $opportunityType;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $published = false;

    /**
     * @ORM\OneToMany(targetEntity=Share::class, mappedBy="request", orphanRemoval=true)
     */
    private $shares;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $requestType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $actionUrl;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $needsApprovalByRoles = [];

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="requests")
     */
    private $company;

    /**
     * @ORM\OneToMany(targetEntity=RequestAction::class, mappedBy="request")
     */
    private $requestActions;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $possibleActions = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $notification = [];

    public function __construct()
    {
        $this->requestPossibleApprovers = new ArrayCollection();
        $this->volunteerRoles           = new ArrayCollection();
        $this->primaryIndustries        = new ArrayCollection();
        $this->shares                   = new ArrayCollection();
        $this->requestActions           = new ArrayCollection();
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

    public function wasCreatedByUser(User $user)
    {
        return $user->getId() === $this->created_by->getId();
    }

    public function needsApprovalByUser(User $user)
    {
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

    public function getSchoolAdminHasSeen(): ?bool
    {
        return $this->schoolAdminHasSeen;
    }

    public function setSchoolAdminHasSeen(?bool $schoolAdminHasSeen): self
    {
        $this->schoolAdminHasSeen = $schoolAdminHasSeen;

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

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|RolesWillingToFulfill[]
     */
    public function getVolunteerRoles(): Collection
    {
        return $this->volunteerRoles;
    }

    public function addVolunteerRole(RolesWillingToFulfill $volunteerRole): self
    {
        if (!$this->volunteerRoles->contains($volunteerRole)) {
            $this->volunteerRoles[] = $volunteerRole;
        }

        return $this;
    }

    public function removeVolunteerRole(RolesWillingToFulfill $volunteerRole): self
    {
        $this->volunteerRoles->removeElement($volunteerRole);

        return $this;
    }

    /**
     * @return Collection|Industry[]
     */
    public function getPrimaryIndustries(): Collection
    {
        return $this->primaryIndustries;
    }

    public function addPrimaryIndustry(Industry $primaryIndustry): self
    {
        if (!$this->primaryIndustries->contains($primaryIndustry)) {
            $this->primaryIndustries[] = $primaryIndustry;
        }

        return $this;
    }

    public function removePrimaryIndustry(Industry $primaryIndustry): self
    {
        $this->primaryIndustries->removeElement($primaryIndustry);

        return $this;
    }

    public function getOpportunityType(): ?string
    {
        return $this->opportunityType;
    }

    public function setOpportunityType(?string $opportunityType): self
    {
        $this->opportunityType = $opportunityType;

        return $this;
    }

    public function getPublished(): ?bool
    {
        if (!$this->published) {
            return false;
        }

        return $this->published;
    }

    public function setPublished(?bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @return Collection|Share[]
     */
    public function getShares(): Collection
    {
        return $this->shares;
    }

    public function addShare(Share $share): self
    {
        if (!$this->shares->contains($share)) {
            $this->shares[] = $share;
            $share->setRequest($this);
        }

        return $this;
    }

    public function removeShare(Share $share): self
    {
        if ($this->shares->removeElement($share)) {
            // set the owning side to null (unless already changed)
            if ($share->getRequest() === $this) {
                $share->setRequest(null);
            }
        }

        return $this;
    }

    public function getRequestType(): ?string
    {
        return $this->requestType;
    }

    public function setRequestType(?string $requestType): self
    {
        $this->requestType = $requestType;

        return $this;
    }

    public function getOpportunityTypeFriendlyName($opportunityType)
    {

        if (($key = array_search($opportunityType, self::$opportunityTypes, true)) !== false) {
            return $key;
        }

        return $opportunityType;
    }

    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }

    public function setActionUrl(?string $actionUrl): self
    {
        $this->actionUrl = $actionUrl;

        return $this;
    }

    public function getNeedsApprovalByRoles(): ?array
    {
        return $this->needsApprovalByRoles;
    }

    public function setNeedsApprovalByRoles(?array $needsApprovalByRoles): self
    {
        $this->needsApprovalByRoles = $needsApprovalByRoles;

        return $this;
    }

    /**
     * @param $needsApprovalByRole
     *
     * @return $this
     */
    public function addNeedsApprovalByRole($needsApprovalByRole)
    {

        if (!in_array($needsApprovalByRole, $this->needsApprovalByRoles, true)) {
            $this->needsApprovalByRoles[] = $needsApprovalByRole;
        }

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection|RequestAction[]
     */
    public function getRequestActions(): Collection
    {
        return $this->requestActions;
    }

    public function addRequestAction(RequestAction $requestAction): self
    {
        if (!$this->requestActions->contains($requestAction)) {
            $this->requestActions[] = $requestAction;
            $requestAction->setRequest($this);
        }

        return $this;
    }

    public function removeRequestAction(RequestAction $requestAction): self
    {
        if ($this->requestActions->removeElement($requestAction)) {
            // set the owning side to null (unless already changed)
            if ($requestAction->getRequest() === $this) {
                $requestAction->setRequest(null);
            }
        }

        return $this;
    }

    public function isApproved()
    {

        foreach ($this->getRequestActions() as $requestAction) {
            if ($requestAction->getName() === RequestAction::REQUEST_ACTION_NAME_APPROVE) {
                return true;
            }
        }

        return false;
    }

    public function isDenied()
    {

        foreach ($this->getRequestActions() as $requestAction) {
            if ($requestAction->getName() === RequestAction::REQUEST_ACTION_NAME_DENY) {
                return true;
            }
        }

        return false;
    }

    public function isHidden()
    {

        foreach ($this->getRequestActions() as $requestAction) {
            if ($requestAction->getName() === RequestAction::REQUEST_ACTION_NAME_HIDE) {
                return true;
            }
        }

        return false;
    }

    public function getPossibleActions(): ?array
    {
        return $this->possibleActions;
    }

    public function setPossibleActions(?array $possibleActions): self
    {
        $this->possibleActions = $possibleActions;

        return $this;
    }

    public function addPossibleAction($actions)
    {
        $actions = is_array($actions) ? $actions : [$actions];

        foreach ($actions as $action) {
            if (!in_array($action, $this->possibleActions, true)) {
                $this->possibleActions[] = $action;
            }
        }


        return $this;
    }

    public function removePossibleAction($actions)
    {
        $actions = is_array($actions) ? $actions : [$actions];

        foreach ($actions as $action) {

            if (($key = array_search($action, $this->possibleActions)) !== false) {
                unset($this->possibleActions[$key]);
            }
        }

        return $this;
    }

    public function hasPossibleAction($action)
    {
        if (in_array($action, $this->possibleActions, true)) {
            return true;
        }

        return false;
    }

    public function getPossibleActionCssClass($action)
    {

        switch ($action) {
            case RequestAction::REQUEST_ACTION_NAME_APPROVE:
                return 'uk-button-primary';
                break;
            case RequestAction::REQUEST_ACTION_NAME_DENY:
                return 'uk-button-danger';
                break;
            case RequestAction::REQUEST_ACTION_NAME_REMOVE_FROM_COMPANY:
                return 'uk-button-danger';
                break;
            default:
                return 'uk-button-default';
                break;
        }
    }

    public function getButtonFriendlyNameForRequestAction($action)
    {

        if ($action === RequestAction::REQUEST_ACTION_NAME_APPROVE) {
            return 'Approve';
        }

        if ($action === RequestAction::REQUEST_ACTION_NAME_DENY) {
            return 'Deny';
        }

        if ($action === RequestAction::REQUEST_ACTION_NAME_HIDE) {
            return 'Hide';
        }

        if ($action === RequestAction::REQUEST_ACTION_NAME_REMOVE_FROM_COMPANY) {
            return 'Remove from company';
        }

        return $action;
    }

    public function getNotification(): ?array
    {
        return $this->notification;
    }

    public function setNotification(?array $notification): self
    {
        $this->notification = $notification;

        return $this;
    }
}
