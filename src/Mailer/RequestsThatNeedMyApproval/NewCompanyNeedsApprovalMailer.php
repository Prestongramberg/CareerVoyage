<?php

namespace App\Mailer\RequestsThatNeedMyApproval;

use App\Entity\Company;
use App\Entity\User;
use App\Mailer\AbstractMailer;
use App\Repository\AdminUserRepository;
use Swift_Mailer;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * Class NewCompanyNeedsApprovalMailer
 * @package App\Mailer
 */
class NewCompanyNeedsApprovalMailer extends AbstractMailer
{

    /**
     * @var AdminUserRepository
     */
    private $adminUserRepository;

    public function __construct(Swift_Mailer $mailer, RouterInterface $router, Environment $templating, $siteFromEmail, AdminUserRepository $adminUserRepository)
    {
        $this->adminUserRepository = $adminUserRepository;

        parent::__construct($mailer, $router, $templating, $siteFromEmail);
    }

    public function send(User $user, Company $company) {

        $adminUsers = $this->adminUserRepository->findAll();

        foreach($adminUsers as $adminUser) {
            $message = (new \Swift_Message('New Company Needs Approval!'))
                ->setFrom($this->siteFromEmail)
                ->setTo($adminUser->getEmail())
                ->setBody(
                    $this->templating->render(
                        'email/RequestsThatNeedMyApproval/newCompanyNeedsApproval.html.twig',
                        ['user' => $user, 'company' => $company]
                    ),
                    'text/html'
                );

            $this->mailer->send($message);
        }
    }
}