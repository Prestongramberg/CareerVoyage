<?php

namespace App\Mailer;

use App\Entity\EmailLog;
use App\Entity\RegionalCoordinator;
use App\Entity\SchoolAdministrator;
use App\Entity\SiteAdminUser;
use App\Entity\StateCoordinator;
use App\Entity\User;

/**
 * Class SecurityMailer
 * @package App\Mailer
 */
class SecurityMailer extends AbstractMailer
{
    public function sendPasswordReset(User $user) {

        $resetPasswordUrl = $this->getFullyQualifiedBaseUrl().$this->router->generate(
                'reset_password',
                array('token' => $user->getPasswordResetToken())
            );

        $message = (new \Swift_Message('Password Reset'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/passwordResetEmail.html.twig',
                    ['user' => $user, 'resetPasswordUrl' => $resetPasswordUrl]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Password Reset');
        $log->setToEmail($user->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function sendPasswordSetup(User $user) {

        $setPasswordUrl = $this->getFullyQualifiedBaseUrl().$this->router->generate(
                'set_password',
                array('token' => $user->getInvitationCode())
            );

        $message = (new \Swift_Message('Password Setup'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/passwordSetEmail.html.twig',
                    ['user' => $user, 'resetPasswordUrl' => $setPasswordUrl]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Password Setup');
        $log->setToEmail($user->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function sendAccountActivation(User $user) {

        $accountActivationUrl = $this->getFullyQualifiedBaseUrl().$this->router->generate(
                'account_activation',
                array('activationCode' => $user->getActivationCode())
            );

        $message = (new \Swift_Message('Activate Account'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/accountActivationEmail.html.twig',
                    ['user' => $user, 'accountActivationUrl' => $accountActivationUrl]
                ),
                'text/html'
            );
        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Activate Account');
        $log->setToEmail($user->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * This function should only be called when the users account has been setup
     * by another user or request process and all the user has left is to set their password
     * @param SiteAdminUser $siteAdminUser
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendPasswordSetupForSiteAdmin(SiteAdminUser $siteAdminUser) {

        $passwordSetUrl = $this->getFullyQualifiedBaseUrl().$this->router->generate(
                'set_password',
                array('token' => $siteAdminUser->getInvitationCode())
            );

        $message = (new \Swift_Message('Password Setup For Site Admin'))
            ->setFrom($this->siteFromEmail)
            ->setTo($siteAdminUser->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/passwordSetupForSiteAdminEmail.html.twig',
                    ['user' => $siteAdminUser, 'passwordSetUrl' => $passwordSetUrl]
                ),
                'text/html'
            );
        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Password Setup For Site Admin');
        $log->setToEmail($siteAdminUser->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * This function should only be called when the users account has been setup
     * by another user or request process and all the user has left is to set their password
     * @param StateCoordinator $stateCoordinator
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendPasswordSetupForStateCoordinator(StateCoordinator $stateCoordinator) {

        $passwordSetUrl = $this->getFullyQualifiedBaseUrl().$this->router->generate(
                'set_password',
                array('token' => $stateCoordinator->getInvitationCode())
            );

        $message = (new \Swift_Message('Password Setup For State Coordinator'))
            ->setFrom($this->siteFromEmail)
            ->setTo($stateCoordinator->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/passwordSetupForStateCoordinatorEmail.html.twig',
                    ['user' => $stateCoordinator, 'passwordSetUrl' => $passwordSetUrl]
                ),
                'text/html'
            );
        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Password Setup For State Coordinator');
        $log->setToEmail($stateCoordinator->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * This function should only be called when the users account has been setup
     * by another user or request process and all the user has left is to set their password
     * @param RegionalCoordinator $regionalCoordinator
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendPasswordSetupForRegionalCoordinator(RegionalCoordinator $regionalCoordinator) {

        $passwordSetUrl = $this->getFullyQualifiedBaseUrl().$this->router->generate(
                'set_password',
                array('token' => $regionalCoordinator->getInvitationCode())
            );

        $message = (new \Swift_Message('Password Setup For Regional Coordinator'))
            ->setFrom($this->siteFromEmail)
            ->setTo($regionalCoordinator->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/passwordSetupForRegionalCoordinatorEmail.html.twig',
                    ['user' => $regionalCoordinator, 'passwordSetUrl' => $passwordSetUrl]
                ),
                'text/html'
            );
        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Password Setup For Regional Coordinator');
        $log->setToEmail($regionalCoordinator->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * This function should only be called when the users account has been setup
     * by another user or request process and all the user has left is to set their password
     * @param SchoolAdministrator $schoolAdministrator
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendPasswordSetupForSchoolAdministrator(SchoolAdministrator $schoolAdministrator) {

        $passwordSetUrl = $this->getFullyQualifiedBaseUrl().$this->router->generate(
                'set_password',
                array('token' => $schoolAdministrator->getInvitationCode())
            );

        $message = (new \Swift_Message('Password Setup For School Administrator'))
            ->setFrom($this->siteFromEmail)
            ->setTo($schoolAdministrator->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/passwordSetupForSchoolAdministratorEmail.html.twig',
                    ['user' => $schoolAdministrator, 'passwordSetUrl' => $passwordSetUrl]
                ),
                'text/html'
            );
        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Password Setup For School Administrator');
        $log->setToEmail($schoolAdministrator->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

}