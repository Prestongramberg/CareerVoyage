<?php

namespace App\Controller;

use App\Entity\JoinCompanyRequest;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SiteAdminUser;
use App\Entity\StateCoordinator;
use App\Entity\TeachLessonExperience;
use App\Entity\TeachLessonRequest;
use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use App\Mailer\SecurityMailer;
use App\Model\ForgotPassword;
use App\Model\ResetPassword;
use App\Repository\RequestRepository;
use App\Repository\UserRepository;
use App\Util\ServiceHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    use ServiceHelper;

    /**
     * @Route("/forgot-password", name="forgot_password_form", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function forgotPasswordFormAction(Request $request): Response
    {
        $forgotPassword = new ForgotPassword();

        $form = $this->createForm(ForgotPasswordType::class, $forgotPassword, [
            'action' => $this->generateUrl('forgot_password_form'),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if(!$form->isValid()) {

                return $this->render('security/forgot_password.html.twig', [
                    'form' => $form->createView()
                ]);


            } else {

                /** @var ForgotPassword $forgotPassword */
                $forgotPassword = $form->getData();
                $emailAddress = $forgotPassword->getEmailAddress();

                /** @var User $user */
                $user = $this->userRepository->getByEmailAddress($emailAddress);
                if(!$user) {
                    $errorMessage = 'That email does not exist in the system!';
                    $form->addError(new FormError($errorMessage));

                    return $this->render('security/forgot_password.html.twig', [
                        'form' => $form->createView()
                    ]);
                }

                // If the forgot-email function was used within the last 24 hours for
                // this user, render the form with an appropriate validation message.
                // TODO COMMENTING OUT FOR NOW UNTIL WE CAN REFACTOR THE SET PASSWORD TIMESTAMP LOGIC FOR IMPORTS OF EDUCATORS TO HAVE NO
                //  TIME LIMIT
            /*    $currentTokenTimestamp = $user->getPasswordResetTokenTimestamp();
                if ($currentTokenTimestamp && $currentTokenTimestamp >= new \DateTime('-23 hours 59 minutes 59 seconds')) {
                    $errorMessage = 'Sorry, an email containing password reset instructions has been sent to this email address within the last 24 hours';
                    $form->addError(new FormError($errorMessage));

                    return $this->render('security/forgot_password.html.twig', [
                        'form' => $form->createView()
                    ]);
                }*/

                $user->setPasswordResetToken();

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->securityMailer->sendPasswordReset($user);

                return $this->render('security/password-reset-code-sent.html.twig', [
                    'user' => $user
                ]);
            }
        }

        return $this->render('security/forgot_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/password-created", name="password_created", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function passwordCreatedAction(Request $request): Response
    {
        return $this->render('security/password-created.html.twig');
    }

    /**
     * @Route("/account-activated", name="account_activated", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function accountActivatedAction(Request $request): Response
    {
        return $this->render('security/account-activated.html.twig');
    }
    /**
     * @Route("/reset-password/{token}", name="reset_password", requirements={"token" = "^[a-f0-9]{64}$"})
     *
     * @param Request $request
     * @param string $token
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function resetPasswordAction(Request $request, $token)
    {

        $user = $this->userRepository->getByPasswordResetToken($token);

        if(!$user) {
            return $this->render('security/reset-password-error.html.twig');
        }

        $resetPassword = new ResetPassword();

        $form = $this->createForm(ResetPasswordType::class, $resetPassword, [
            'action' => $this->generateUrl('reset_password', ['token' => $token]),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);


        if ($form->isSubmitted()) {

            if (!$form->isValid()) {

                return $this->render('security/reset_password_form.html.twig', [
                    'form' => $form->createView()
                ]);

            } else {

                /** @var ResetPassword $resetPassword */
                $resetPassword = $form->getData();

                $user->setPassword($this->passwordEncoder->encodePassword(
                    $user,
                    $resetPassword->getPassword()
                ));

                $user->setTempPassword(null);

                $user->clearPasswordResetToken();

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $this->redirectToRoute('password_created');
            }
        }

        return $this->render('security/reset_password_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/set-password/{token}", name="set_password", requirements={"token" = "^[a-f0-9]{64}$"})
     *
     * @param Request $request
     * @param string $token
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function setPasswordAction(Request $request, $token)
    {

        $user = $this->userRepository->getByInvitationCode($token);

        if(!$user) {
            return $this->render('security/set-password-error.html.twig');
        }

        $resetPassword = new ResetPassword();

        $form = $this->createForm(ResetPasswordType::class, $resetPassword, [
            'action' => $this->generateUrl('set_password', ['token' => $token]),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if (!$form->isValid()) {

                return $this->render('security/set_password_form.html.twig', [
                    'form' => $form->createView()
                ]);

            } else {

                /** @var ResetPassword $resetPassword */
                $resetPassword = $form->getData();

                $user->setPassword($this->passwordEncoder->encodePassword(
                    $user,
                    $resetPassword->getPassword()
                ));

                $user->setTempPassword(null);

                $user->clearPasswordResetToken();

                $user->setActivated(true);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $this->redirectToRoute('password_created');
            }
        }

        return $this->render('security/set_password_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/account-activation/{activationCode}", name="account_activation", requirements={"activationCode" = "^[a-f0-9]{64}$"})
     *
     * @param Request $request
     * @param $activationCode
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function accountActivationAction(Request $request, $activationCode)
    {
        /** @var User $user */
        $user = $this->userRepository->getByActivationCode($activationCode);

        if (!$user) {
            return $this->redirectToRoute('welcome');
        }

        $user->setActivated(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // When the account is activated it needs the password set right away we redirect to password reset page
        if($user->getPasswordResetToken()) {
            return $this->redirectToRoute('set_password', ['token' => $user->getPasswordResetToken()]);
        }

        if($user instanceof ProfessionalUser && !$user->getSplashShown()) {

            $user->setSplashShown(true);
            $this->entityManager->flush();

            $token = new UsernamePasswordToken($user, null, 'members', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_secured_area', serialize($token));

            return $this->redirectToRoute('splash_professional_welcome', ['splash' => 'professional-welcome']);
        }

        return $this->redirectToRoute('account_activated');
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout()
    {
        // controller can be blank: it will never be executed!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    /**
     * @Route("/request/{token}/activate", name="request_activate", methods={"GET"}, requirements={"token" = "^[a-f0-9]{64}$"})
     *
     * @param Request $httpRequest
     * @param string $token
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function requestActivate(Request $httpRequest, $token)
    {
        $request = $this->requestRepository->findOneBy([
            'activationCode' => $token,
            'approved' => false,
            'denied' => false,
        ]);

        if(!$request || !$request->getAllowApprovalByActivationCode()) {
            // todo render a twig template here instead
            throw new \Exception("Activation code invalid");
        }

        // todo finish this function to allow requests to be approved by email and link in the email to click and approve

        return $this->redirectToRoute('requests');
    }

    /**
     * @Route("/login-as-user", name="login_as_user", methods={"POST"})
     *
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     * @throws \Exception
     */
    public function loginAsUser(Request $request, SessionInterface $session)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();
        $userId = $request->request->get('userId');
        $user = $this->userRepository->find($userId);

        if(!$user) {
            throw new \Exception("User not found");
        }

        if($previouslyLoggedInAs = $session->get('previouslyLoggedInAs', null)) {

            if($previouslyLoggedInAs['userId'] != $userId) {
                throw new \Exception("Error processing request.");
            }

            /** @var User $user */
            $user = $this->userRepository->find($userId);
            if(!$user->canLoginAsAnotherUser()) {
                throw new \Exception("You are not allowed to login as another user.");
            }

            $session->remove('previouslyLoggedInAs');
        } else {
            if(!$loggedInUser->canLoginAsAnotherUser()) {
                throw new \Exception("You are not allowed to login as another user.");
            }

            // double check permissions to make sure the logged in user can switch to that user's account
            $this->denyAccessUnlessGranted('edit', $user);

            $session->set('previouslyLoggedInAs', [
                'userId' => $loggedInUser->getId(),
                'fullName' => $loggedInUser->getFullName()
            ]);
        }

        return $this->guardHandler->authenticateUserAndHandleSuccess(
            $user,
            $request,
            $this->authenticator,
            'main' // firewall name in security.yaml
        );
    }

    /**
     * @Route("/security-router/{token}", name="security_router", methods={"GET"}, requirements={"token" = "^[a-f0-9]{64}$"})
     *
     * @param Request $request
     * @param SessionInterface $session
     * @param $token
     * @return Response
     * @throws \Exception
     */
    public function securityRouter(Request $request, SessionInterface $session, $token)
    {
        $user = $this->userRepository->getByTemporarySecurityToken($token);

        if(!$user) {
            throw new \Exception("User not found");
        }

      /*  $request->getSession()->invalidate();
        $this->securityToken->setToken(null);
        $session->clear();*/

        return $this->guardHandler->authenticateUserAndHandleSuccess(
            $user,
            $request,
            $this->authenticator,
            'main' // firewall name in security.yaml
        );
    }

    /**
     * The normal logout function/route was not clearing all the session history due to our
     * custom middleware for logging into various site URLs
     *
     * @Route("/sign-out", name="sign_out", methods={"GET"})
     *
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     * @throws \Exception
     */
    public function signOut(Request $request, SessionInterface $session)
    {
        $session->invalidate();
        $this->securityToken->setToken(null);
        $session->clear();

        return $this->redirectToRoute('welcome');
    }
}
