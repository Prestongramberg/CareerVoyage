<?php

namespace App\Mailer;

use App\Entity\EmailLog;
use App\Entity\SchoolAdministrator;
use App\Entity\SiteAdminUser;
use App\Entity\User;
use Swift_Attachment;

/**
 * Class FeedbackMailer
 * @package App\Mailer
 */
class FeedbackMailer extends AbstractMailer
{
    public function requestForLessonIdeaOrSiteVisit(User $user, $message, $from) {

        $message = (new \Swift_Message('Request for topic, idea or site visit.'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/feedback/request_for_lesson_experience_or_site_visit.html.twig',
                    ['user' => $user, 'message' => $message, 'from' => $from]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($from);
        $log->setSubject('Request for topic, idea or site visit.');
        $log->setToEmail($user->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function requestForNewCourseToBeAddedToSystem(User $user, $message, $from) {

        $message = (new \Swift_Message('Request for new course to be added to the system.'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/feedback/request_for_new_course.html.twig',
                    ['user' => $user, 'message' => $message, 'from' => $from]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($from);
        $log->setSubject('Request for new course to be added to the system.');
        $log->setToEmail($user->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
