<?php

namespace App\Form\Flow;

use App\Entity\Feedback;
use App\Entity\SignUp;
use App\Entity\UserImport;
use App\Form\Step\UserImport\BasicInfoStep;
use App\Form\Step\UserImport\ColumnMappingInfoStep;
use App\Form\Step\UserImport\FileInfoStep;
use App\Form\Step\UserImport\UserInfoStep;
use Craue\FormFlowBundle\Event\GetStepsEvent;
use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Event\PostValidateEvent;
use Craue\FormFlowBundle\Form\FormFlowEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class UserImportFlow
 *
 * @package App\Form\Flow
 */
class UserImportFlow extends FormFlow implements EventSubscriberInterface
{

    protected $allowDynamicStepNavigation = true;

    protected $revalidatePreviousSteps = false;

    protected $allowRedirectAfterSubmit = true;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RequestStack $requestStack
     */
    private $requestStack;

    /**
     * @var int
     */
    private $lastStepNumber;

    /**
     * @param  SessionInterface        $session
     * @param  EntityManagerInterface  $entityManager
     * @param  RequestStack            $requestStack
     */
    public function __construct(
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack
    ) {
        $this->session       = $session;
        $this->entityManager = $entityManager;
        $this->requestStack  = $requestStack;
    }

    /**
     * This method is only needed when _not_ using autoconfiguration. If it's there even with autoconfiguration enabled,
     * the `removeSubscriber` call ensures that subscribed events won't occur twice.
     * (You can remove the `removeSubscriber` call if you'll definitely never use autoconfiguration for that flow.)
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        parent::setEventDispatcher($dispatcher);
        $dispatcher->removeSubscriber($this);
        $dispatcher->addSubscriber($this);
    }

    public static function getSubscribedEvents()
    {
        return [
            FormFlowEvents::POST_VALIDATE => 'onPostValidate',
            FormFlowEvents::GET_STEPS     => 'onGetSteps',
        ];
    }

    public function onGetSteps(GetStepsEvent $event)
    {
        $flow = $event->getFlow();

        if ($flow->getName() !== $this->getName()) {
            return;
        }

        /** @var UserImport $userImport */
        $userImport = $flow->getFormData();
        $type = $userImport->getType();

        $event->stopPropagation();

        $steps = [];

        if (!in_array($type, ['Student', 'Educator'])) {
            $steps[] = BasicInfoStep::create($this->requestStack->getCurrentRequest(), count($steps) + 1);
        }

        $steps[] = FileInfoStep::create($this->requestStack->getCurrentRequest(), count($steps) + 1);

        $steps[] = ColumnMappingInfoStep::create($this->requestStack->getCurrentRequest(), count($steps) + 1);

        $steps[] = UserInfoStep::create($this->requestStack->getCurrentRequest(), count($steps) + 1);

        $this->lastStepNumber = count($steps);

        $event->setSteps($steps);

        return $event;
    }


    /**
     * Only runs when the form is valid.
     *
     * @param  PostValidateEvent  $event
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function onPostValidate(PostValidateEvent $event)
    {
        $flow = $event->getFlow();
        $step = $flow->getStep($flow->getCurrentStepNumber());

        // We have multiple form flows so we need to make sure the event from another form flow does not get called
        if ($flow->getName() !== $this->getName()) {
            return;
        }

        /** @var UserImport $userImport */
        $userImport = $event->getFlow()
                            ->getFormData();

        if($step->getName() === 'file_info_step') {
            foreach($userImport->getUserImportUsers() as $userImportUser) {

                if($userImportUser->getId()) {
                    $this->entityManager->remove($userImportUser);
                }
            }

            foreach($userImport->getUserImportUsers() as $userImportUser) {
                $userImportUser->setUserImport($userImport);
            }

            $this->entityManager->persist($userImport);
            $this->entityManager->flush();
        }

        if($step->getName() === 'column_mapping_info_step') {
            foreach($userImport->getUserImportUsers() as $userImportUser) {

                if($userImportUser->getId()) {
                    $this->entityManager->remove($userImportUser);
                }
            }

            foreach($userImport->getUserImportUsers() as $userImportUser) {
                $userImportUser->setUserImport($userImport);
            }

            $this->entityManager->persist($userImport);
            $this->entityManager->flush();
        }
    }

    public function getFirstStepNumber()
    {
        return 1;
    }

    /**
     * @return int
     */
    public function getLastStepNumber()
    {
        return $this->lastStepNumber;
    }

    public function getName()
    {
        return 'userImport';
    }

}