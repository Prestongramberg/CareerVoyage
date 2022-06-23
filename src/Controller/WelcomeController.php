<?php

namespace App\Controller;

use App\Entity\EducatorUser;
use App\Entity\ProfessionalUser;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Form\EducatorRegistrationFormType;
use App\Form\ProfessionalRegistrationFormType;
use App\Form\StudentRegistrationFormType;
use App\Security\LoginFormAuthenticator;
use App\Service\UploaderHelper;
use App\Util\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    use ServiceHelper;

    /**
     * @Route("/", name="welcome")
     * @param Request                      $request
     * @param AuthenticationUtils          $authenticationUtils
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler    $guardHandler
     * @param LoginFormAuthenticator       $authenticator
     * @param string                       $uploadsPath
     *
     * @return JsonResponse|Response
     * @throws \Exception
     */
    public function index(Request $request, AuthenticationUtils $authenticationUtils, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator, string $uploadsPath)
    {

 //       return $this->redirect('http://futureforward.org/');

        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('dashboard');
        }


        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();


        $options = [
            'action' => $this->generateUrl('welcome'),
            'skip_validation' => $request->request->get('skip_validation', false)
        ];

        // START PROFESSIONAL REGISTRATION FORM
        $professionalUser = new ProfessionalUser();
        $professionalRegistrationForm = $this->createForm(ProfessionalRegistrationFormType::class, $professionalUser, $options);

        // START EDUCATOR REGISTRATION FORM
       /* $educatorUser = new EducatorUser();
        $educatorRegistrationForm = $this->createForm(EducatorRegistrationFormType::class, $educatorUser, [
            'action' => $this->generateUrl('welcome'),
            'method' => 'POST',
        ]);

        $studentUser = new StudentUser();
        $studentRegistrationForm = $this->createForm(StudentRegistrationFormType::class, $studentUser, [
            'action' => $this->generateUrl('welcome'),
            'method' => 'POST',
        ]);*/


        // HANDLE FORM SUBMISSIONS
        $formType = null;
        if($request->getMethod() === 'POST') {

            $formType = $request->request->get('formType');
            switch ($formType) {
                case 'educatorRegistrationForm':
                    /*$form = $educatorRegistrationForm;*/
                    break;
                case 'professionalRegistrationForm':
                    $form = $professionalRegistrationForm;
                    break;
                case 'studentRegistrationForm':
                    /*$form = $studentRegistrationForm;*/
                    break;
                default:
                    throw new \Exception("Form type not found");
                    break;
            }

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                /** @var User $user */
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
                        $form->get('plainPassword')->getData()
                    )
                );

                if (true === $form['agreeTerms']->getData()) {

                    $user->agreeToTerms();
                    $user->initializeNewUser();

                    if($user instanceof ProfessionalUser) {

                        $regions = $this->regionRepository->findAll();

                        foreach($regions as $region) {
                            $user->addRegion($region);
                        }
                    }

                    if (!$user->getPhoto() && $user->getFirstName() && $user->getLastName()) {

                        $avatarUrl    = sprintf("https://ui-avatars.com/api/?name=%s+%s&background=random&size=128", $user->getFirstName(), $user->getLastName());
                        $fileContents = file_get_contents($avatarUrl);

                        $filesystem  = new Filesystem();
                        $destination = $uploadsPath . '/' . UploaderHelper::PROFILE_PHOTO;
                        $fileName = uniqid('', true) . '.png';
                        $filesystem->dumpFile($destination . '/' . $fileName, $fileContents);
                        $user->setPhoto($fileName);
                    }


                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();

                    // send activation email
                    $this->securityMailer->sendAccountActivation($user);

                    return $this->redirectToRoute('account_pending_approval');

                   /* // do anything else you need here, like send an email
                    return $guardHandler->authenticateUserAndHandleSuccess(
                        $user,
                        $request,
                        $authenticator,
                        'main' // firewall name in security.yaml
                    );*/
                }
            }
        }

        return $this->render('welcome/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'professionalRegistrationForm' => $professionalRegistrationForm->createView(),
            /*'educatorRegistrationForm' => $educatorRegistrationForm->createView(),
            'studentRegistrationForm' => $studentRegistrationForm->createView(),*/
            'formType' => $formType
        ]);
    }

    /**
     * @Route("/account-pending-approval", name="account_pending_approval")
     * @param Request $request
     * @return JsonResponse|Response
     * @throws \Exception
     */
    public function accountPendingApproval(Request $request)
    {
        return $this->render('welcome/account-pending-approval.html.twig', []);
    }

    /**
     * @Route("/industry-change", name="industry_change")
     * @param Request $request
     * @return JsonResponse|Response
     * @throws \Exception
     */
    public function industryChange(Request $request)
    {
        $options = [
            'method' => 'POST',
            'action' => $this->generateUrl('welcome'),
            'skip_validation' => $request->request->get('skip_validation', false)
        ];

        // START PROFESSIONAL REGISTRATION FORM
        $professionalUser = new ProfessionalUser();
        $form = $this->createForm(ProfessionalRegistrationFormType::class, $professionalUser, $options);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // noop cause form will never be valid for this request
        }

        return new JsonResponse(
            [
                'success' => false,
                'formMarkup' => $this->renderView('welcome/index.html.twig', [
                    'professionalRegistrationForm' => $form->createView(),
                    'formType' => 'professionalRegistrationForm',
                    'last_username' => null,
                    'error' => null,
                ])
            ], Response::HTTP_BAD_REQUEST
        );
    }
}
