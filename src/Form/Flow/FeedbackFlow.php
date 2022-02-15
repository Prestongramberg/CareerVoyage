<?php

namespace App\Form\Flow;

use App\Cache\CacheKey;
use App\Entity\Feedback;
use App\Form\Step\Feedback\BasicInfoStep;
use App\Form\Step\Feedback\CompanyInfoStep;
use App\Form\Step\Feedback\FeedbackInfoStep;
use App\Form\Step\Feedback\SchoolInfoStep;
use Craue\FormFlowBundle\Event\GetStepsEvent;
use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Event\PostValidateEvent;
use Craue\FormFlowBundle\Form\FormFlowEvents;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Class FeedbackFlow
 *
 * @package App\Form\Flow
 */
class FeedbackFlow extends FormFlow implements EventSubscriberInterface
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
        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request          = $flow->getRequest();
        $basicInfo        = $request->request->get('basicInfo', []);
        $feedbackProvider = $basicInfo['feedbackProvider'] ?? null;

        if ($flow->getName() !== $this->getName()) {
            return;
        }

        /** @var Feedback $feedback */
        $feedback = $flow->getFormData();

        $event->stopPropagation();

        $steps = [];

        /*   if (!$signUp->hasQueryParameter('vid')) {
               $steps[] = JoinNSCSStep::create($this->requestStack->getCurrentRequest(), count($steps) + 1);
           }*/

        $steps[] = BasicInfoStep::create($this->requestStack->getCurrentRequest(), count($steps) + 1);

        /*if (in_array($feedback->getFeedbackProvider(), ['Student', 'Educator'], true) || in_array($feedbackProvider, ['Student', 'Educator'], true)) {
            $steps[] = SchoolInfoStep::create($this->requestStack->getCurrentRequest(), count($steps) + 1);
        }*/

        $steps[] = SchoolInfoStep::create($this->requestStack->getCurrentRequest(), count($steps) + 1);

        $steps[] = CompanyInfoStep::create($this->requestStack->getCurrentRequest(), count($steps) + 1);

        $steps[] = FeedbackInfoStep::create($this->requestStack->getCurrentRequest(), count($steps) + 1);

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
        return 'feedback';
    }

}