<?php

namespace App\Mailer;

use App\Entity\Company;
use App\Entity\EducatorRegisterStudentForCompanyExperienceRequest;
use App\Entity\JoinCompanyRequest;
use App\Entity\NewCompanyRequest;
use App\Entity\StudentToMeetProfessionalRequest;
use App\Entity\TeachLessonRequest;
use App\Entity\User;
use App\Entity\UserRegisterForSchoolExperienceRequest;
use App\Mailer\AbstractMailer;
use App\Repository\AdminUserRepository;

/**
 * Class RequestsMailer
 * @package App\Mailer
 */
class RequestsMailer extends AbstractMailer
{

    /**
     * Join company request
     *
     * @param JoinCompanyRequest $joinCompanyRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function joinCompanyRequestApproval(JoinCompanyRequest $joinCompanyRequest) {

        $message = (new \Swift_Message("Join Company Request Approval."))
            ->setFrom($this->siteFromEmail)
            ->setTo($joinCompanyRequest->getCreatedBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/joinCompanyRequestApproval.html.twig',
                    ['request' => $joinCompanyRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }

    public function newCompanyRequest(NewCompanyRequest $newCompanyRequest) {

        $adminUsers = $this->userRepository->findByRole(User::ROLE_ADMIN_USER);

        foreach($adminUsers as $adminUser) {
            $message = (new \Swift_Message('New Company Needs Approval!'))
                ->setFrom($this->siteFromEmail)
                ->setTo($adminUser->getEmail())
                ->setBody(
                    $this->templating->render(
                        'email/requests/newCompanyRequest.html.twig',
                        ['request' => $newCompanyRequest]
                    ),
                    'text/html'
                );

            $this->mailer->send($message);
        }
    }

    public function companyAwaitingApproval(NewCompanyRequest $newCompanyRequest) {

        $message = (new \Swift_Message('Your company is waiting approval!'))
            ->setFrom($this->siteFromEmail)
            ->setTo($newCompanyRequest->getCreatedBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/newCompanyAwaitingApproval.html.twig',
                    ['request' => $newCompanyRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    public function newCompanyRequestApproval(NewCompanyRequest $newCompanyRequest) {

        $message = (new \Swift_Message('Your company has been approved!'))
            ->setFrom($this->siteFromEmail)
            ->setTo($newCompanyRequest->getCreatedBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/newCompanyRequestApproval.html.twig',
                    ['request' => $newCompanyRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    public function educatorRegisterStudentForCompanyExperienceRequest(EducatorRegisterStudentForCompanyExperienceRequest $educatorRegisterStudentForCompanyExperienceRequest) {

        $message = (new \Swift_Message("Register Students Request."))
            ->setFrom($this->siteFromEmail)
            ->setTo($educatorRegisterStudentForCompanyExperienceRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/educatorRegisterStudentForCompanyExperienceRequest.html.twig',
                    ['request' => $educatorRegisterStudentForCompanyExperienceRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }

    public function educatorRegisterStudentForCompanyExperienceRequestApproval(EducatorRegisterStudentForCompanyExperienceRequest $educatorRegisterStudentForCompanyExperienceRequest) {
        $message = (new \Swift_Message("Register Students Request Approval."))
            ->setFrom($this->siteFromEmail)
            ->setTo($educatorRegisterStudentForCompanyExperienceRequest->getCreatedBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/educatorRegisterStudentForCompanyExperienceRequestApproval.html.twig',
                    ['request' => $educatorRegisterStudentForCompanyExperienceRequest]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }

    public function userRegisterForSchoolExperienceRequest(UserRegisterForSchoolExperienceRequest  $userRegisterForSchoolExperienceRequest) {

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
        $this->mailer->send($message);
    }

    public function userRegisterForSchoolExperienceRequestApproval(UserRegisterForSchoolExperienceRequest  $userRegisterForSchoolExperienceRequest) {

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
        $this->mailer->send($message);
    }

    /**
     * Join company request
     *
     * @param JoinCompanyRequest $joinCompanyRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function joinCompanyRequest(JoinCompanyRequest $joinCompanyRequest) {

        $message = (new \Swift_Message("Join Company Request."))
            ->setFrom($this->siteFromEmail)
            ->setTo($joinCompanyRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/joinCompanyRequest.html.twig',
                    ['request' => $joinCompanyRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * Teach Lesson Request
     *
     * @param TeachLessonRequest $teachLessonRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function teachLessonRequest(TeachLessonRequest $teachLessonRequest) {

        $message = (new \Swift_Message("Teach Lesson Request."))
            ->setFrom($this->siteFromEmail)
            ->setTo($teachLessonRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/teachLessonRequest.html.twig',
                    ['request' => $teachLessonRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * Teach Lesson Request Approval
     *
     * @param TeachLessonRequest $teachLessonRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function teachLessonRequestApproval(TeachLessonRequest $teachLessonRequest) {

        $message = (new \Swift_Message("Teach Lesson Request Approval."))
            ->setFrom($this->siteFromEmail)
            ->setTo($teachLessonRequest->getCreatedBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/teachLessonRequestApproval.html.twig',
                    ['request' => $teachLessonRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }

    /**
     * Teach Lesson Request Denied
     *
     * @param TeachLessonRequest $teachLessonRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function teachLessonRequestDenied(TeachLessonRequest $teachLessonRequest) {

        $message = (new \Swift_Message("Teach Lesson Request Denied."))
            ->setFrom($this->siteFromEmail)
            ->setTo($teachLessonRequest->getCreatedBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/teachLessonRequestDenied.html.twig',
                    ['request' => $teachLessonRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }

    /**
     * Student to meet professional approval
     *
     * @param StudentToMeetProfessionalRequest $request
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function studentToMeetProfessionalApproval(StudentToMeetProfessionalRequest $request) {
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
        $this->mailer->send($message);
    }

    /**
     * Student to meet professional approval
     *
     * @param StudentToMeetProfessionalRequest $request
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function studentToMeetProfessionalFinalDateConfirmed(StudentToMeetProfessionalRequest $request) {
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
        $this->mailer->send($message);
    }
}