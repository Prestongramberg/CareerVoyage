<?php

namespace App\Mailer;

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
    public function requestForLessonIdeaOrSiteVisit(SchoolAdministrator $schoolAdministrator, $message) {

        $message = (new \Swift_Message('Request for lesson, idea or site visit.'))
            ->setFrom($this->siteFromEmail)
            ->setTo($schoolAdministrator->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/feedback/request_for_lesson_experience_or_site_visit.html.twig',
                    ['user' => $schoolAdministrator, 'message' => $message]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }
}
