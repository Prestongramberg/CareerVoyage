<?php

namespace App\Mailer;

use App\Entity\User;
use App\Entity\Experience;
use App\Mailer\AbstractMailer;
use App\Repository\AdminUserRepository;
use Psr\Log\LoggerInterface;

/**
 * Class ExperienceMailer
 * @package App\Mailer
 */
class ExperienceMailer extends AbstractMailer
{

    /**
     * Sends an email to registrants when an event has been canceled
     *
     * @param User $userToSendMessageTo
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function experienceCancellationMessage(Experience $experience, User $userToSendMessageTo, $message) {

        $message = (new \Swift_Message("An Experience you are signed up for has been cancelled."))
            ->setFrom($this->siteFromEmail)
            ->setTo($userToSendMessageTo->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/experience/experienceCancellation.html.twig',
                    ['experience' => $experience, 'message' => $message, 'user' => $userToSendMessageTo]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }

    /**
     * Sends an email to students for an event they may be interested in
     *
     * @param User $userToSendMessageTo
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function experienceForwardToStudent(Experience $experience, User $userToSendMessageTo, $message, $fromUser) {

        $routerContext = $this->router->getContext();
        $scheme = $routerContext->getScheme();
        $host = $routerContext->getHost();
        $port = $routerContext->getHttpPort();

        $url = $scheme . '://' . $host . ($port !== 80 ? ':'. $port : '');

        switch ($experience->getClassName()) {
            case 'SchoolExperience':
                $url .= $this->router->generate('school_experience_view', ['id' => $experience->getId()]);
                break;
            case 'CompanyExperience':
                $url .= $this->router->generate('company_experience_view', ['id' => $experience->getId()]);
                break;
            default:
                $url .= '';
                break;
        }

        $message = (new \Swift_Message("You have been invited to an Experience!"))
            ->setFrom($this->siteFromEmail)
            ->setTo($userToSendMessageTo->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/experience/notify_students_of_event.html.twig',
                    ['experience' => $experience, 'message' => $message, 'user' => $userToSendMessageTo, 'url' => $url, 'educator' => $fromUser]
                ),
                'text/html'
            );

        $this->mailer->send($message);

    }
}