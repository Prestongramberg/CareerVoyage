<?php

namespace App\Mailer;

use App\Entity\Company;
use App\Entity\EducatorRegisterStudentForCompanyExperienceRequest;
use App\Entity\EmailLog;
use App\Entity\Experience;
use App\Entity\Lesson;
use App\Entity\Request;
use App\Entity\RequestAction;
use App\Entity\RequestPossibleApprovers;
use App\Entity\School;
use App\Entity\User;
use App\Entity\UserRegisterForSchoolExperienceRequest;

/**
 * Class RequestsMailer
 *
 * @package App\Mailer
 */
class RequestsMailer extends AbstractMailer
{

    /************************************************ START NEW COMPANY ***********************************************/

    public function newCompanyApproval(User $recipient, Company $company)
    {
        $message = (new \Swift_Message('New company needs approval'))
            ->setFrom($this->siteFromEmail)
            ->setTo($recipient->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/newCompanyApproval.html.twig',
                    [
                        'recipient' => $recipient,
                        'company' => $company,
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('New company needs approval');
        $log->setToEmail($recipient->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);


        $this->entityManager->flush();
    }

    public function newCompanyAwaitingApproval(User $recipient, Company $company)
    {
        $message = (new \Swift_Message('Your company is waiting approval'))
            ->setFrom($this->siteFromEmail)
            ->setTo($recipient->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/newCompanyAwaitingApproval.html.twig',
                    [
                        'recipient' => $recipient,
                        'company' => $company,
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Your company is waiting approval');
        $log->setToEmail($recipient->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function newCompanyApproved(Company $company)
    {
        $message = (new \Swift_Message('Your company has been approved'))
            ->setFrom($this->siteFromEmail)
            ->setTo($company->getOwner()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/newCompanyApproved.html.twig',
                    [
                        'recipient' => $company->getOwner(),
                        'company' => $company,
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Your company has been approved');
        $log->setToEmail($company->getOwner()->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /************************************************ END NEW COMPANY ***********************************************/


    /************************************************ START JOIN COMPANY ***********************************************/

    /**
     * Join company request
     *
     * @param User    $recipient
     * @param User    $createdBy
     * @param Company $company
     *
     * @return void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function joinCompanyApproval(User $recipient, User $createdBy, Company $company)
    {

        $message = (new \Swift_Message("Join company request approval"))
            ->setFrom($this->siteFromEmail)
            ->setTo($recipient->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/joinCompanyApproval.html.twig',
                    [
                        'recipient' => $recipient,
                        'createdBy' => $createdBy,
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Join company request approval");
        $log->setToEmail($recipient->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * Join company request
     *
     * @param User    $recipient
     * @param Company $company
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function joinCompanyApproved(User $recipient, Company $company)
    {

        $message = (new \Swift_Message("Join company request approved"))
            ->setFrom($this->siteFromEmail)
            ->setTo($recipient->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/joinCompanyApproved.html.twig',
                    [
                        'recipient' => $recipient,
                        'company' => $company,
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Join company request approved");
        $log->setToEmail($recipient->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }



    /************************************************ END JOIN COMPANY ***********************************************/


    /************************************************ START COMPANY INVITE ***********************************************/

    /**
     * company invite request
     *
     * @param User    $recipient
     * @param User    $createdBy
     * @param Company $company
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function companyInviteApproval(User $recipient, User $createdBy, Company $company)
    {
        $message = (new \Swift_Message("Company invite approval"))
            ->setFrom($this->siteFromEmail)
            ->setTo($recipient->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/companyInviteApproval.html.twig',
                    [
                        'recipient' => $recipient,
                        'createdBy' => $createdBy,
                        'company' => $company,
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Company invite approval");
        $log->setToEmail($recipient->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }


    /**
     * Join company request
     *
     * @param User    $recipient
     * @param User    $approver
     * @param Company $company
     *
     * @return void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function companyInviteApproved(User $recipient, User $approver, Company $company)
    {
        $message = (new \Swift_Message("Company invite approved"))
            ->setFrom($this->siteFromEmail)
            ->setTo($recipient->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/companyInviteApproved.html.twig',
                    [
                        'approver' => $approver,
                        'recipient' => $recipient,
                        'company' => $company,
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Company invite approved");
        $log->setToEmail($recipient->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }


    /************************************************ END COMPANY INVITE ***********************************************/


    /************************************************ START TEACH LESSON INVITE ***********************************************/

    /**
     * Teach Lesson Request
     *
     * @param User   $recipient
     * @param User   $createdBy
     * @param Lesson $lesson
     *
     * @return bool
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function teachLessonInviteApproval(User $recipient, User $createdBy, Lesson $lesson)
    {
        $message = (new \Swift_Message("Teach topic invitation"))
            ->setFrom($this->siteFromEmail)
            ->setTo($recipient->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/teachLessonInviteApproval.html.twig',
                    [
                        'recipient' => $recipient,
                        'createdBy' => $createdBy,
                        'lesson' => $lesson,
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Teach topic invitation");
        $log->setToEmail($recipient->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * Teach Lesson Request Approval
     *
     * @param User   $recipient
     * @param User   $approver
     * @param Lesson $lesson
     *
     * @return void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function teachLessonInviteApproved(User $recipient, User $approver, Lesson $lesson)
    {
        $message = (new \Swift_Message("Teach topic invitation accepted"))
            ->setFrom($this->siteFromEmail)
            ->setTo($recipient->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/teachLessonInviteApproved.html.twig',
                    [
                        'recipient' => $recipient,
                        'approver' => $approver,
                        'lesson' => $lesson,
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Teach topic invitation accepted");
        $log->setToEmail($recipient->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * Teach Lesson Request Denied
     *
     * @param User $recipient
     * @param User $approver
     *
     * @return bool
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function teachLessonInviteDenied(User $recipient, User $approver, Lesson $lesson)
    {
        $message = (new \Swift_Message("Teach topic invitation denied"))
            ->setFrom($this->siteFromEmail)
            ->setTo($recipient->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/teachLessonInviteDenied.html.twig',
                    [
                        'recipient' => $recipient,
                        'approver' => $approver,
                        'lesson' => $lesson,
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Teach topic invitation denied");
        $log->setToEmail($recipient->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();

    }

    /************************************************ END TEACH LESSON INVITE ***********************************************/


    /************************************************ START REGISTRATION **********************************************
     *
     * @param User       $recipient
     * @param User       $createdBy
     * @param Experience $experience
     *
     * @return void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */

    public function userRegistrationApproval(User $recipient, User $createdBy, Experience $experience)
    {
        $message = (new \Swift_Message("New Registration"))
            ->setFrom($this->siteFromEmail)
            ->setTo($recipient->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/userRegistrationApproval.html.twig',
                    [
                        'recipient' => $recipient,
                        'createdBy' => $createdBy,
                        'experience' => $experience,
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("New Registration");
        $log->setToEmail($recipient->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function userRegisterationApproved(User $recipient, Experience $experience) {

        $message = (new \Swift_Message("Register User Request Approval."))
            ->setFrom($this->siteFromEmail)
            ->setTo($recipient->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/userRegistrationApproved.html.twig',
                    [
                        'recipient' => $recipient,
                        'experience' => $experience
                    ]
                ),
                'text/html'
            );
        $status  = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Register User Request Approval.");
        $log->setToEmail($recipient->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /************************************************ END REGISTRATION ***********************************************/



    /************************************************ START ONE ON ON MEETING ***********************************************/

    /**
     * Student to meet professional approval
     *
     * @param User $recipient
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function oneOnOneMeetingApproval(User $recipient)
    {
        $message = (new \Swift_Message("One on one meeting approval."))
            ->setFrom($this->siteFromEmail)
            ->setTo($recipient->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/oneOnOneMeetingApproval.html.twig',
                    ['recipient' => $recipient]
                ),
                'text/html'
            );
        $status  = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("One on one meeting approval.");
        $log->setToEmail($recipient->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /************************************************ END ONE ON ON MEETING ***********************************************/


    public function userDeregisterFromEvent(User $deregisteredUser, User $userToSendEmailTo, Experience $experience)
    {

        $subject = sprintf("User %s %s de-registered from experience %s", $deregisteredUser->getFirstName(), $deregisteredUser->getLastName(), $experience->getTitle());
        $message = (new \Swift_Message($subject))
            ->setFrom($this->siteFromEmail)
            ->setTo($userToSendEmailTo->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/experience/experienceDeregister.html.twig',
                    ['experience' => $experience, 'user' => $userToSendEmailTo, 'deregisteredUser' => $deregisteredUser]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject($subject);
        $log->setToEmail($userToSendEmailTo->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();

    }
}