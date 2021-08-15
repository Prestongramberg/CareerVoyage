<?php

namespace App\Mailer;

use App\Entity\Company;
use App\Entity\EducatorRegisterStudentForCompanyExperienceRequest;
use App\Entity\EmailLog;
use App\Entity\Experience;
use App\Entity\Request;
use App\Entity\RequestPossibleApprovers;
use App\Entity\StudentToMeetProfessionalRequest;
use App\Entity\TeachLessonRequest;
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

    public function newCompanyApproval(Request $request, Company $company)
    {

        $skip = (
            $request->getRequestType() !== Request::REQUEST_TYPE_NEW_COMPANY
        );

        if($skip) {
            return false;
        }

        /** @var RequestPossibleApprovers $possibleApprover */
        foreach ($request->getRequestPossibleApprovers() as $possibleApprover) {

            $recipient = $possibleApprover->getPossibleApprover();

            $skipSendingEmail = (
                !$recipient ||
                !$possibleApprover->getPossibleApprover()->getEmail()
            );

            if($skipSendingEmail) {
                continue;
            }

            $recipient = $possibleApprover->getPossibleApprover();

            $message = (new \Swift_Message('New Company Needs Approval'))
                ->setFrom($this->siteFromEmail)
                ->setTo($recipient->getEmail())
                ->setBody(
                    $this->templating->render(
                        'email/requests/newCompanyApproval.html.twig',
                        [
                            'recipientFirstName' => $recipient->getFirstName(),
                            'company' => $company
                        ]
                    ),
                    'text/html'
                );

            $status = $this->mailer->send($message);

            $log = new EmailLog();
            $log->setFromEmail($this->siteFromEmail);
            $log->setSubject('New Company Needs Approval!');
            $log->setToEmail($recipient->getEmail());
            $log->setStatus($status);
            $log->setBody($message->getBody());

            $this->entityManager->persist($log);
        }

        $this->entityManager->flush();

        return true;
    }

    public function newCompanyAwaitingApproval(Request $request, Company $company)
    {
        $skip = (
            $request->getRequestType() !== Request::REQUEST_TYPE_NEW_COMPANY ||
            !$request->getCreatedBy() ||
            !$request->getCreatedBy()->getEmail() ||
            !$request->getCreatedBy()->getFirstName()
        );

        if($skip) {
            return false;
        }

        $message = (new \Swift_Message('Your company is waiting approval'))
            ->setFrom($this->siteFromEmail)
            ->setTo($request->getCreatedBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/newCompanyAwaitingApproval.html.twig',
                    [
                        'request' => $request,
                        'recipientFirstName' => $request->getCreatedBy()->getFirstName(),
                        'company' => $company
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Your company is waiting approval!');
        $log->setToEmail($request->getCreatedBy()->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return true;
    }

    public function newCompanyApproved(Company $company)
    {
        $skip = (
            !$company->getOwner() ||
            !$company->getOwner()->getEmail() ||
            !$company->getOwner()->getFirstName()
        );

        if($skip) {
            return false;
        }

        $message = (new \Swift_Message('Your company has been approved'))
            ->setFrom($this->siteFromEmail)
            ->setTo($company->getOwner()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/newCompanyApproved.html.twig',
                    [
                        'recipientFirstName' => $company->getOwner()->getFirstName(),
                        'company' => $company
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject('Your company has been approved!');
        $log->setToEmail($company->getOwner()->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return true;
    }

    /************************************************ END NEW COMPANY ***********************************************/



    /* START JOIN COMPANY */



    /* END JOIN COMPANY */




    /* START COMPANY INVITE */



    /* END COMPANY INVITE */



    /* START TEACH LESSON */



    /* END TEACH LESSON */


    /**
     * Join company request
     *
     * @param Request $request
     *
     * @return bool
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function joinCompanyRequestApproval(Request $request, Company $company)
    {

        if(!$request->getCreatedBy() || !$request->getCreatedBy()->getEmail()) {
            return false;
        }

        $message = (new \Swift_Message("Join Company Request Approval."))
            ->setFrom($this->siteFromEmail)
            ->setTo($request->getCreatedBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/joinCompanyRequestApproval.html.twig',
                    [
                        'request' => $request,
                        'recipientFirstName' => $request->getCreatedBy()->getFirstName(),
                        'company' => $company
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Join Company Request Approval.");
        $log->setToEmail($request->getCreatedBy()->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Join company request
     *
     * @param Request $request
     *
     * @return bool
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function companyInviteApproval(Request $request, Company $company)
    {

        if(!$request->getCreatedBy() || !$request->getCreatedBy()->getEmail()) {
            return false;
        }

        $message = (new \Swift_Message("Join Company Request Approval."))
            ->setFrom($this->siteFromEmail)
            ->setTo($request->getCreatedBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/joinCompanyRequestApproval.html.twig',
                    [
                        'request' => $request,
                        'recipientFirstName' => $request->getCreatedBy()->getFirstName(),
                        'company' => $company
                    ]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Join Company Request Approval.");
        $log->setToEmail($request->getCreatedBy()->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return true;
    }

    /**
     * company invite request
     *
     * @param Request $request
     * @param Company $company
     *
     * @return bool
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function companyInviteRequest(Request $request, Company $company)
    {
        $skipSendingEmail = (
            $request->getRequestType() !== Request::REQUEST_TYPE_JOIN_COMPANY ||
            !$request->getCreatedBy()
        );

        if($skipSendingEmail) {
            return false;
        }

        /** @var RequestPossibleApprovers $possibleApprover */
        foreach ($request->getRequestPossibleApprovers() as $possibleApprover) {

            $skipSendingEmail = (
                !$possibleApprover->getPossibleApprover() ||
                !$possibleApprover->getPossibleApprover()->getEmail()
            );

            if ($skipSendingEmail) {
                continue;
            }

            $message = (new \Swift_Message("Join Company Request."))
                ->setFrom($this->siteFromEmail)
                ->setTo($possibleApprover->getPossibleApprover()->getEmail())
                ->setBody(
                    $this->templating->render(
                        'email/requests/joinCompanyRequest.html.twig',
                        [
                            'request' => $request,
                            'recipient' => $possibleApprover->getPossibleApprover(),
                            'createdBy' => $request->getCreatedBy()
                        ]
                    ),
                    'text/html'
                );

            $status = $this->mailer->send($message);

            $log = new EmailLog();
            $log->setFromEmail($this->siteFromEmail);
            $log->setSubject("Join Company Request.");
            $log->setToEmail($possibleApprover->getPossibleApprover()->getEmail());
            $log->setStatus($status);
            $log->setBody($message->getBody());

            $this->entityManager->persist($log);
            $this->entityManager->flush();

        }

        return true;
    }

    public function educatorRegisterStudentForCompanyExperienceRequest(EducatorRegisterStudentForCompanyExperienceRequest $educatorRegisterStudentForCompanyExperienceRequest
    ) {

        $message = (new \Swift_Message("Register Student Request."))
            ->setFrom($this->siteFromEmail)
            ->setTo($educatorRegisterStudentForCompanyExperienceRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/educatorRegisterStudentForCompanyExperienceRequest.html.twig',
                    ['request' => $educatorRegisterStudentForCompanyExperienceRequest]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Register Student Request.");
        $log->setToEmail($educatorRegisterStudentForCompanyExperienceRequest->getNeedsApprovalBy()->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();

    }

    public function educatorRegisterStudentForCompanyExperienceRequestApproval(EducatorRegisterStudentForCompanyExperienceRequest $educatorRegisterStudentForCompanyExperienceRequest
    ) {
        $message = (new \Swift_Message("Register Student Request Approval."))
            ->setFrom($this->siteFromEmail)
            ->setTo($educatorRegisterStudentForCompanyExperienceRequest->getCreatedBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/educatorRegisterStudentForCompanyExperienceRequestApproval.html.twig',
                    ['request' => $educatorRegisterStudentForCompanyExperienceRequest]
                ),
                'text/html'
            );
        $status  = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Register Student Request Approval.");
        $log->setToEmail($educatorRegisterStudentForCompanyExperienceRequest->getCreatedBy()->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }


    public function educatorRegisterStudentForCompanyExperienceRequestApprovalEmailForStudent(EducatorRegisterStudentForCompanyExperienceRequest $educatorRegisterStudentForCompanyExperienceRequest
    ) {
        $message = (new \Swift_Message("You've Been Registered For a Company Experience."))
            ->setFrom($this->siteFromEmail)
            ->setTo($educatorRegisterStudentForCompanyExperienceRequest->getStudentUser()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/educatorRegisterStudentForCompanyExperienceRequestApprovalEmailForStudent.html.twig',
                    ['request' => $educatorRegisterStudentForCompanyExperienceRequest]
                ),
                'text/html'
            );
        $status  = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("You've Been Registered For a Company Experience.");
        $log->setToEmail($educatorRegisterStudentForCompanyExperienceRequest->getStudentUser()->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function userRegisterForSchoolExperienceRequest(UserRegisterForSchoolExperienceRequest $userRegisterForSchoolExperienceRequest
    ) {

        $message = (new \Swift_Message("Register User Request."))
            ->setFrom($this->siteFromEmail)
            ->setTo($userRegisterForSchoolExperienceRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/userRegisterForSchoolExperienceRequest.html.twig',
                    ['request' => $userRegisterForSchoolExperienceRequest]
                ),
                'text/html'
            );
        $status  = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Register User Request.");
        $log->setToEmail($userRegisterForSchoolExperienceRequest->getNeedsApprovalBy()->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function userRegisterForSchoolExperienceRequestApproval(UserRegisterForSchoolExperienceRequest $userRegisterForSchoolExperienceRequest
    ) {

        $message = (new \Swift_Message("Register User Request Approval."))
            ->setFrom($this->siteFromEmail)
            ->setTo($userRegisterForSchoolExperienceRequest->getCreatedBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/userRegisterForSchoolExperienceRequestApproval.html.twig',
                    ['request' => $userRegisterForSchoolExperienceRequest]
                ),
                'text/html'
            );
        $status  = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Register User Request Approval.");
        $log->setToEmail($userRegisterForSchoolExperienceRequest->getCreatedBy()->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * Join company request
     *
     * @param Request $request
     * @param Company $company
     *
     * @return bool
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function joinCompanyRequest(Request $request, Company $company)
    {
        $skipSendingEmail = (
            $request->getRequestType() !== Request::REQUEST_TYPE_JOIN_COMPANY ||
            !$request->getCreatedBy()
        );

        if($skipSendingEmail) {
            return false;
        }

        /** @var RequestPossibleApprovers $possibleApprover */
        foreach ($request->getRequestPossibleApprovers() as $possibleApprover) {

            $skipSendingEmail = (
                !$possibleApprover->getPossibleApprover() ||
                !$possibleApprover->getPossibleApprover()->getEmail()
            );

            if ($skipSendingEmail) {
                continue;
            }

            $message = (new \Swift_Message("Join Company Request."))
                ->setFrom($this->siteFromEmail)
                ->setTo($possibleApprover->getPossibleApprover()->getEmail())
                ->setBody(
                    $this->templating->render(
                        'email/requests/joinCompanyRequest.html.twig',
                        [
                            'request' => $request,
                            'recipient' => $possibleApprover->getPossibleApprover(),
                            'createdBy' => $request->getCreatedBy()
                        ]
                    ),
                    'text/html'
                );

            $status = $this->mailer->send($message);

            $log = new EmailLog();
            $log->setFromEmail($this->siteFromEmail);
            $log->setSubject("Join Company Request.");
            $log->setToEmail($possibleApprover->getPossibleApprover()->getEmail());
            $log->setStatus($status);
            $log->setBody($message->getBody());

            $this->entityManager->persist($log);
            $this->entityManager->flush();

        }



        return true;
    }

    /**
     * Teach Lesson Request
     *
     * @param TeachLessonRequest $teachLessonRequest
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function teachLessonRequest(Request $teachLessonRequest)
    {

        $message = (new \Swift_Message("Teach Topic Request."))
            ->setFrom($this->siteFromEmail)
            ->setTo($teachLessonRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/teachLessonRequest.html.twig',
                    ['request' => $teachLessonRequest]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Teach Topic Request.");
        $log->setToEmail($teachLessonRequest->getNeedsApprovalBy()->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * Teach Lesson Request Approval
     *
     * @param TeachLessonRequest $teachLessonRequest
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function teachLessonRequestApproval(TeachLessonRequest $teachLessonRequest)
    {

        $message = (new \Swift_Message("Teach Topic Request Approval."))
            ->setFrom($this->siteFromEmail)
            ->setTo($teachLessonRequest->getCreatedBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/teachLessonRequestApproval.html.twig',
                    ['request' => $teachLessonRequest]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Teach Topic Request Approval.");
        $log->setToEmail($teachLessonRequest->getCreatedBy()->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();

    }

    /**
     * Teach Lesson Request Denied
     *
     * @param TeachLessonRequest $teachLessonRequest
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function teachLessonRequestDenied(TeachLessonRequest $teachLessonRequest)
    {

        $message = (new \Swift_Message("Teach Topic Request Denied."))
            ->setFrom($this->siteFromEmail)
            ->setTo($teachLessonRequest->getCreatedBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/teachLessonRequestDenied.html.twig',
                    ['request' => $teachLessonRequest]
                ),
                'text/html'
            );

        $status = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Teach Topic Request Denied.");
        $log->setToEmail($teachLessonRequest->getCreatedBy()->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();

    }

    /**
     * Student to meet professional approval
     *
     * @param StudentToMeetProfessionalRequest $request
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function studentToMeetProfessionalApproval(StudentToMeetProfessionalRequest $request)
    {
        $message = (new \Swift_Message("Student To Meet Professional Approval."))
            ->setFrom($this->siteFromEmail)
            ->setTo($request->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/studentToMeetProfessional.html.twig',
                    ['request' => $request]
                ),
                'text/html'
            );
        $status  = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Student To Meet Professional Approval.");
        $log->setToEmail($request->getNeedsApprovalBy()->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * Student to meet professional approval
     *
     * @param StudentToMeetProfessionalRequest $request
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function studentToMeetProfessionalFinalDateConfirmed(StudentToMeetProfessionalRequest $request)
    {
        $message = (new \Swift_Message("Student To Meet Final Date Approved."))
            ->setFrom($this->siteFromEmail)
            ->setTo($request->getProfessional()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/studentToMeetProfessionalFinalDateConfirmed.html.twig',
                    ['request' => $request]
                ),
                'text/html'
            );
        $status  = $this->mailer->send($message);

        $log = new EmailLog();
        $log->setFromEmail($this->siteFromEmail);
        $log->setSubject("Student To Meet Final Date Approved.");
        $log->setToEmail($request->getProfessional()->getEmail());
        $log->setStatus($status);
        $log->setBody($message->getBody());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

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