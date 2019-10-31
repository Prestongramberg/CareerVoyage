<?php
/**
 * Created by PhpStorm.
 * User: joshcrawmer
 * Date: 2019-10-16
 * Time: 01:19
 */

namespace App\EventListener;


use App\Entity\Site;
use App\Entity\User;
use App\Repository\SiteRepository;
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
        // todo come back to this and lock down the URLs on the site
        return;

        $route = $event->getRequest()->get('_route');

        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        if(!$this->tokenStorage->getToken() || !$this->tokenStorage->getToken()->getUser()) {
            return;
        }

        if($route === 'welcome' && $this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User) {
            $response = new RedirectResponse($this->router->generate('sign_out'));
            $event->setResponse($response);
            return;
        }

        if($route === 'security_router' || $route === 'sign_out' || $route === 'welcome') {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();

        if($user && $user instanceof User) {

            $site = null;
            if($user->isProfessional() || $user->isAdmin()) {
                /** @var Site $site */
                $site = $this->siteRepository->findOneBy([
                    'parentSite' => 1
                ]);
            } else if($user->isSiteAdmin()
                || $user->isStateCoordinator()
                || $user->isRegionalCoordinator()
                || $user->isSchoolAdministrator()
                || $user->isStudent() ||
                $user->isEducator()){
                /** @var Site $site */
                $site = $user->getSite();
            }

            if(!$site) {
                throw new \Exception('Issue locating a site connected to user.');
            }

            // if the user is not on the correct site URL then redirect to our router middleware
            if($site->getFullyQualifiedBaseUrl() !== $this->getFullyQualifiedBaseUrl()) {

                $response = new RedirectResponse(
                    $site->getFullyQualifiedBaseUrl() . $this->router->generate($route)
                );

                $event->setResponse($response);
            }
        }
        return;
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