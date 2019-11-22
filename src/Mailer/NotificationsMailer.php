<?php

namespace App\Mailer;

use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
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
    public function notifyCompanyOwnerOfSchoolEvent(User $user, SchoolExperience $schoolExperience) {

        $url = $this->router->generate('school_experience_view', ['id' => $schoolExperience->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $message = (new \Swift_Message('School Event You Might Be Interested In!'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/feedback/notify_company_owner_of_school_event.html.twig',
                    ['user' => $user, 'experience' => $schoolExperience, 'url' => $url]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }
}
