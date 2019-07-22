<?php

namespace App\Mailer\MyRequests;

use App\Entity\Company;
use App\Entity\User;
use App\Mailer\AbstractMailer;

/**
 * Class NewCompanyApprovedMailer
 * @package App\Mailer
 */
class NewCompanyApprovedMailer extends AbstractMailer
{

    public function send(User $user, Company $company) {

        $message = (new \Swift_Message('Company approved!'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/myRequests/newCompanyApproved.html.twig',
                    ['user' => $user, 'company' => $company]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }
}