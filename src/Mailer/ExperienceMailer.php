<?php

namespace App\Mailer;

use App\Entity\EmailLog;
use App\Entity\SchoolExperience;
use App\Entity\StudentToMeetProfessionalExperience;
use App\Entity\User;
use App\Entity\Experience;
use App\Mailer\AbstractMailer;
use App\Repository\AdminUserRepository;
use App\Service\NotificationPreferencesManager;
use Psr\Log\LoggerInterface;

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
    private function experienceCancellationMessage(Experience $experience, User $userToSendMessageTo, $userMessage) {

        $message = (new \Swift_Message("An Experience you are signed up for has been cancelled."))
            ->setFrom($this->siteFromEmail)
            ->setTo($userToSendMessageTo->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/experience/experienceCancellation.html.twig',
                    ['experience' => $experience, 'message' => $userMessage, 'user' => $userToSendMessageTo]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("An Experience you are signed up for has been cancelled.");
        $log->setToEmail($userToSendMessageTo->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();



    }

    /**
     * Sends an email to students for an event they may be interested in
     *
     * @param User $userToSendMessageTo
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \ReflectionException
     */
    private function experienceForward(Experience $experience, User $userToSendMessageTo, $userMessage, $fromUser) {

        $routerContext = $this->router->getContext();
        $scheme = $routerContext->getScheme();
        $host = $routerContext->getHost();
        $port = $routerContext->getHttpPort();

        $url = $scheme . '://' . $host . ($port !== 80 ? ':'. $port : '');

        switch ($experience->getClassName()) {
            case 'SchoolExperience':
                $url .= $this->router->generate('school_experience_view', ['id' => $experience->getId()]);
                break;
            case 'CompanyExperience':
                $url .= $this->router->generate('company_experience_view', ['id' => $experience->getId()]);
                break;
            default:
                $url .= '';
                break;
        }

        $message = (new \Swift_Message("You have been invited to an Experience!"))
            ->setFrom($this->siteFromEmail)
            ->setTo($userToSendMessageTo->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/experience/notify_students_of_event.html.twig',
                    ['experience' => $experience, 'message' => $userMessage, 'user' => $userToSendMessageTo, 'url' => $url, 'educator' => $fromUser]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("You have been invited to an Experience!");
        $log->setToEmail($userToSendMessageTo->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();

    }

    private function notifyCompanyOwnerOfSchoolEvent(SchoolExperience $schoolExperience, User $user, $userMessage) {

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

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('School Event You Might Be Interested In!');
        $log->setToEmail($user->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    private function notifyProfessionalOfSchoolEvent(SchoolExperience $schoolExperience, User $user, $userMessage) {

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

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('School Event You Might Be Interested In!');
        $log->setToEmail($user->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    private function notifyTeacherOfProfessionalFeedbackForStudentMeeting(StudentToMeetProfessionalExperience $experience, User $user, $feedback) {

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

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('A Professional Has Provided Feedback On One Of Your Students');
        $log->setToEmail($user->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    private function notifyUserOfEventDateChange(Experience $experience, User $user, $message) {

        $message = (new \Swift_Message('Experience Date Changed'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/experience/notify_user_of_event_date_change.html.twig',
                    ['user' => $user, 'experience' => $experience, 'customMessage' => $message]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Experience Date Changed');
        $log->setToEmail($user->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    private function notifyUserOfEventCancellation(Experience $experience, User $user, $message) {

        $message = (new \Swift_Message('Experience Cancelled'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/experience/notify_user_of_event_cancellation.html.twig',
                    ['user' => $user, 'experience' => $experience, 'customMessage' => $message]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Experience Cancelled');
        $log->setToEmail($user->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    private function genericShareNotification($message, User $user, User $sentFrom) {

        $message = (new \Swift_Message(sprintf('%s has sent you a new message.', $sentFrom->getFullName())))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/generic_share_notification.html.twig',
                    ['user' => $user, 'customMessage' => $message, 'sent_from' => $sentFrom]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Shared with you.');
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

            if(empty($arguments[1]) || !$arguments[1] instanceof User) {
                return;
            }

            $user = $arguments[1];

            if($this->notificationPreferencesManager->isNotificationDisabled(NotificationPreferencesManager::MASK_DISABLE_EVENT_NOTIFICATION_EMAILS, $user)) {
                return;
            }

            if($this->notificationPreferencesManager->isNotificationDisabled(NotificationPreferencesManager::MASK_DISABLE_ALL_NOTIFICATION_EMAILS, $user)) {
                return;
            }

            return call_user_func_array(array($this,$method),$arguments);
        }
    }
}