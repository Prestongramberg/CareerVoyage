<?php

namespace App\Entity;

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


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email", groups={"CREATE", "EDIT"})
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username", groups={"CREATE", "EDIT"})
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"professionalUser" = "ProfessionalUser", "educatorUser" = "EducatorUser", "studentUser" = "StudentUser"})
 */
abstract class User implements UserInterface
{

    use TimestampableEntity;

    const ROLE_USER = 'ROLE_USER';
    const ROLE_PROFESSIONAL_USER = 'ROLE_PROFESSIONAL_USER';
    const ROLE_EDUCATOR_USER = 'ROLE_EDUCATOR_USER ';
    const ROLE_STUDENT_USER = 'ROLE_STUDENT_USER';

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;
    
    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true,
     *     groups={"CREATE", "EDIT"}
     * )
     * @Assert\NotBlank(message="Don't forget an email for your user!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=180, unique=true)
     */
    protected $email;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @Assert\NotBlank(message="Don't forget a username for your user!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=180, unique=true)
     */
    protected $username;

    /**
     * @RollerworksPassword\PasswordRequirements(requireLetters=true, requireNumbers=true, requireCaseDiff=true, requireSpecialCharacter= true, minLength = "6", groups={"CREATE", "EDIT"})
     * @Assert\NotBlank(message="Don't forget a password for your user!", groups={"CREATE"})
     *
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @Assert\NotBlank(message="Don't forget a first name for your user!", groups={"CREATE", "EDIT"})
     *
     * @ORM\Column(type="string", length=24)
     */
    protected $firstName;

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @Assert\NotBlank(message="Don't forget a last name for your user!", groups={"CREATE", "EDIT"})
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
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * @Groups({"PROFESSIONAL_USER_DATA"})
     * @ORM\Column(type="boolean")
     */
    protected $deleted = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $agreedToTermsAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LessonFavorite", mappedBy="user", orphanRemoval=true)
     */
    protected $lessonFavorites;

    public function __construct()
    {
        $this->lessonFavorites = new ArrayCollection();
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
        // $this->plainPassword = null;
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

    public function setupAsProfessional() {

        if (!in_array(self::ROLE_PROFESSIONAL_USER, $this->roles)) {
            $this->roles[] = self::ROLE_PROFESSIONAL_USER;
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

}
