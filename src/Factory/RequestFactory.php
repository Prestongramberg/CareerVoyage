<?php

namespace App\Factory;

use App\Entity\SchoolStateUser;
use App\Form\BecomeStateCoordinatorRequestFromSuperAdminFormType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use \Twig\Environment;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RequestFactory
{
    const FORM_BECOME_STATE_COORDINATOR_REQUEST_FROM_SUPER_ADMIN = 'BECOME_STATE_COORDINATOR_REQUEST_FROM_SUPER_ADMIN';

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var Environment
     */
    private $templating;

    /**
     * RequestFactory constructor.
     * @param FormFactoryInterface $formFactory
     * @param Environment $templating
     */
    public function __construct(FormFactoryInterface $formFactory, Environment $templating)
    {
        $this->formFactory = $formFactory;
        $this->templating = $templating;
    }

    public function getForm($type) {
        switch ($type) {
            case self::FORM_BECOME_STATE_COORDINATOR_REQUEST_FROM_SUPER_ADMIN:
                $schoolStateUser = new SchoolStateUser();
                $form = $this->formFactory->create(BecomeStateCoordinatorRequestFromSuperAdminFormType::class, $schoolStateUser);
                return $this->templating->render('request/form/request_new_state_coordinator.html.twig', [
                    'form' => $form->createView()
                ]);
                break;
            default:
                throw new NotFoundHttpException(sprintf("Form not found for given request %s", $type));
                break;
        }
    }
}
