<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;
use App\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

/**
 * @ORM\HasLifecycleCallbacks()
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email", groups={"CREATE", "EDIT"})
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username", groups={"CREATE", "EDIT"})
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"professionalUser" = "ProfessionalUser", "educatorUser" = "EducatorUser", "studentUser" = "StudentUser", "adminUser" = "AdminUser", "stateCoordinator" = "StateCoordinator", "regionalCoordinator" = "RegionalCoordinator", "schoolAdministrator" = "SchoolAdministrator", "siteAdminUser" = "SiteAdminUser", "systemUser" = "SystemUser"})
 *
 */
abstract class User implements UserInterface
{
    use TimestampableEntity;

    const ROLE_USER                      = 'ROLE_USER';
    const ROLE_DASHBOARD_USER            = 'ROLE_DASHBOARD_USER';
    const ROLE_PROFESSIONAL_USER         = 'ROLE_PROFESSIONAL_USER';
    const ROLE_EDUCATOR_USER             = 'ROLE_EDUCATOR_USER';
    const ROLE_STUDENT_USER              = 'ROLE_STUDENT_USER';
    const ROLE_ADMIN_USER                = 'ROLE_ADMIN_USER';
    const ROLE_STATE_COORDINATOR_USER    = 'ROLE_STATE_COORDINATOR_USER';
    const ROLE_REGIONAL_COORDINATOR_USER = 'ROLE_REGIONAL_COORDINATOR_USER';
    const ROLE_SCHOOL_ADMINISTRATOR_USER = 'ROLE_SCHOOL_ADMINISTRATOR_USER';
    const ROLE_SITE_ADMIN_USER           = 'ROLE_SITE_ADMIN_USER';
    const ROLE_COMPANY_ADMINISTRATOR     = 'ROLE_COMPANY_ADMINISTRATOR';

    public static $reportPermissionUserRoleChoices = [
        'Professional User' => self::ROLE_PROFESSIONAL_USER,
        'Educator User' => self::ROLE_EDUCATOR_USER,
        'Regional Coordinator' => self::ROLE_REGIONAL_COORDINATOR_USER,
        'School Administrator' => self::ROLE_SCHOOL_ADMINISTRATOR_USER,
        'Company Administrator' => self::ROLE_COMPANY_ADMINISTRATOR,
    ];

    /**
     * @Groups({"ALL_USER_DATA", "PROFESSIONAL_USER_DATA",  "EXPERIENCE_DATA", "ALL_USER_DATA", "REQUEST", "CHAT", "MESSAGE", "EXPERIENCE_DATA", "EDUCATOR_USER_DATA"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @Groups({"ALL_USER_DATA", "REQUEST", "CHAT", "MESSAGE", "EDUCATOR_USER_DATA"})
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email",
     *     groups={"CREATE", "EDIT", "EDUCATOR_USER", "STUDENT_USER", "STATE_COORDINATOR_EDIT", "PROFESSIONAL_PROFILE_ACCOUNT"}
     * )
     * @Assert\NotBlank(message="Don't forget an email", groups={"CREATE", "EDIT", "INCOMPLETE_USER", "STATE_COORDINATOR_EDIT", "REGIONAL_COORDINATOR_EDIT", "EDUCATOR_USER", "PROFESSIONAL_PROFILE_ACCOUNT", "EDUCATOR_PROFILE_ACCOUNT"})
     * @ORM\Column(type="string", length=180, unique=true, nullable=true)
     */
    protected $email;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA", "ALL_USER_DATA", "REQUEST", "STUDENT_USER", "EDUCATOR_USER"})
     * @Assert\NotBlank(message="Don't forget a username for your user", groups={"EDUCATOR_USER", "STUDENT_USER", "EDUCATOR_PROFILE_ACCOUNT"})
     *
     * @CustomAssert\UsernameInvalid(groups={"EDUCATOR_USER", "STUDENT_USER", "EDUCATOR_PROFILE_ACCOUNT"})
     *
     * @ORM\Column(type="string", length=180, unique=true, nullable=true)
     */
    protected $username;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    protected $password;

    /**
     * @RollerworksPassword\PasswordRequirements(requireLetters=true, requireNumbers=true, requireCaseDiff=true, requireSpecialCharacter= true, minLength = "6", groups={"CREATE", "EDIT", "STUDENT_USER", "EDUCATOR_USER", "STATE_COORDINATOR_EDIT", "REGIONAL_COORDINATOR_EDIT"})
     * @Assert\NotBlank(message="Don't forget a password for your user", groups={"CREATE"})
     */
    protected $plainPassword;

    /**
     * InvitationCode
     *
     * The invitation code associated with a user.
     *
     * @var string
     *
     * @ORM\Column(name="invitation_code", type="string", length=255, nullable=true)
     */
    protected $invitationCode;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA",  "EXPERIENCE_DATA", "ALL_USER_DATA", "REQUEST", "STUDENT_USER", "EDUCATOR_USER", "CHAT", "MESSAGE", "EXPERIENCE_DATA", "EDUCATOR_USER_DATA"})
     * @Assert\NotBlank(message="Don't forget a first name", groups={"CREATE", "EDIT", "INCOMPLETE_USER", "EDUCATOR_USER", "STUDENT_USER", "STATE_COORDINATOR_EDIT", "REGIONAL_COORDINATOR_EDIT", "PROFESSIONAL_PROFILE_PERSONAL", "EDUCATOR_PROFILE_PERSONAL"})
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA",  "EXPERIENCE_DATA", "ALL_USER_DATA", "REQUEST", "STUDENT_USER", "EDUCATOR_USER", "CHAT", "MESSAGE", "EXPERIENCE_DATA", "EDUCATOR_USER_DATA"})
     * @Assert\NotBlank(message="Don't forget a last name", groups={"CREATE", "EDIT", "INCOMPLETE_USER", "EDUCATOR_USER", "STUDENT_USER", "STATE_COORDINATOR_EDIT", "REGIONAL_COORDINATOR_EDIT", "PROFESSIONAL_PROFILE_PERSONAL", "EDUCATOR_PROFILE_PERSONAL"})
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $passwordResetToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $passwordResetTokenTimestamp;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA", "ALL_USER_DATA"})
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * @Groups({"PROFESSIONAL_USER_DATA", "ALL_USER_DATA"})
     * @ORM\Column(type="boolean")
     */
    protected $deleted = 0;

    /**
     * @Groups({"ALL_USER_DATA"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $agreedToTermsAt;

    /**
     * @Groups({"ALL_USER_DATA"})
     * @ORM\OneToMany(targetEntity="App\Entity\LessonFavorite", mappedBy="user", orphanRemoval=true)
     */
    protected $lessonFavorites;

    /**
     * @Groups({"ALL_USER_DATA"})
     * @ORM\OneToMany(targetEntity="App\Entity\Lesson", mappedBy="user")
     */
    protected $lessons;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Request", mappedBy="created_by", orphanRemoval=true)
     */
    protected $requests;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Request", mappedBy="needsApprovalBy", orphanRemoval=true)
     */
    protected $requestsThatNeedMyApproval;

    /**
     * @Groups({"ALL_USER_DATA"})
     * @ORM\OneToMany(targetEntity="App\Entity\CompanyFavorite", mappedBy="user", orphanRemoval=true)
     */
    protected $companyFavorites;

    /**
     * @Groups({"ALL_USER_DATA"})
     * @ORM\OneToMany(targetEntity="App\Entity\LessonTeachable", mappedBy="user", orphanRemoval=true)
     */
    protected $lessonTeachables;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $photo;

    /**
     * @Groups({"ALL_USER_DATA"})
     * @ORM\Column(type="boolean")
     */
    protected $activated = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $activationCode;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA", "ALL_USER_DATA"})
     * @ORM\Column(type="boolean")
     */
    protected $isEmailHiddenFromProfile = false;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA", "ALL_USER_DATA"})
     * @ORM\Column(type="boolean")
     */
    protected $isPhoneHiddenFromProfile = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ChatMessage", mappedBy="sentTo")
     */
    protected $chatMessages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Registration", mappedBy="user", orphanRemoval=true)
     */
    protected $registrations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Feedback", mappedBy="user")
     */
    protected $feedback;

    /**
     * This is just a temporary token that is used in various parts of the app to later authenticate the user.
     * One of the spots it's used now is in the security-router to redirect the user to proper base url for login
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $temporarySecurityToken;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserRegisterForSchoolExperienceRequest", mappedBy="user", orphanRemoval=true)
     */
    protected $userRegisterForSchoolExperienceRequests;

    /**
     * @Groups({"STUDENT_USER", "EDUCATOR_USER"})
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $tempPassword;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RequestPossibleApprovers", mappedBy="possibleApprover", orphanRemoval=true)
     */
    protected $requestPossibleApprovers;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SchoolExperience", mappedBy="schoolContact", orphanRemoval=true)
     */
    protected $schoolExperiences;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\VideoFavorite", mappedBy="user")
     */
    protected $videoFavorites;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $notificationPreferenceMask;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $profileCompleted = false;

    /**
     * @ORM\OneToMany(targetEntity=CompanyView::class, mappedBy="user", orphanRemoval=true)
     */
    private $companyViews;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $dashboardOrder = [];

    /**
     * @ORM\OneToMany(targetEntity=Share::class, mappedBy="sentFrom")
     */
    protected $sentFromShares;

    /**
     * @ORM\OneToMany(targetEntity=Share::class, mappedBy="sentTo")
     */
    protected $sentToShares;

    /**
     * @Groups({"ALL_USER_DATA"})
     *
     * @var bool
     */
    protected $loggedInUserShared = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $loginCount = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastLoginDate;

    /**
     * @ORM\ManyToMany(targetEntity=ReportShare::class, mappedBy="users")
     */
    private $reportShares;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @var array
     */
    private $splashPagesShown = [];

    /**
     * @ORM\OneToMany(targetEntity=UserMeta::class, mappedBy="user")
     */
    private $userMetas;

    /**
     * @ORM\OneToMany(targetEntity=RequestAction::class, mappedBy="user")
     */
    private $requestActions;


    /**
     * @ORM\PrePersist
     */
    public function setProfileStatusEvent(): void
    {
        $this->profileCompleted = $this->isProfileCompleted();
    }

    public function __construct()
    {
        $this->lessonFavorites                         = new ArrayCollection();
        $this->lessons                                 = new ArrayCollection();
        $this->requests                                = new ArrayCollection();
        $this->requestsThatNeedMyApproval              = new ArrayCollection();
        $this->companyFavorites                        = new ArrayCollection();
        $this->lessonTeachables                        = new ArrayCollection();
        $this->chatMessages                            = new ArrayCollection();
        $this->registrations                           = new ArrayCollection();
        $this->feedback                                = new ArrayCollection();
        $this->userRegisterForSchoolExperienceRequests = new ArrayCollection();
        $this->requestPossibleApprovers                = new ArrayCollection();
        $this->videoFavorites                          = new ArrayCollection();
        $this->companyViews                            = new ArrayCollection();
        $this->reportShares                            = new ArrayCollection();
        $this->userMetas = new ArrayCollection();
        $this->requestActions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @Groups({"PROFESSIONAL_USER_DATA", "ALL_USER_DATA", "CHAT", "MESSAGE"})
     *
     * @return string|null
     */
    public function getFullName(): ?string
    {
        return $this->firstName . " " . $this->lastName;
    }


    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    /**
     * @param string $passwordResetToken
     *
     * @return User
     * @throws \Exception
     */
    public function setPasswordResetToken($passwordResetToken = null)
    {
        if (empty($passwordResetToken)) {
            $passwordResetToken = bin2hex(random_bytes(32));
        }

        if (strlen($passwordResetToken) !== 64) {
            throw new \InvalidArgumentException('Reset token must be 64 characters in length');
        }

        $this->passwordResetToken = $passwordResetToken;

        $this->setPasswordResetTokenTimestamp();

        return $this;
    }

    public function getPasswordResetTokenTimestamp(): ?\DateTimeInterface
    {
        return $this->passwordResetTokenTimestamp;
    }

    /**
     * @param DateTime $passwordResetTokenTimestamp
     *
     * @return User
     * @throws \Exception
     */
    public function setPasswordResetTokenTimestamp(DateTime $passwordResetTokenTimestamp = null)
    {
        if (empty($passwordResetTokenTimestamp)) {
            $passwordResetTokenTimestamp = new DateTime();
        }

        $this->passwordResetTokenTimestamp = $passwordResetTokenTimestamp;

        return $this;
    }

    /**
     * Clear out password reset token related fields
     *
     * @return User
     */
    public function clearPasswordResetToken()
    {
        $this->passwordResetToken          = null;
        $this->passwordResetTokenTimestamp = null;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array $roles
     *
     * @return $this
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }


    /**
     * @param string $role
     *
     * @return $this
     */
    public function addRole($role)
    {

        if(!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function friendlyRoleName()
    {
        if ($this->isProfessional()) {
            return 'professional';
        } elseif ($this->isEducator()) {
            return 'educator';
        } elseif ($this->isStudent()) {
            return 'student';
        } elseif ($this->isAdmin()) {
            return 'admin';
        } elseif ($this->isStateCoordinator()) {
            return 'state coordinator';
        } elseif ($this->isRegionalCoordinator()) {
            return 'regional coordinator';
        } elseif ($this->isSiteAdmin()) {
            return 'side admin';
        } elseif ($this->isSchoolAdministrator()) {
            return 'school administrator';
        } else {
            return 'user';
        }
    }

    /**
     * @Groups({"ALL_USER_DATA", "CHAT", "MESSAGE"})
     * @return bool
     */
    public function isProfessional()
    {
        $roles = $this->getRoles();

        if (in_array(self::ROLE_PROFESSIONAL_USER, $roles)) {
            return true;
        }

        return false;
    }

    /**
     * @Groups({"ALL_USER_DATA", "CHAT", "MESSAGE"})
     * @return bool
     */
    public function isEducator()
    {
        $roles = $this->getRoles();

        if (in_array(self::ROLE_EDUCATOR_USER, $roles)) {
            return true;
        }

        return false;
    }

    /**
     * @Groups({"ALL_USER_DATA", "CHAT", "MESSAGE"})
     * @return bool
     */
    public function isStudent()
    {
        $roles = $this->getRoles();

        if (in_array(self::ROLE_STUDENT_USER, $roles)) {
            return true;
        }

        return false;
    }

    /**
     * @Groups({"ALL_USER_DATA"})
     * @return bool
     */
    public function isAdmin()
    {
        $roles = $this->getRoles();

        if (in_array(self::ROLE_ADMIN_USER, $roles)) {
            return true;
        }

        return false;
    }

    /**
     * @Groups({"ALL_USER_DATA"})
     * @return bool
     */
    public function isStateCoordinator()
    {
        $roles = $this->getRoles();

        if (in_array(self::ROLE_STATE_COORDINATOR_USER, $roles)) {
            return true;
        }

        return false;
    }

    /**
     * @Groups({"ALL_USER_DATA"})
     * @return bool
     */
    public function isRegionalCoordinator()
    {
        $roles = $this->getRoles();

        if (in_array(self::ROLE_REGIONAL_COORDINATOR_USER, $roles)) {
            return true;
        }

        return false;
    }

    /**
     * @Groups({"ALL_USER_DATA"})
     * @return bool
     */
    public function isSiteAdmin()
    {
        $roles = $this->getRoles();

        if (in_array(self::ROLE_SITE_ADMIN_USER, $roles)) {
            return true;
        }

        return false;
    }

    /**
     * @Groups({"ALL_USER_DATA"})
     * @return bool
     */
    public function isSchoolAdministrator()
    {
        $roles = $this->getRoles();

        if (in_array(self::ROLE_SCHOOL_ADMINISTRATOR_USER, $roles)) {
            return true;
        }

        return false;
    }

    /**
     * @Groups({"ALL_USER_DATA"})
     * @return bool
     */
    public function isCompanyAdministrator()
    {
        $roles = $this->getRoles();

        if (in_array(self::ROLE_COMPANY_ADMINISTRATOR, $roles)) {
            return true;
        }

        return false;
    }

    public function isCompanyOwner() {

        if(!$this instanceof ProfessionalUser) {
            return false;
        }

        if(!$this->getCompany()) {
            return false;
        }

        if(!$this->getCompany()->getOwner()) {
            return false;
        }

        if($this->getCompany()->getOwner()->getId() !== $this->getId()) {
            return false;
        }

        return true;
    }

    public function canBeInvitedToCompany($company) {

        if(!$this instanceof ProfessionalUser) {
            return false;
        }

        if($this->isCompanyOwner()) {
            return false;
        }

        if($this->getCompany()) {
            return false;
        }

        return true;
    }

    public function setupAsAdmin()
    {

        if (!in_array(self::ROLE_ADMIN_USER, $this->roles)) {
            $this->roles[] = self::ROLE_ADMIN_USER;
        }
    }

    public function setupAsProfessional()
    {

        if (!in_array(self::ROLE_PROFESSIONAL_USER, $this->roles)) {
            $this->roles[] = self::ROLE_PROFESSIONAL_USER;
        }
    }

    public function setupAsStateCoordinator()
    {

        if (!in_array(self::ROLE_STATE_COORDINATOR_USER, $this->roles)) {
            $this->roles[] = self::ROLE_STATE_COORDINATOR_USER;
        }
    }

    public function setupAsRegionalCoordinator()
    {

        if (!in_array(self::ROLE_REGIONAL_COORDINATOR_USER, $this->roles)) {
            $this->roles[] = self::ROLE_REGIONAL_COORDINATOR_USER;
        }
    }

    public function setupAsSchoolAdministrator()
    {

        if (!in_array(self::ROLE_SCHOOL_ADMINISTRATOR_USER, $this->roles)) {
            $this->roles[] = self::ROLE_SCHOOL_ADMINISTRATOR_USER;
        }
    }

    public function setupAsEducator()
    {

        if (!in_array(self::ROLE_EDUCATOR_USER, $this->roles)) {
            $this->roles[] = self::ROLE_EDUCATOR_USER;
        }
    }

    public function setupAsStudent()
    {

        if (!in_array(self::ROLE_STUDENT_USER, $this->roles)) {
            $this->roles[] = self::ROLE_STUDENT_USER;
        }
    }

    public function setupAsSiteAdminUser()
    {

        if (!in_array(self::ROLE_SITE_ADMIN_USER, $this->roles)) {
            $this->roles[] = self::ROLE_SITE_ADMIN_USER;
        }
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getAgreedToTermsAt()
    {
        return $this->agreedToTermsAt;
    }

    public function agreeToTerms()
    {
        $this->agreedToTermsAt = new \DateTime();
    }

    public function setAgreedToTermsAt($agreedToTermsAt)
    {
        $this->agreedToTermsAt = $agreedToTermsAt;
    }

    /**
     * @return Collection|LessonFavorite[]
     */
    public function getLessonFavorites(): Collection
    {
        return $this->lessonFavorites;
    }

    public function addLessonFavorite(LessonFavorite $lessonFavorite): self
    {
        if (!$this->lessonFavorites->contains($lessonFavorite)) {
            $this->lessonFavorites[] = $lessonFavorite;
            $lessonFavorite->setUser($this);
        }

        return $this;
    }

    public function removeLessonFavorite(LessonFavorite $lessonFavorite): self
    {
        if ($this->lessonFavorites->contains($lessonFavorite)) {
            $this->lessonFavorites->removeElement($lessonFavorite);
            // set the owning side to null (unless already changed)
            if ($lessonFavorite->getUser() === $this) {
                $lessonFavorite->setUser(null);
            }
        }

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
            $lesson->setUser($this);
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): self
    {
        if ($this->lessons->contains($lesson)) {
            $this->lessons->removeElement($lesson);
            // set the owning side to null (unless already changed)
            if ($lesson->getUser() === $this) {
                $lesson->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Request[]
     */
    public function getRequests(): Collection
    {
        return $this->requests;
    }

    public function addRequest(Request $request): self
    {
        if (!$this->requests->contains($request)) {
            $this->requests[] = $request;
            $request->setCreatedBy($this);
        }

        return $this;
    }

    public function removeRequest(Request $request): self
    {
        if ($this->requests->contains($request)) {
            $this->requests->removeElement($request);
            // set the owning side to null (unless already changed)
            if ($request->getCreatedBy() === $this) {
                $request->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Request[]
     */
    public function getRequestsThatNeedMyApproval(): Collection
    {
        return $this->requestsThatNeedMyApproval;
    }

    public function addRequestsThatNeedMyApproval(Request $request): self
    {
        if (!$this->requestsThatNeedMyApproval->contains($request)) {
            $this->requestsThatNeedMyApproval[] = $request;
            $request->setNeedsApprovalBy($this);
        }

        return $this;
    }

    public function removeRequestsThatNeedMyApproval(Request $request): self
    {
        if ($this->requestsThatNeedMyApproval->contains($request)) {
            $this->requestsThatNeedMyApproval->removeElement($request);
            // set the owning side to null (unless already changed)
            if ($request->getNeedsApprovalBy() === $this) {
                $request->setNeedsApprovalBy(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return Collection|CompanyFavorite[]
     */
    public function getCompanyFavorites(): Collection
    {
        return $this->companyFavorites;
    }

    public function addCompanyFavorite(CompanyFavorite $companyFavorite): self
    {
        if (!$this->companyFavorites->contains($companyFavorite)) {
            $this->companyFavorites[] = $companyFavorite;
            $companyFavorite->setUser($this);
        }

        return $this;
    }

    public function removeCompanyFavorite(CompanyFavorite $companyFavorite): self
    {
        if ($this->companyFavorites->contains($companyFavorite)) {
            $this->companyFavorites->removeElement($companyFavorite);
            // set the owning side to null (unless already changed)
            if ($companyFavorite->getUser() === $this) {
                $companyFavorite->setUser(null);
            }
        }

        return $this;
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
            $lessonTeachable->setUser($this);
        }

        return $this;
    }

    public function removeLessonTeachable(LessonTeachable $lessonTeachable): self
    {
        if ($this->lessonTeachables->contains($lessonTeachable)) {
            $this->lessonTeachables->removeElement($lessonTeachable);
            // set the owning side to null (unless already changed)
            if ($lessonTeachable->getUser() === $this) {
                $lessonTeachable->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo): void
    {
        $this->photo = $photo;
    }

    public function getPhotoPath()
    {
        return UploaderHelper::PROFILE_PHOTO . '/' . $this->getPhoto();
    }

    /**
     * @Groups({"PROFESSIONAL_USER_DATA", "ALL_USER_DATA", "CHAT"})
     */
    public function getPhotoImageURL()
    {
        if ($this->getPhoto()) {
            return '/media/cache/squared_thumbnail_small/uploads/' . $this->getPhotoPath();
        }

        return '';
    }

    public function getActivated()
    {
        return $this->activated;
    }

    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * @return string
     */
    public function getInvitationCode()
    {
        return $this->invitationCode;
    }

    /**
     * @param string $invitationCode
     */
    public function setInvitationCode($invitationCode)
    {
        $this->invitationCode = $invitationCode;
    }

    public function initializeNewUser($activationCode = true, $invitationCode = false)
    {
        if ($activationCode) {
            $activationCode = bin2hex(random_bytes(32));
            $this->setActivationCode($activationCode);
        }

        if ($invitationCode) {
            $invitationCode = bin2hex(random_bytes(32));
            $this->setInvitationCode($invitationCode);
        }

        $roles         = $this->getRoles();
        $this->roles[] = self::ROLE_DASHBOARD_USER;
    }


    public function getActivationCode()
    {
        return $this->activationCode;
    }

    public function setActivationCode($activationCode)
    {
        $this->activationCode = $activationCode;

        return $this;
    }

    public function canEditEvent(Experience $experience)
    {

        if ($this->isAdmin()) {
            return true;
        }

        if ($experience instanceof CompanyExperience) {
            if ($experience->getCompany()->isUserOwner($this)) {
                return true;
            }
        }

        if ($experience instanceof SchoolExperience) {
            if ($experience->getSchool()->isUserSchoolAdministrator($this)) {
                return true;
            }
        }

        return false;
    }

    public function getIsEmailHiddenFromProfile(): ?bool
    {
        return $this->isEmailHiddenFromProfile;
    }

    public function setIsEmailHiddenFromProfile(?bool $isEmailHiddenFromProfile): self
    {
        $this->isEmailHiddenFromProfile = $isEmailHiddenFromProfile;

        return $this;
    }

    public function getIsPhoneHiddenFromProfile(): ?bool
    {
        return $this->isPhoneHiddenFromProfile;
    }

    public function setIsPhoneHiddenFromProfile(?bool $isPhoneHiddenFromProfile): self
    {
        $this->isPhoneHiddenFromProfile = $isPhoneHiddenFromProfile;

        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Collection|ChatMessage[]
     */
    public function getChatMessages(): Collection
    {
        return $this->chatMessages;
    }

    public function addChatMessage(ChatMessage $chatMessage): self
    {
        if (!$this->chatMessages->contains($chatMessage)) {
            $this->chatMessages[] = $chatMessage;
            $chatMessage->setSentTo($this);
        }

        return $this;
    }

    public function removeChatMessage(ChatMessage $chatMessage): self
    {
        if ($this->chatMessages->contains($chatMessage)) {
            $this->chatMessages->removeElement($chatMessage);
            // set the owning side to null (unless already changed)
            if ($chatMessage->getSentTo() === $this) {
                $chatMessage->setSentTo(null);
            }
        }

        return $this;
    }

    public function canLoginAsAnotherUser()
    {
        // return $this->isAdmin() || $this->isSiteAdmin();
        return $this->isAdmin() || $this->isSiteAdmin();
    }

    /**
     * @return Collection|Registration[]
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(Registration $registration): self
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations[] = $registration;
            $registration->setUser($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): self
    {
        if ($this->registrations->contains($registration)) {
            $this->registrations->removeElement($registration);
            // set the owning side to null (unless already changed)
            if ($registration->getUser() === $this) {
                $registration->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Feedback[]
     */
    public function getFeedback(): Collection
    {
        return $this->feedback;
    }

    public function addFeedback(Feedback $feedback): self
    {
        if (!$this->feedback->contains($feedback)) {
            $this->feedback[] = $feedback;
            $feedback->setUser($this);
        }

        return $this;
    }

    public function removeFeedback(Feedback $feedback): self
    {
        if ($this->feedback->contains($feedback)) {
            $this->feedback->removeElement($feedback);
            // set the owning side to null (unless already changed)
            if ($feedback->getUser() === $this) {
                $feedback->setUser(null);
            }
        }

        return $this;
    }

    public function getTemporarySecurityToken(): ?string
    {
        return $this->temporarySecurityToken;
    }

    public function setTemporarySecurityToken(?string $temporarySecurityToken): self
    {
        $this->temporarySecurityToken = $temporarySecurityToken;

        return $this;
    }

    public function initializeTemporarySecurityToken()
    {
        $this->temporarySecurityToken = bin2hex(random_bytes(32));
    }

    /**
     * @return Collection|UserRegisterForSchoolExperienceRequest[]
     */
    public function getUserRegisterForSchoolExperienceRequests(): Collection
    {
        return $this->userRegisterForSchoolExperienceRequests;
    }

    public function addUserRegisterForSchoolExperienceRequest(
        UserRegisterForSchoolExperienceRequest $userRegisterForSchoolExperienceRequest
    ): self {
        if (!$this->userRegisterForSchoolExperienceRequests->contains($userRegisterForSchoolExperienceRequest)) {
            $this->userRegisterForSchoolExperienceRequests[] = $userRegisterForSchoolExperienceRequest;
            $userRegisterForSchoolExperienceRequest->setUser($this);
        }

        return $this;
    }

    public function removeUserRegisterForSchoolExperienceRequest(
        UserRegisterForSchoolExperienceRequest $userRegisterForSchoolExperienceRequest
    ): self {
        if ($this->userRegisterForSchoolExperienceRequests->contains($userRegisterForSchoolExperienceRequest)) {
            $this->userRegisterForSchoolExperienceRequests->removeElement($userRegisterForSchoolExperienceRequest);
            // set the owning side to null (unless already changed)
            if ($userRegisterForSchoolExperienceRequest->getUser() === $this) {
                $userRegisterForSchoolExperienceRequest->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTempPassword()
    {
        return $this->tempPassword;
    }

    /**
     * @param mixed $tempPassword
     */
    public function setTempPassword($tempPassword): void
    {
        $this->tempPassword = $tempPassword;
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
            $requestPossibleApprover->setPossibleApprover($this);
        }

        return $this;
    }

    public function removeRequestPossibleApprover(RequestPossibleApprovers $requestPossibleApprover): self
    {
        if ($this->requestPossibleApprovers->contains($requestPossibleApprover)) {
            $this->requestPossibleApprovers->removeElement($requestPossibleApprover);
            // set the owning side to null (unless already changed)
            if ($requestPossibleApprover->getPossibleApprover() === $this) {
                $requestPossibleApprover->setPossibleApprover(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|VideoFavorite[]
     */
    public function getVideoFavorites(): Collection
    {
        return $this->videoFavorites;
    }

    public function addVideoFavorite(VideoFavorite $videoFavorite): self
    {
        if (!$this->videoFavorites->contains($videoFavorite)) {
            $this->videoFavorites[] = $videoFavorite;
            $videoFavorite->setUser($this);
        }

        return $this;
    }

    public function removeVideoFavorite(VideoFavorite $videoFavorite): self
    {
        if ($this->videoFavorites->contains($videoFavorite)) {
            $this->videoFavorites->removeElement($videoFavorite);
            // set the owning side to null (unless already changed)
            if ($videoFavorite->getUser() === $this) {
                $videoFavorite->setUser(null);
            }
        }

        return $this;
    }

    public function getClassName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function getNotificationPreferenceMask(): ?int
    {
        return $this->notificationPreferenceMask;
    }

    public function setNotificationPreferenceMask(?int $notificationPreferenceMask): self
    {
        $this->notificationPreferenceMask = $notificationPreferenceMask;

        return $this;
    }

    public function getProfileCompleted(): ?bool
    {
        return $this->profileCompleted;
    }

    public function setProfileCompleted(bool $profileCompleted): self
    {
        $this->profileCompleted = $profileCompleted;

        return $this;
    }

    public function isProfileCompleted()
    {

        $user = $this;

        if ($user->isProfessional()) {
            /** @var ProfessionalUser $user */
            if (!$user->getSchools()->count() || !$user->getRolesWillingToFulfill()->count() || !$user->getPersonalAddressSearch()) {
                return false;
            } else {
                return true;
            }
        }

        if ($user->isEducator()) {
            /** @var EducatorUser $user */

            if(!$user->getPrimaryIndustries()->count()) {
                return false;
            }

            if(!$user->getSecondaryIndustries()->count()) {
                return false;
            }

            if(!$user->getMyCourses()->count()) {
                return false;
            }

            return true;
        }

        if ($user->isStudent()) {

            /** @var StudentUser $user */
            if ($user->getSecondaryIndustries()->count() === 0) {
                return false;
            } else {
                return true;
            }
        }

        return true;

    }

    /**
     * @return Collection|CompanyView[]
     */
    public function getCompanyViews(): Collection
    {
        return $this->companyViews;
    }

    public function addCompanyView(CompanyView $companyView): self
    {
        if (!$this->companyViews->contains($companyView)) {
            $this->companyViews[] = $companyView;
            $companyView->setUserId($this);
        }

        return $this;
    }

    public function getDashboardOrder(): ?array
    {
        return $this->dashboardOrder;
    }

    public function setDashboardOrder(?array $dashboardOrder): self
    {
        $this->dashboardOrder = $dashboardOrder;

        return $this;
    }

    /**
     * @return Collection|Share[]
     */
    public function getSentToShares(): Collection
    {
        return $this->sentToShares;
    }

    public function addSentToShare(Share $sentToShare): self
    {
        if (!$this->sentToShares->contains($sentToShare)) {
            $this->sentToShares[] = $sentToShare;
            $sentToShare->setSentFrom($this);
        }

        return $this;
    }


    public function removeCompanyView(CompanyView $companyView): self
    {
        if ($this->companyViews->removeElement($companyView)) {
            // set the owning side to null (unless already changed)
            if ($companyView->getUserId() === $this) {
                $companyView->setUserId(null);
            }
        }

        return $this;
    }

    public function removeSentToShareShare(Share $sentToShare): self
    {
        if ($this->sentToShares->removeElement($sentToShare)) {
            // set the owning side to null (unless already changed)
            if ($sentToShare->getSentFrom() === $this) {
                $sentToShare->setSentFrom(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection|Share[]
     */
    public function getSentFromShares(): Collection
    {
        return $this->sentFromShares;
    }

    public function addSentFromShare(Share $sentFromShare): self
    {
        if (!$this->sentFromShares->contains($sentFromShare)) {
            $this->sentFromShares[] = $sentFromShare;
            $sentFromShare->setSentFrom($this);
        }

        return $this;
    }

    public function removeSentFromShareShare(Share $sentFromShare): self
    {
        if ($this->sentFromShares->removeElement($sentFromShare)) {
            // set the owning side to null (unless already changed)
            if ($sentFromShare->getSentFrom() === $this) {
                $sentFromShare->setSentFrom(null);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isLoggedInUserShared(): bool
    {
        return $this->loggedInUserShared;
    }

    /**
     * @param bool $loggedInUserShared
     */
    public function setLoggedInUserShared(bool $loggedInUserShared): void
    {
        $this->loggedInUserShared = $loggedInUserShared;
    }

    public function getLoginCount(): ?int
    {
        return $this->loginCount;
    }

    public function setLoginCount(?int $loginCount): self
    {
        $this->loginCount = $loginCount;

        return $this;
    }

    public function incrementLoginCount()
    {
        $this->loginCount++;
    }

    public function getLastLoginDate(): ?\DateTimeInterface
    {
        return $this->lastLoginDate;
    }

    public function setLastLoginDate(?\DateTimeInterface $lastLoginDate): self
    {
        $this->lastLoginDate = $lastLoginDate;

        return $this;
    }


    /**
     * @return Collection|ReportShare[]
     */
    public function getReportShares(): Collection
    {
        return $this->reportShares;
    }

    public function addReportShare(ReportShare $reportShare): self
    {
        if (!$this->reportShares->contains($reportShare)) {
            $this->reportShares[] = $reportShare;
            $reportShare->addUser($this);
        }

    }

    public function removeReportShare(ReportShare $reportShare): self
    {
        if ($this->reportShares->removeElement($reportShare)) {
            $reportShare->removeUser($this);

        }
    }

    public function getSplashPagesShown(): ?array
    {
        if(!$this->splashPagesShown) {
            return [];
        }

        return $this->splashPagesShown;
    }

    public function setSplashPagesShown(?array $splashPagesShown): self
    {
        $this->splashPagesShown = $splashPagesShown;

        return $this;
    }

    public function addSplashPageShown($splashPage): self
    {

        if(!in_array($splashPage, $this->getSplashPagesShown(), true)) {
            $this->splashPagesShown[] = $splashPage;
        }

        return $this;
    }

    public function splashPageShown($splashPage): bool
    {
        if(in_array($splashPage, $this->getSplashPagesShown(), true)) {
            return true;
        }

        return false;
    }

    public function getPotentialSplashPages() {
        return [];
    }

    /**
     * @return Collection|UserMeta[]
     */
    public function getUserMetas(): Collection
    {
        return $this->userMetas;
    }

    public function addUserMeta(UserMeta $userMeta): self
    {
        if (!$this->userMetas->contains($userMeta)) {
            $this->userMetas[] = $userMeta;
            $userMeta->setUser($this);
        }

        return $this;
    }

    public function removeUserMeta(UserMeta $userMeta): self
    {
        if ($this->userMetas->removeElement($userMeta)) {
            // set the owning side to null (unless already changed)
            if ($userMeta->getUser() === $this) {
                $userMeta->setUser(null);
            }
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
            $requestAction->setUser($this);
        }

        return $this;
    }

    public function removeRequestAction(RequestAction $requestAction): self
    {
        if ($this->requestActions->removeElement($requestAction)) {
            // set the owning side to null (unless already changed)
            if ($requestAction->getUser() === $this) {
                $requestAction->setUser(null);
            }
        }

        return $this;
    }

    public function setAvatarProfilePhotoIfNeeded($uploadsPath) {

        if (!$this->getPhoto() && $this->getFirstName() && $this->getLastName()) {

            $avatarUrl    = sprintf("https://ui-avatars.com/api/?name=%s+%s&background=random&size=128", $this->getFirstName(), $this->getLastName());
            $fileContents = file_get_contents($avatarUrl);

            $filesystem  = new Filesystem();
            $destination = $uploadsPath . '/' . UploaderHelper::PROFILE_PHOTO;
            $fileName = uniqid('', true) . '.png';
            $filesystem->dumpFile($destination . '/' . $fileName, $fileContents);
            $this->setPhoto($fileName);

            return true;
        }

        return false;
    }
}
