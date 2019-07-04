<?php

namespace App\Controller;

use App\Entity\ProfessionalUser;
use App\Entity\User;
use App\Form\EducatorRegistrationFormType;
use App\Form\ProfessionalRegistrationFormType;
use App\Form\StudentRegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class WelcomeController
 * @package App\Controller
 */
class WelcomeController extends AbstractController
{
    /**
     * @Route("/", name="welcome")
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $authenticator
     * @return Response
     * @throws \Exception
     */
    public function index(Request $request, AuthenticationUtils $authenticationUtils, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // START PROFESSIONAL REGISTRATION FORM
        $professionalUser = new ProfessionalUser();
        $professionalRegistrationForm = $this->createForm(ProfessionalRegistrationFormType::class, $professionalUser, [
            'action' => $this->generateUrl('welcome'),
            'method' => 'POST',
        ]);

        // START EDUCATOR REGISTRATION FORM
        $educatorRegistrationForm = $this->createForm(EducatorRegistrationFormType::class, $professionalUser, [
            'action' => $this->generateUrl('welcome'),
            'method' => 'POST',
        ]);

        $studentRegistrationForm = $this->createForm(StudentRegistrationFormType::class, $professionalUser, [
            'action' => $this->generateUrl('welcome'),
            'method' => 'POST',
        ]);


        // HANDLE FORM SUBMISSIONS
        $formType = null;
        if($request->getMethod() === 'POST') {

            $formType = $request->request->get('formType');
            switch ($formType) {
                case 'educatorRegistrationForm':
                    $form = $educatorRegistrationForm;
                    break;
                case 'professionalRegistrationForm':
                    $form = $professionalRegistrationForm;
                    break;
                case 'studentRegistrationForm':
                    $form = $studentRegistrationForm;
                    break;
                default:
                    throw new \Exception("Form type not found");
                    break;
            }

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $user = $form->getData();

                switch ($formType) {
                    case 'educatorRegistrationForm':
                        $user->setupAsEducator();
                        break;
                    case 'professionalRegistrationForm':
                        $user->setupAsProfessional();
                        break;
                    case 'studentRegistrationForm':
                        $user->setupAsStudent();
                        break;
                }


                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                // do anything else you need here, like send an email
                return $guardHandler->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $authenticator,
                    'main' // firewall name in security.yaml
                );
            }
        }

        return $this->render('welcome/index.html.twig', [
            'last_username' => $lastUsername, 'error' => $error,
            'professionalRegistrationForm' => $professionalRegistrationForm->createView(),
            'educatorRegistrationForm' => $educatorRegistrationForm->createView(),
            'studentRegistrationForm' => $studentRegistrationForm->createView(),
            'formType' => $formType
        ]);
    }
}