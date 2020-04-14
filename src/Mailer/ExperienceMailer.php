<?php

namespace App\Mailer;

use App\Entity\User;
use App\Entity\Experience;
use App\Mailer\AbstractMailer;
use App\Repository\AdminUserRepository;

/**
 * Class ExperienceMailer
 * @package App\Mailer
 */
class ExperienceMailer extends AbstractMailer
{

    /**
     * Sends an email to registrants when an event has been canceled
     *
     * @param User $userToSendMessageTo
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function experienceCancellationMessage(Experience $experience, User $userToSendMessageTo, $message) {

        $message = (new \Swift_Message("An Experience you are signed up for has been cancelled."))
            ->setFrom($this->siteFromEmail)
            ->setTo($userToSendMessageTo->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/experience/experienceCancellation.html.twig',
                    ['experience' => $experience, 'message' => $message, 'user' => $userToSendMessageTo]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }
}