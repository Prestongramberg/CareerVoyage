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
 *     "educatorRegisterStudentForCompanyExperienceRequest" = "EducatorRegisterStudentForCompanyExperienceRequest",
 *     "educatorRegisterEducatorForCompanyExperienceRequest" = "EducatorRegisterEducatorForCompanyExperienceRequest",
 *     "schoolAdminRegisterSAForCompanyExperienceRequest" = "SchoolAdminRegisterSAForCompanyExperienceRequest",
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

    const REQUEST_TYPE_JOB_BOARD           = 'JOB_BOARD';
    const REQUEST_TYPE_NEW_COMPANY         = 'NEW_COMPANY';
    const REQUEST_TYPE_JOIN_COMPANY        = 'JOIN_COMPANY';
    const REQUEST_TYPE_COMPANY_INVITE      = 'COMPANY_INVITE';
    const REQUEST_TYPE_TEACH_LESSON_INVITE = 'TEACH_LESSON_INVITE';
    const REQUEST_TYPE_NOTIFICATION        = 'NOTIFICATION';
    const REQUEST_TYPE_NEW_REGISTRATION    = 'NEW_REGISTRATION';
    const REQUEST_TYPE_ONE_ON_ONE_MEETING  = 'ONE_ON_ONE_MEETING';

    const REQUEST_STATUS_PENDING      = 'PENDING';
    const REQUEST_STATUS_APPROVED     = 'APPROVED';
    const REQUEST_STATUS_DENIED       = 'DENIED';
    const REQUEST_STATUS_ACTIVE       = 'ACTIVE';
    const REQUEST_STATUS_INACTIVE     = 'INACTIVE';
    const REQUEST_STATUS_UNREGISTERED = 'UNREGISTERED';

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
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
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
     * @ORM\OneToMany(targetEntity=RequestAction::class, mappedBy="request", cascade={"remove"})
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

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $statusLabel;

    /**
     * @ORM\OneToOne(targetEntity=Experience::class, mappedBy="request")
     */
    private $experience;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasNewNotification = false;

    /**
     * @ORM\ManyToOne(targetEntity=Request::class, inversedBy="requests")
     */
    private $parentRequest;

    /**
     * @ORM\OneToMany(targetEntity=Request::class, mappedBy="parentRequest")
     */
    private $requests;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $requestActionAt;

    public function __construct()
    {
        $this->requestPossibleApprovers = new ArrayCollection();
        $this->volunteerRoles           = new ArrayCollection();
        $this->primaryIndustries        = new ArrayCollection();
        $this->shares                   = new ArrayCollection();
        $this->requestActions           = new ArrayCollection();
        $this->requests                 = new ArrayCollection();
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

    public function getAssociatedRequestPossibleApproverForUser(User $user)
    {

        $requestPossibleApprovers = array_filter($this->requestPossibleApprovers->toArray(), function (RequestPossibleApprovers $requestPossibleApprovers
        ) use ($user) {
            if (!$requestPossibleApprovers->getPossibleApprover()) {
                return false;
            }

            if ($user->getId() !== $requestPossibleApprovers->getPossibleApprover()->getId()) {
                return false;
            }

            return true;
        });

        $requestPossibleApprovers = array_values($requestPossibleApprovers);

        if (!empty($requestPossibleApprovers)) {
            return $requestPossibleApprovers[0];
        }

        return null;
    }

    public function getAssociatedRequestPossibleApproversNotEqualToUser(User $user, $oneOrNull = false)
    {

        $requestPossibleApprovers = array_filter($this->requestPossibleApprovers->toArray(), function (RequestPossibleApprovers $requestPossibleApprovers
        ) use ($user) {
            if (!$requestPossibleApprovers->getPossibleApprover()) {
                return false;
            }

            if ($user->getId() === $requestPossibleApprovers->getPossibleApprover()->getId()) {
                return false;
            }

            return true;
        });

        $requestPossibleApprovers = array_values($requestPossibleApprovers);

        if ($oneOrNull) {
            return $requestPossibleApprovers[0] ?? null;
        }

        return $requestPossibleApprovers ?? [];
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

    public function getButtonCssClass($action = null)
    {
        if (!$action) {
            $action = $this->status;
        }

        switch ($action) {
            case RequestAction::REQUEST_ACTION_NAME_APPROVE:
            case RequestAction::REQUEST_ACTION_NAME_MARK_AS_ACTIVE:
                return 'uk-button-primary';
                break;
            case RequestAction::REQUEST_ACTION_NAME_DENY:
            case RequestAction::REQUEST_ACTION_NAME_MARK_AS_INACTIVE:
            case RequestAction::REQUEST_ACTION_NAME_REMOVE_FROM_COMPANY:
            case RequestAction::REQUEST_ACTION_NAME_LEAVE_COMPANY:
            case RequestAction::REQUEST_ACTION_NAME_UNREGISTER:
                return 'uk-button-danger';
                break;
            default:
                return 'uk-button-default';
                break;
        }
    }

    public function getStatusCssClass()
    {
        switch ($this->status) {
            case self::REQUEST_STATUS_PENDING:
                return 'uk-label-warning';
                break;
            case self::REQUEST_STATUS_APPROVED:
            case self::REQUEST_STATUS_ACTIVE:
                return 'uk-label-success';
                break;
            case self::REQUEST_STATUS_DENIED:
            case self::REQUEST_STATUS_INACTIVE:
            case self::REQUEST_STATUS_UNREGISTERED:
                return 'uk-label-danger';
                break;
            default:
                return 'uk-label';
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

        if ($action === RequestAction::REQUEST_ACTION_NAME_MARK_AS_PENDING) {
            return 'Pending';
        }

        if ($action === RequestAction::REQUEST_ACTION_NAME_HIDE) {
            return 'Hide';
        }

        if ($action === RequestAction::REQUEST_ACTION_NAME_REMOVE_FROM_COMPANY) {
            return 'Remove From Company';
        }

        if ($action === RequestAction::REQUEST_ACTION_NAME_LEAVE_COMPANY) {
            return 'Leave Company';
        }

        if ($action === RequestAction::REQUEST_ACTION_NAME_SUGGEST_NEW_DATES) {
            return 'Suggest New Dates';
        }

        if ($action === RequestAction::REQUEST_ACTION_NAME_SUGGEST_MEETING_DATES) {
            return 'Suggest Meeting Dates';
        }

        if ($action === RequestAction::REQUEST_ACTION_NAME_SEND_MESSAGE) {
            return 'Send Message';
        }

        if ($action === RequestAction::REQUEST_ACTION_NAME_MARK_AS_ACTIVE) {
            return 'Active';
        }

        if ($action === RequestAction::REQUEST_ACTION_NAME_MARK_AS_INACTIVE) {
            return 'Inactive';
        }

        if ($action === RequestAction::REQUEST_ACTION_NAME_VIEW_REGISTRATION_LIST) {
            return 'Registrations';
        }

        if ($action === RequestAction::REQUEST_ACTION_NAME_UNREGISTER) {
            return 'Unregister';
        }

        if ($action === RequestAction::REQUEST_ACTION_NAME_REGISTER) {
            return 'Register';
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusLabel(): ?string
    {
        return $this->statusLabel;
    }

    public function setStatusLabel(?string $statusLabel): self
    {
        $this->statusLabel = $statusLabel;

        return $this;
    }

    public function getExperience(): ?Experience
    {
        return $this->experience;
    }

    public function setExperience(?Experience $experience): self
    {
        // unset the owning side of the relation if necessary
        if ($experience === null && $this->experience !== null) {
            $this->experience->setRequest(null);
        }

        // set the owning side of the relation if necessary
        if ($experience !== null && $experience->getRequest() !== $this) {
            $experience->setRequest($this);
        }

        $this->experience = $experience;

        return $this;
    }

    public function getIsFromProfessional()
    {
        return $this->getCreatedBy() instanceof ProfessionalUser;
    }

    public function getIsFromEducator()
    {
        return $this->getCreatedBy() instanceof EducatorUser;
    }

    public function getHasNewNotification(): ?bool
    {
        return $this->hasNewNotification;
    }

    public function setHasNewNotification(?bool $hasNewNotification): self
    {
        $this->hasNewNotification = $hasNewNotification;

        return $this;
    }

    public function getTimeElapsedSinceHasNotification()
    {
        if ($this->requestActionAt) {
            return $this->requestActionAt->format("m/d/Y h:i A");
        }

        return $this->createdAt->format("m/d/Y h:i A");
    }

    public function getParentRequest(): ?self
    {
        return $this->parentRequest;
    }

    public function setParentRequest(?self $parentRequest): self
    {
        $this->parentRequest = $parentRequest;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getRequests(): Collection
    {
        return $this->requests;
    }

    public function addRequest(self $request): self
    {
        if (!$this->requests->contains($request)) {
            $this->requests[] = $request;
            $request->setParentRequest($this);
        }

        return $this;
    }

    public function removeRequest(self $request): self
    {
        if ($this->requests->removeElement($request)) {
            // set the owning side to null (unless already changed)
            if ($request->getParentRequest() === $this) {
                $request->setParentRequest(null);
            }
        }

        return $this;
    }

    public function getRequestActionAt(): ?\DateTimeInterface
    {
        return $this->requestActionAt;
    }

    public function setRequestActionAt(?\DateTimeInterface $requestActionAt): self
    {
        $this->requestActionAt = $requestActionAt;

        return $this;
    }
}
