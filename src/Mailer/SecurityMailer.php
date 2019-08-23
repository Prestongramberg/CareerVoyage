<?php

namespace App\Mailer;

use App\Entity\SiteAdminUser;
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

        $this->mailer->send($message);
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
        $this->mailer->send($message);
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
        $this->mailer->send($message);
    }


}