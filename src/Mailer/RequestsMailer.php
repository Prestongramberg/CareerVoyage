<?php

namespace App\Mailer;

use App\Entity\Company;
use App\Entity\JoinCompanyRequest;
use App\Entity\NewCompanyRequest;
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
     * @param JoinCompanyRequest $joinCompanyRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function userToCompanyApproval(JoinCompanyRequest $joinCompanyRequest) {

        $message = (new \Swift_Message("Your request to join {$joinCompanyRequest->getCompany()->getName()} has been approved!"))
            ->setFrom($this->siteFromEmail)
            ->setTo($joinCompanyRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/userToCompanyApproved.html.twig',
                    ['request' => $joinCompanyRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }

    /**
     * @param JoinCompanyRequest $joinCompanyRequest
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function userToCompanyRequest(JoinCompanyRequest $joinCompanyRequest) {

        $message = (new \Swift_Message("You have a new request from {$joinCompanyRequest->getCreatedBy()->getFullName()} to join your company!"))
            ->setFrom($this->siteFromEmail)
            ->setTo($joinCompanyRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/userToCompany.html.twig',
                    ['request' => $joinCompanyRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }

    public function companyToUserRequest(JoinCompanyRequest $joinCompanyRequest) {

        $message = (new \Swift_Message("You have a new request from {$joinCompanyRequest->getCompany()->getName()} to join their company page!"))
            ->setFrom($this->siteFromEmail)
            ->setTo($joinCompanyRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/companyToUser.html.twig',
                    ['request' => $joinCompanyRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }


    public function companyToUserApproval(JoinCompanyRequest $joinCompanyRequest) {

        $message = (new \Swift_Message("Your request for {$joinCompanyRequest->getNeedsApprovalBy()->getFullName()} to join your company page has been accepted!"))
            ->setFrom($this->siteFromEmail)
            ->setTo($joinCompanyRequest->getNeedsApprovalBy()->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/requests/companyToUserApproved.html.twig',
                    ['request' => $joinCompanyRequest]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }

    public function newCompanyNeedsApproval(NewCompanyRequest $newCompanyRequest) {

        $adminUsers = $this->userRepository->findByRole(User::ROLE_ADMIN_USER);

        foreach($adminUsers as $adminUser) {
            $message = (new \Swift_Message('New Company Needs Approval!'))
                ->setFrom($this->siteFromEmail)
                ->setTo($adminUser->getEmail())
                ->setBody(
                    $this->templating->render(
                        'email/requests/newCompanyNeedsApproval.html.twig',
                        ['request' => $newCompanyRequest]
                    ),
                    'text/html'
                );

            $this->mailer->send($message);
        }
    }

    public function newCompanyApproved(NewCompanyRequest $newCompanyRequest) {

        $adminUsers = $this->userRepository->findByRole(User::ROLE_ADMIN_USER);

        foreach($adminUsers as $adminUser) {
            $message = (new \Swift_Message('Your company has been approved!'))
                ->setFrom($this->siteFromEmail)
                ->setTo($newCompanyRequest->getCreatedBy()->getEmail())
                ->setBody(
                    $this->templating->render(
                        'email/requests/newCompanyApproved.html.twig',
                        ['request' => $newCompanyRequest]
                    ),
                    'text/html'
                );

            $this->mailer->send($message);
        }
    }
}