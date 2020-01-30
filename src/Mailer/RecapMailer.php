<?php

namespace App\Mailer;

use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\Lesson;
use App\Entity\SchoolAdministrator;
use App\Entity\SiteAdminUser;
use App\Entity\StudentUser;
use App\Entity\User;
use Swift_Attachment;

/**
 * Class RecapMailer
 * @package App\Mailer
 */
class RecapMailer extends AbstractMailer
{
    /**
     * @param User $user
     * @param $lessons Lesson[]
     * @param array $schoolExperiences
     * @param array $companyExperiences
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function send(User $user, $lessons = [], $schoolExperiences = [], $companyExperiences = []) {

        $subject = 'Weekly Recap';
        if($user->isStudent() || $user->isEducator()) {
            /** @var StudentUser $user */
            $subject = sprintf("%s Weekly Recap", ucwords($user->getSite()->getName()));
        } elseif ($user->isProfessional()) {
            $subject = 'PINTEX Weekly Recap';
        }

        $message = (new \Swift_Message($subject))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/recap/index.html.twig',
                    ['user' => $user, 'lessons' => $lessons, 'schoolExperiences' => $schoolExperiences, 'companyExperiences' => $companyExperiences]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }
}