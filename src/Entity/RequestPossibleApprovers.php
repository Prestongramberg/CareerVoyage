<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RequestPossibleApproversRepository")
 */
class RequestPossibleApprovers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Request", inversedBy="requestPossibleApprovers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $request;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="requestPossibleApprovers")
     * @ORM\JoinColumn(nullable=false)
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
        if(!$this->viewedActions) {
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

        $unseenActions = array_filter($this->getRequest()->getRequestActions()->toArray(), function (RequestAction $requestAction) {

            if (!$requestAction->getUser()) {
                return false;
            }

            return $requestAction->getUser()->getId() !== $this->getPossibleApprover()->getId();
        });

        return count($this->getViewedActions()) < count($unseenActions);
    }

}
