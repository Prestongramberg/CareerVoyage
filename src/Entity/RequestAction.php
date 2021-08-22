<?php

namespace App\Entity;

use App\Repository\RequestActionRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\HasLifecycleCallbacks()
 *
 * @ORM\Entity(repositoryClass=RequestActionRepository::class)
 */
class RequestAction
{
    use Timestampable;

    const REQUEST_ACTION_NAME_APPROVE                = 'APPROVE';
    const REQUEST_ACTION_NAME_DENY                   = 'DENY';
    const REQUEST_ACTION_NAME_MARK_AS_PENDING        = 'MARK_AS_PENDING';
    const REQUEST_ACTION_NAME_HIDE                   = 'HIDE';
    const REQUEST_ACTION_NAME_READ_RECEIPT           = 'READ_RECEIPT';
    const REQUEST_ACTION_NAME_REMOVE_FROM_COMPANY    = 'REMOVE_FROM_COMPANY';
    const REQUEST_ACTION_NAME_LEAVE_COMPANY          = 'LEAVE_COMPANY';
    const REQUEST_ACTION_NAME_SUGGEST_NEW_DATES      = 'SUGGEST_NEW_DATES';
    const REQUEST_ACTION_NAME_SEND_MESSAGE           = 'SEND_MESSAGE';
    const REQUEST_ACTION_NAME_DEFAULT                = 'DEFAULT';
    const REQUEST_ACTION_NAME_MARK_AS_ACTIVE         = 'MARK_AS_ACTIVE';
    const REQUEST_ACTION_NAME_MARK_AS_INACTIVE       = 'MARK_AS_INACTIVE';
    const REQUEST_ACTION_NAME_VIEW_REGISTRATION_LIST = 'VIEW_REGISTRATION_LIST';
    const REQUEST_ACTION_NAME_UNREGISTER             = 'UNREGISTER';
    const REQUEST_ACTION_NAME_REGISTER               = 'REGISTER';


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Request::class, inversedBy="requestActions")
     */
    private $request;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="requestActions")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setRequest(?Request $request): self
    {
        $this->request = $request;

        return $this;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFriendlyName()
    {
        $friendlyName = $this->name;

        switch ($this->getRequest()->getRequestType()) {
            case Request::REQUEST_TYPE_COMPANY_INVITE:
                if ($this->name === self::REQUEST_ACTION_NAME_APPROVE) {
                    $friendlyName = 'Company Invite Accepted';
                }

                if ($this->name === self::REQUEST_ACTION_NAME_DENY) {
                    $friendlyName = 'Company Invite Denied';
                }

                if ($this->name === self::REQUEST_ACTION_NAME_HIDE) {
                    $friendlyName = 'Hidden';
                }

                if ($this->name === self::REQUEST_ACTION_NAME_READ_RECEIPT) {
                    $friendlyName = 'Your request has been seen';
                }

                if ($this->name === self::REQUEST_ACTION_NAME_REMOVE_FROM_COMPANY) {
                    $friendlyName = 'Removed from company';
                }

                if ($this->name === self::REQUEST_ACTION_NAME_LEAVE_COMPANY) {
                    $friendlyName = 'Left company';
                }
                break;
            default:
                if ($this->name === self::REQUEST_ACTION_NAME_APPROVE) {
                    $friendlyName = 'Approved';
                }

                if ($this->name === self::REQUEST_ACTION_NAME_DENY) {
                    $friendlyName = 'Denied';
                }

                if ($this->name === self::REQUEST_ACTION_NAME_HIDE) {
                    $friendlyName = 'Hidden';
                }

                if ($this->name === self::REQUEST_ACTION_NAME_READ_RECEIPT) {
                    $friendlyName = 'Your request has been seen';
                }

                if ($this->name === self::REQUEST_ACTION_NAME_REMOVE_FROM_COMPANY) {
                    $friendlyName = 'Removed from company';
                }

                if ($this->name === self::REQUEST_ACTION_NAME_LEAVE_COMPANY) {
                    $friendlyName = 'Left company';
                }
                break;
        }

        return $friendlyName;
    }

    public function getLabelCssClass()
    {

        switch ($this->name) {
            case self::REQUEST_ACTION_NAME_APPROVE:
            case self::REQUEST_ACTION_NAME_MARK_AS_ACTIVE:
                return 'uk-label-success';
                break;
            case self::REQUEST_ACTION_NAME_DENY:
            case self::REQUEST_ACTION_NAME_MARK_AS_INACTIVE:
            case self::REQUEST_ACTION_NAME_REMOVE_FROM_COMPANY:
            case self::REQUEST_ACTION_NAME_LEAVE_COMPANY:
                return 'uk-label-danger';
                break;
            default:
                return 'uk-label';
                break;
        }
    }
}
