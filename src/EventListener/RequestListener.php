<?php
/**
 * Created by PhpStorm.
 * User: joshcrawmer
 * Date: 2019-10-16
 * Time: 01:19
 */

namespace App\EventListener;


use App\Entity\Site;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Repository\SiteRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestListener
{

    private $entityManager;
    private $router;
    private $siteRepository;
    private $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router,  SiteRepository $siteRepository, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->siteRepository = $siteRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }
        /** @var User $user */
        if($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser()) {
            if(empty($user) || !$user instanceof User) {
                return;
            }
            if($user->isStudent()) {
                /** @var StudentUser $user */
                if($user->getGraduatingYear()) {
                    if ('graduated' === $event->getRequest()->get('_route') || $event->getRequest()->get('_route') === 'login_as_user') {
                        return;
                    }
                    if(date("Y") > (int) $user->getGraduatingYear()) {
                        $url = $this->router->generate('graduated');
                        $response = new RedirectResponse($url);
                        $event->setResponse($response);
                        return;
                    }
                }
            }
        }
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