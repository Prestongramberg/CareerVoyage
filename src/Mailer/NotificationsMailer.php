<?php

namespace App\Mailer;

use App\Entity\Experience;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\StudentToMeetProfessionalExperience;
use App\Entity\SiteAdminUser;
use App\Entity\User;
use Swift_Attachment;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class NotificationsMailer
 * @package App\Mailer
 */
class NotificationsMailer extends AbstractMailer
{

    public function notifyCompanyOwnerOfSchoolEvent(User $user, SchoolExperience $schoolExperience, $userMessage) {

        $url = $this->router->generate('school_experience_view', ['id' => $schoolExperience->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $message = (new \Swift_Message('School Event You Might Be Interested In!'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/feedback/notify_company_owner_of_school_event.html.twig',
                    ['user' => $user, 'experience' => $schoolExperience, 'url' => $url, 'message' => $userMessage]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    public function notifyProfessionalOfSchoolEvent(User $user, SchoolExperience $schoolExperience, $userMessage) {

        $url = $this->router->generate('school_experience_view', ['id' => $schoolExperience->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $message = (new \Swift_Message('School Event You Might Be Interested In!'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/feedback/notify_professional_of_school_event.html.twig',
                    ['user' => $user, 'experience' => $schoolExperience, 'url' => $url, 'message' => $userMessage]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    public function notifyTeacherOfProfessionalFeedbackForStudentMeeting(User $user, StudentToMeetProfessionalExperience $experience, $feedback) {

        $message = (new \Swift_Message('A Professional Has Provided Feedback On One Of Your Students'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/feedback/notify_educator_of_professional_feedback.html.twig',
                    ['user' => $user, 'experience' => $experience, 'feedback' => $feedback]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    public function notifyUserOfEventDateChange(User $user, Experience $experience, $message) {

        $message = (new \Swift_Message('Event Date Changed'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/experience/notify_user_of_event_date_change.html.twig',
                    ['user' => $user, 'experience' => $experience, 'customMessage' => $message]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    public function notifyUserOfEventCancellation(User $user, Experience $experience, $message) {

        $message = (new \Swift_Message('Event Cancelled'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/experience/notify_user_of_event_cancellation.html.twig',
                    ['user' => $user, 'experience' => $experience, 'customMessage' => $message]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    public function genericShareNotification(User $user, $message) {

        $message = (new \Swift_Message('Shared with you.'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/generic_share_notification.html.twig',
                    ['user' => $user, 'customMessage' => $message]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

}
