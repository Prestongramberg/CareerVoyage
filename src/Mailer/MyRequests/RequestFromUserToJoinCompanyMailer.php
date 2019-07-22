<?php

namespace App\Mailer\MyRequests;

use App\Entity\Company;
use App\Entity\User;
use App\Mailer\AbstractMailer;

/**
 * Class RequestFromUserToJoinCompanyMailer
 * @package App\Mailer
 */
class RequestFromUserToJoinCompanyMailer extends AbstractMailer
{

    public function send(User $user, Company $company) {

        $message = (new \Swift_Message('You have a new request from company to join it!'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/RequestsThatNeedMyApproval/requestFromCompanyToJoinCompany.html.twig',
                    ['user' => $user, 'company' => $company]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }
}