<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\HasLifecycleCallbacks()
 *
 * @ORM\Entity(repositoryClass="App\Repository\RequestPossibleApproversRepository")
 */
class RequestPossibleApprovers
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Request", inversedBy="requestPossibleApprovers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $request;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="requestPossibleApprovers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $possibleApprover;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $possibleActions = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $notificationTitle;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $viewedActions = [];

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasNotification = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $notificationDate;

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

    public function getPossibleApprover(): ?User
    {
        return $this->possibleApprover;
    }

    public function setPossibleApprover(?User $possibleApprover): self
    {
        $this->possibleApprover = $possibleApprover;

        return $this;
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

    public function getNotificationTitle(): ?string
    {
        return $this->notificationTitle;
    }

    public function setNotificationTitle(?string $notificationTitle): self
    {
        $this->notificationTitle = $notificationTitle;

        return $this;
    }

    public function getViewedActions(): ?array
    {
        if (!$this->viewedActions) {
            return [];
        }

        return $this->viewedActions;
    }

    public function setViewedActions(?array $viewedActions): self
    {
        $this->viewedActions = $viewedActions;

        return $this;
    }

    public function hasNewNotifications()
    {
        if (!$this->getRequest()) {
            return false;
        }

        $unseenActions = array_filter($this->getRequest()->getRequestActions()->toArray(), function (RequestAction $requestAction
        ) {

            if (!$requestAction->getUser()) {
                return false;
            }

            return $requestAction->getUser()->getId() !== $this->getPossibleApprover()->getId();
        });

        return count($this->getViewedActions()) < count($unseenActions);
    }

    public function getNotifications()
    {

        if (!$this->getRequest()) {
            return [];
        }

        return array_filter($this->getRequest()->getRequestActions()->toArray(), function (RequestAction $requestAction
        ) {

            if (!$requestAction->getUser()) {
                return false;
            }

            return $requestAction->getUser()->getId() !== $this->getPossibleApprover()->getId();
        });
    }

    public function readNotifications()
    {

        $viewedActions = [];
        /** @var RequestAction $notification */
        foreach ($this->getNotifications() as $notification) {
            $viewedActions[] = $notification->getId();
        }

        $this->setViewedActions(array_unique($viewedActions));
    }

    public function getHasNotification(): ?bool
    {
        if(!$this->hasNotification) {
            return false;
        }

        return $this->hasNotification;
    }

    public function setHasNotification(?bool $hasNotification): self
    {
        if($hasNotification) {
            $this->notificationDate = new \DateTime();
        }

        $this->hasNotification = $hasNotification;

        return $this;
    }

    public function getTimeElapsedSinceHasNotification()
    {
        // the notification date should always be there but just incase it's not we have some fall backs. Legacy, etc
        if($this->notificationDate) {
            return $this->notificationDate->format("m/d/Y h:i A");
        }

        if($this->updatedAt) {
            return $this->updatedAt->format("m/d/Y h:i A");
        }

        if($this->createdAt) {
            return $this->createdAt->format("m/d/Y h:i A");
        }

        return $this->getRequest()->getCreatedAt()->format("m/d/Y h:i A");
    }

    public function getNotificationDate(): ?\DateTimeInterface
    {
        return $this->notificationDate;
    }

    public function setNotificationDate(?\DateTimeInterface $notificationDate): self
    {
        $this->notificationDate = $notificationDate;

        return $this;
    }

}
