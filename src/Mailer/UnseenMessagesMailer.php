<?php

namespace App\Mailer;

use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\Lesson;
use App\Entity\SchoolAdministrator;
use App\Entity\SiteAdminUser;
use App\Entity\StudentUser;
use App\Entity\User;
use Swift_Attachment;

/**
 * Class UnseenMessagesMailer
 * @package App\Mailer
 */
class UnseenMessagesMailer extends AbstractMailer
{
    /**
     * @param User $user
     * @param array $chatMessages
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function send(User $user, $chatMessages = []) {

        $message = (new \Swift_Message('Unread Messages'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/unseenMessages/index.html.twig',
                    ['user' => $user, 'chatMessages' => $chatMessages]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }
}