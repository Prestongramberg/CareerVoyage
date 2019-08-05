<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;
use App\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email", groups={"CREATE", "EDIT"})
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username", groups={"CREATE", "EDIT"})
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"professionalUser" = "ProfessionalUser", "educatorUser" = "EducatorUser", "studentUser" = "StudentUser", "adminUser" = "AdminUser", "schoolAdminUser" = "SchoolAdminUser", "schoolMultiSiteAdmin" = "SchoolMultiSiteAdmin", "schoolRegionalUser" = "SchoolRegionalUser", "schoolStateUser" = "SchoolStateUser", "stateCoordinator" = "StateCoordinator"})
 */
abstract class User implements UserInterface
{

    use TimestampableEntity;

    const ROLE_USER = 'ROLE_USER';
    const ROLE_DASHBOARD_USER = 'ROLE_DASHBOARD_USER';
    const ROLE_PROFESSIONAL_USER = 'ROLE_PROFESSIONAL_USER';
    const ROLE_EDUCATOR_USER = 'ROLE_EDUCATOR_USER ';
    const ROLE_STUDENT_USER = 'ROLE_STUDENT_USER';
    const ROLE_ADMIN_USER = 'ROLE_ADMIN_USER';
    const ROLE_STATE_COORDINATOR_USER = 'ROLE_STATE_COORDINATOR_USER';

    /**
     * @Groups({"PROFESSIONAL_USER_DATA",  "EXPERIENCE_DATA", "ALL_USER_DATA", "REQUEST"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;
    
    /**
     * @Groups({"PROFESSIONAL_USER_DATA",  "EXPERIENCE_DATA", "ALL_USER_DATA", "REQUEST"})
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     groups={"CREATE", "EDIT"}
     * )
     * @Assert\NotBlank(message="Don't forget an email for your user!", groups={"CREATE", "EDIT", "INCOMPLETE_USER"})
     * @ORM\Column(type="string", length=180, unique=true)
     */
    protected $email;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA", "ALL_USER_DATA", "REQUEST"})
     * @Assert\NotBlank(message="Don't forget a username for your user!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=180, unique=true, nullable=true)
     */
    protected $username;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    protected $password;

    /**
     * @RollerworksPassword\PasswordRequirements(requireLetters=true, requireNumbers=true, requireCaseDiff=true, requireSpecialCharacter= true, minLength = "6", groups={"CREATE", "EDIT"})
     * @Assert\NotBlank(message="Don't forget a password for your user!", groups={"CREATE"})
     */
    protected $plainPassword;

    /**
     * InvitationCode
     *
     * The invitation code associated with a user.
     *
     * @var string
     *
     * @ORM\Column(name="invitation_code", type="string", length=16, nullable=true)
     */
    protected $invitationCode;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA",  "EXPERIENCE_DATA", "ALL_USER_DATA", "REQUEST"})
     * @Assert\NotBlank(message="Don't forget a first name for your user!", groups={"CREATE", "EDIT", "INCOMPLETE_USER"})
     *
     * @ORM\Column(type="string", length=24)
     */
    protected $firstName;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA",  "EXPERIENCE_DATA", "ALL_USER_DATA", "REQUEST"})
     * @Assert\NotBlank(message="Don't forget a last name for your user!", groups={"CREATE", "EDIT", "INCOMPLETE_USER"})
     *
     * @ORM\Column(type="string", length=24)
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
     * @ORM\OneToMany(targetEntity="App\Entity\Request", mappedBy="needsApprovalBy")
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
     * @ORM\Column(type="boolean")
     */
    protected $activated = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $activationCode;

    public function __construct()
    {
        $this->lessonFavorites = new ArrayCollection();
        $this->lessons = new ArrayCollection();
        $this->requests = new ArrayCollection();
        $this->requestsThatNeedMyApproval = new ArrayCollection();
        $this->companyFavorites = new ArrayCollection();
        $this->lessonTeachables = new ArrayCollection();
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
        return (string) $this->password;
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
     * @Groups({"ALL_USER_DATA"})
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
     * @Groups({"ALL_USER_DATA"})
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
     * @Groups({"ALL_USER_DATA"})
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

    public function setupAsAdmin() {

        if (!in_array(self::ROLE_ADMIN_USER, $this->roles)) {
            $this->roles[] = self::ROLE_ADMIN_USER;
        }
    }

    public function setupAsProfessional() {

        if (!in_array(self::ROLE_PROFESSIONAL_USER, $this->roles)) {
            $this->roles[] = self::ROLE_PROFESSIONAL_USER;
        }
    }

    public function setupAsStateCoordinator() {

        if (!in_array(self::ROLE_STATE_COORDINATOR_USER, $this->roles)) {
            $this->roles[] = self::ROLE_STATE_COORDINATOR_USER;
        }
    }

    public function setupAsEducator() {

        if (!in_array(self::ROLE_EDUCATOR_USER, $this->roles)) {
            $this->roles[] = self::ROLE_EDUCATOR_USER;
        }
    }

    public function setupAsStudent() {

        if (!in_array(self::ROLE_STUDENT_USER, $this->roles)) {
            $this->roles[] = self::ROLE_STUDENT_USER;
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
        return UploaderHelper::PROFILE_PHOTO.'/'.$this->getPhoto();
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

    public function initializeNewUser()
    {
        $activationCode = bin2hex(random_bytes(32));
        $this->setActivationCode($activationCode);
        $roles = $this->getRoles();
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
}
