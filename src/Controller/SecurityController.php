<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use App\Mailer\ResetPasswordMailer;
use App\Model\ForgotPassword;
use App\Model\ResetPassword;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var ResetPasswordMailer
     */
    private $resetPasswordMailer;

    /**
     * SecurityController constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ResetPasswordMailer $resetPasswordMailer
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        ResetPasswordMailer $resetPasswordMailer
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->resetPasswordMailer = $resetPasswordMailer;
    }

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

                $this->resetPasswordMailer->send($user);

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
     * @param Request $request
     * @param User $user
     * @param RedirectResponse $redirectResponse
     * @return RedirectResponse|Response
     */
    protected function generatePasswordActionResponse(Request $request, User $user, RedirectResponse $redirectResponse)
    {
        $form = $this->createForm('app_set_password');

        $form->handleRequest($request);

        if ($request->isMethod(Request::METHOD_POST) && $form->isValid()) {
            /** @var ChangePassword $changePassword */
            $changePassword = $form->getData();

            $encodedPassword = $this->passwordEncoder->encodePassword($user, $changePassword->getPassword());
            $user->setPassword($encodedPassword)
                ->clearPasswordResetToken()
                ->setIsPasswordSetUp(true);

            $this->entityManager->persist($user);
            $this->entityManager->flush($user);

            return $redirectResponse;
        }

        $returnUrl = $request->headers->get('referer');

        if (!empty($returnUrl)) {
            $session = $this->get('session');
            $session->set('return_destination', $returnUrl);
        }

        return $this->render('security/set-password.html.twig', array(
            'method' => 'post',
            'form'   => $form->createView(),
            'user'   => $user,
        ));
    }
}
