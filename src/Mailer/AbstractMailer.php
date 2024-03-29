<?php

namespace App\Mailer;

use App\Repository\UserRepository;
use App\Service\NotificationPreferencesManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * Class AbstractMailer
 * @package App\Mailer
 */
class AbstractMailer
{
    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var Environment
     */
    protected $templating;

    /**
     * @var string
     */
    protected $siteFromEmail;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var NotificationPreferencesManager $notificationPreferencesManager
     */
    protected $notificationPreferencesManager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $baseHost;

    /**
     * @var string
     */
    protected $baseScheme;

    /**
     * AbstractMailer constructor.
     *
     * @param Swift_Mailer                   $mailer
     * @param RouterInterface                $router
     * @param Environment                    $templating
     * @param string                         $siteFromEmail
     * @param UserRepository                 $userRepository
     * @param NotificationPreferencesManager $notificationPreferencesManager
     * @param EntityManagerInterface         $entityManager
     * @param string                         $baseHost
     * @param string                         $baseScheme
     */
    public function __construct(
        Swift_Mailer $mailer, RouterInterface $router, Environment $templating, string $siteFromEmail,
        UserRepository $userRepository, NotificationPreferencesManager $notificationPreferencesManager,
        EntityManagerInterface $entityManager, string $baseHost, string $baseScheme
    ) {
        $this->mailer                         = $mailer;
        $this->router                         = $router;
        $this->templating                     = $templating;
        $this->siteFromEmail                  = $siteFromEmail;
        $this->userRepository                 = $userRepository;
        $this->notificationPreferencesManager = $notificationPreferencesManager;
        $this->entityManager                  = $entityManager;
        $this->baseHost                       = $baseHost;
        $this->baseScheme                     = $baseScheme;
    }

    /**
     * Generate the fully qualified base URL (scheme + host + port, if not default + app base path)
     *
     * @return string
     */
    protected function getFullyQualifiedBaseUrl()
    {
        $routerContext = $this->router->getContext();
        $port = $routerContext->getHttpPort();

        return sprintf(
            '%s://%s%s%s',
            $routerContext->getScheme(),
            $routerContext->getHost(),
            ($port !== 80 ? ':'.$port : ''),
            $routerContext->getBaseUrl()
        );
    }
}