<?php

namespace App\Mailer;

use App\Entity\Company;
use App\Entity\JoinCompanyRequest;
use App\Entity\NewCompanyRequest;
use App\Entity\RegionalCoordinatorRequest;
use App\Entity\SchoolAdministratorRequest;
use App\Entity\SiteAdminRequest;
use App\Entity\StateCoordinatorRequest;
use App\Entity\TeachLessonRequest;
use App\Entity\User;
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

    public function newCompanyRequestApproval(NewCompanyRequest $newCompanyRequest) {

        $adminUsers = $this->userRepository->findByRole(User::ROLE_ADMIN_USER);

        foreach($adminUsers as $adminUser) {
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
    }

    /**
     * Request from super admin to user to become state coordinator
     *
     * @param StateCoordinatorRequest $stateCoordinatorRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function stateCoordinatorRequest(StateCoordinatorRequest $stateCoordinatorRequest) {

        $activateUrl = $this->getFullyQualifiedBaseUrl().$this->router->generate(
                'request_activate',
                array('token' => $stateCoordinatorRequest->getActivationCode())
            );

        $message = (new \Swift_Message("State Coordinator Request."))
            ->setFrom($this->siteFromEmail)
            ->setTo($stateCoordinatorRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/stateCoordinatorRequest.html.twig',
                    ['request' => $stateCoordinatorRequest, 'activateUrl' => $activateUrl]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }

    /**
     * Request from super admin to user to become a site admin
     *
     * @param SiteAdminRequest $siteAdminRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function siteAdminRequest(SiteAdminRequest $siteAdminRequest) {

        $activateUrl = $this->getFullyQualifiedBaseUrl().$this->router->generate(
                'request_activate',
                array('token' => $siteAdminRequest->getActivationCode())
            );

        $message = (new \Swift_Message("Site Admin Request."))
            ->setFrom($this->siteFromEmail)
            ->setTo($siteAdminRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/siteAdminRequest.html.twig',
                    ['request' => $siteAdminRequest, 'activateUrl' => $activateUrl]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }

    /**
     * Request from state coordinator to user to become regional coordinator
     *
     * @param RegionalCoordinatorRequest $regionalCoordinatorRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function regionalCoordinatorRequest(RegionalCoordinatorRequest $regionalCoordinatorRequest) {

        $message = (new \Swift_Message("Regional Coordinator Request."))
            ->setFrom($this->siteFromEmail)
            ->setTo($regionalCoordinatorRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/regionalCoordinatorRequest.html.twig',
                    ['request' => $regionalCoordinatorRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }

    /**
     * Request from regional coordinator to user to become school administrator
     *
     * @param SchoolAdministratorRequest $schoolAdministratorRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function schoolAdministratorRequest(SchoolAdministratorRequest $schoolAdministratorRequest) {

        $message = (new \Swift_Message("School Administrator Request."))
            ->setFrom($this->siteFromEmail)
            ->setTo($schoolAdministratorRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/schoolAdministratorRequest.html.twig',
                    ['request' => $schoolAdministratorRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }

    /**
     * State coordinator request approval
     * @param StateCoordinatorRequest $stateCoordinatorRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function stateCoordinatorRequestApproval(StateCoordinatorRequest $stateCoordinatorRequest) {
        $message = (new \Swift_Message("State Coordinator Request Approval."))
            ->setFrom($this->siteFromEmail)
            ->setTo($stateCoordinatorRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/stateCoordinatorRequestApproval.html.twig',
                    ['request' => $stateCoordinatorRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * Regional coordinator request approval
     * @param RegionalCoordinatorRequest $regionalCoordinatorRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function regionalCoordinatorRequestApproval(RegionalCoordinatorRequest $regionalCoordinatorRequest) {

        $message = (new \Swift_Message("Regional Coordinator Request Approval."))
            ->setFrom($this->siteFromEmail)
            ->setTo($regionalCoordinatorRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/regionalCoordinatorRequestApproval.html.twig',
                    ['request' => $regionalCoordinatorRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * School administrator request approval
     * @param SchoolAdministratorRequest $schoolAdministratorRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function schoolAdministratorRequestApproval(SchoolAdministratorRequest $schoolAdministratorRequest) {

        $message = (new \Swift_Message("School Administrator Request Approval."))
            ->setFrom($this->siteFromEmail)
            ->setTo($schoolAdministratorRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/schoolAdministratorRequestApproval.html.twig',
                    ['request' => $schoolAdministratorRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * School administrator request approval
     * @param SiteAdminRequest $siteAdminRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function siteAdminRequestApproval(SiteAdminRequest $siteAdminRequest) {

        $message = (new \Swift_Message("Site Admin Request Approval."))
            ->setFrom($this->siteFromEmail)
            ->setTo($siteAdminRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/siteAdminRequestApproval.html.twig',
                    ['request' => $siteAdminRequest]
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
}