<?php

namespace App\Mailer;

use App\Entity\EducatorUser;
use App\Entity\EmailLog;
use App\Entity\Experience;
use App\Entity\Lesson;
use App\Entity\SchoolAdministrator;
use App\Entity\SiteAdminUser;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Service\NotificationPreferencesManager;
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
     * @param User $user
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private function send($totalUnreadMessageCount, $unreadMessageCountsForUser, User $user) {
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
                        'baseUrl' => $baseUrl,
                        'user' => $user
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Unread Messages');
        $log->setToEmail($user->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();

    }

    /**
     * We need to run notification preference checks on every email call prior to actually sending any mail
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method,$arguments) {

        if(method_exists($this, $method)) {

            if(empty($arguments[2]) || !$arguments[2] instanceof User) {
                return;
            }

            $user = $arguments[2];

            if($this->notificationPreferencesManager->isNotificationDisabled(NotificationPreferencesManager::MASK_DISABLE_CHAT_NOTIFICATION_EMAILS, $user)) {
                return;
            }

            if($this->notificationPreferencesManager->isNotificationDisabled(NotificationPreferencesManager::MASK_DISABLE_ALL_NOTIFICATION_EMAILS, $user)) {
                return;
            }

            return call_user_func_array(array($this,$method),$arguments);
        }
    }
}