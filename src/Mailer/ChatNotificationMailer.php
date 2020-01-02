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
class ChatNotificationMailer extends AbstractMailer
{
    /**
     * @param $totalUnreadMessageCount
     * @param $unreadMessageCountsForUser
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function send($totalUnreadMessageCount, $unreadMessageCountsForUser) {
        $context = $this->router->getContext();
        $context->setHost($this->baseHost);
        $context->setScheme($this->baseScheme);
        $context->setBaseUrl('');
        $baseUrl = $this->getFullyQualifiedBaseUrl();

        $message = (new \Swift_Message('Unread Messages'))
            ->setFrom($this->siteFromEmail)
            ->setTo($totalUnreadMessageCount['user_sent_to_email'])
            ->setBody(
                $this->templating->render(
                    'email/chatNotificationEmail.html.twig',
                    [
                        'totalUnreadMessageCount' => $totalUnreadMessageCount,
                        'unreadMessageCountsForUser' => $unreadMessageCountsForUser,
                        'baseUrl' => $baseUrl
                    ]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }
}