<?php

namespace App\Controller;

use App\Entity\JoinCompanyRequest;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\RegionalCoordinatorRequest;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolAdministratorRequest;
use App\Entity\SiteAdminRequest;
use App\Entity\SiteAdminUser;
use App\Entity\StateCoordinator;
use App\Entity\StateCoordinatorRequest;
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
                $currentTokenTimestamp = $user->getPasswordResetTokenTimestamp();
                if ($currentTokenTimestamp && $currentTokenTimestamp >= new \DateTime('-23 hours 59 minutes 59 seconds')) {
                    $errorMessage = 'Sorry, an email containing password reset instructions has been sent to this email address within the last 24 hours';
                    $form->addError(new FormError($errorMessage));

                    return $this->render('security/forgot_password.html.twig', [
                        'form' => $form->createView()
                    ]);
                }

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

        $this->handleRequestApproval($request, $httpRequest);

        return $this->redirectToRoute('requests');
    }

    /**
     * @param \App\Entity\Request $request
     * @param Request $httpRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private function handleRequestApproval(\App\Entity\Request $request, Request $httpRequest) {

        switch($request->getClassName()) {
            case 'NewCompanyRequest':
                /** @var NewCompanyRequest $request */
                $request->setApproved(true);
                $company = $request->getCompany();
                $company->setApproved(true);
                $this->entityManager->persist($company);
                $this->addFlash('success', 'Company approved');
                $this->requestsMailer->newCompanyRequestApproval($request);
                break;
            case 'JoinCompanyRequest':
                /** @var JoinCompanyRequest $request */
                $request->setApproved(true);
                if($request->getIsFromCompany()) {
                    /** @var ProfessionalUser $needsApprovalBy */
                    $needsApprovalBy = $request->getNeedsApprovalBy();
                    $needsApprovalBy->setupAsProfessional();
                    $needsApprovalBy->setCompany($request->getCompany());
                    $needsApprovalBy->agreeToTerms();
                    $this->entityManager->persist($needsApprovalBy);
                    $this->addFlash('success', 'You have joined the company!');
                } else {
                    /** @var ProfessionalUser $createdBy */
                    $createdBy = $request->getCreatedBy();
                    $createdBy->setupAsProfessional();
                    $createdBy->setCompany($request->getCompany());
                    $createdBy->agreeToTerms();
                    $this->entityManager->persist($createdBy);
                    $this->addFlash('success', 'User successfully added to company!');
                }
                $this->requestsMailer->joinCompanyRequestApproval($request);
                break;
            case 'StateCoordinatorRequest':
                /** @var StateCoordinatorRequest $request */
                $request->setApproved(true);
                /** @var StateCoordinator $needsApprovalBy */
                $needsApprovalBy = $request->getNeedsApprovalBy();
                $this->addFlash('success', 'You have accepted a state coordinator position!');
                $needsApprovalBy->setState($request->getState());
                $needsApprovalBy->agreeToTerms();
                $needsApprovalBy->setupAsStateCoordinator();
                $this->entityManager->persist($needsApprovalBy);
                $this->requestsMailer->stateCoordinatorRequestApproval($request);
                break;
            case 'RegionalCoordinatorRequest':
                /** @var RegionalCoordinatorRequest $request */
                $request->setApproved(true);
                /** @var RegionalCoordinator $needsApprovalBy */
                $needsApprovalBy = $request->getNeedsApprovalBy();
                $this->addFlash('success', 'You have accepted a regional coordinator position!');
                $needsApprovalBy->setRegion($request->getRegion());
                $needsApprovalBy->agreeToTerms();
                $this->entityManager->persist($needsApprovalBy);
                $this->requestsMailer->regionalCoordinatorRequestApproval($request);
                break;
            case 'SchoolAdministratorRequest':
                /** @var SchoolAdministratorRequest $request */
                $request->setApproved(true);
                /** @var SchoolAdministrator $needsApprovalBy */
                $needsApprovalBy = $request->getNeedsApprovalBy();
                $this->addFlash('success', 'You have accepted a school administrator position!');
                $needsApprovalBy->addSchool($request->getSchool());
                $needsApprovalBy->agreeToTerms();
                $this->entityManager->persist($needsApprovalBy);
                $this->requestsMailer->schoolAdministratorRequestApproval($request);
                break;
            case 'TeachLessonRequest':
                /** @var TeachLessonRequest $request */
                $request->setApproved(true);
                /** @var ProfessionalUser $needsApprovalBy */
                $needsApprovalBy = $request->getNeedsApprovalBy();

                $date = DateTime::createFromFormat('m/d/Y g:i A', $httpRequest->request->get('date'));
                $teachLessonExperience = new TeachLessonExperience();
                $teachLessonExperience->setStartDateAndTime($date);
                $teachLessonExperience->setTitle('Lesson Teaching');
                $teachLessonExperience->setBriefDescription(sprintf("
                You are teaching lesson %s at school %s
                ", $request->getLesson()->getTitle(), $request->getCreatedBy()->getSchool()->getName()));

                /** @var School $school */
                $school = $request->getCreatedBy()->getSchool();

                // the CSV school import fixtures did not have emails so we need to check for them!
                if($school->getEmail()) {
                    $teachLessonExperience->setEmail($school->getEmail());
                }

                if($school->getStreet()) {
                    $teachLessonExperience->setStreet($school->getStreet());
                }

                if($school->getCity()) {
                    $teachLessonExperience->setCity($school->getCity());
                }

                if($school->getState()) {
                    $teachLessonExperience->setState($school->getState());
                }

                if($school->getZipcode()) {
                    $teachLessonExperience->setZipcode($school->getZipcode());
                }

                $this->entityManager->persist($teachLessonExperience);
                $this->addFlash('success', 'You have accepted the invite to teach!');

                // not all educators have an email address.
                if($request->getCreatedBy()->getEmail()) {
                    $this->requestsMailer->teachLessonRequestApproval($request);
                }
                break;
            case 'SiteAdminRequest':
                /** @var SiteAdminRequest $request */
                $request->setApproved(true);
                /** @var SiteAdminUser $needsApprovalBy */
                $needsApprovalBy = $request->getNeedsApprovalBy();
                $this->addFlash('success', 'You have accepted a site administrator position!');
                $needsApprovalBy->setSite($request->getSite());
                $needsApprovalBy->agreeToTerms();
                $needsApprovalBy->setupAsSiteAdminUser();
                $this->entityManager->persist($needsApprovalBy);
                $this->requestsMailer->siteAdminRequestApproval($request);
                break;
        }
        $this->entityManager->persist($request);
        $this->entityManager->flush();
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
}
