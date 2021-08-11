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

    const REQUEST_ACTION_NAME_APPROVE             = 'APPROVE';
    const REQUEST_ACTION_NAME_DENY                = 'DENY';
    const REQUEST_ACTION_NAME_HIDE                = 'HIDE';
    const REQUEST_ACTION_NAME_READ_RECEIPT        = 'READ_RECEIPT';
    const REQUEST_ACTION_NAME_REMOVE_FROM_COMPANY = 'REMOVE_FROM_COMPANY';


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

        if ($this->name === self::REQUEST_ACTION_NAME_APPROVE) {
            return 'Approved';
        }

        if ($this->name === self::REQUEST_ACTION_NAME_DENY) {
            return 'Denied';
        }

        if ($this->name === self::REQUEST_ACTION_NAME_HIDE) {
            return 'Hidden';
        }

        if ($this->name === self::REQUEST_ACTION_NAME_READ_RECEIPT) {
            return 'Your request has been seen';
        }

        if ($this->name === self::REQUEST_ACTION_NAME_REMOVE_FROM_COMPANY) {
            return 'Removed from company';
        }

        return $this->name;
    }

    public function getLabelCssClass()
    {

        switch ($this->name) {
            case self::REQUEST_ACTION_NAME_APPROVE:
                return 'uk-label-success';
                break;
            case self::REQUEST_ACTION_NAME_DENY:
            case self::REQUEST_ACTION_NAME_REMOVE_FROM_COMPANY:
                return 'uk-label-danger';
                break;
            default:
                return 'uk-label';
                break;
        }
    }
}
