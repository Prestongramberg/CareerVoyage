<?php
/**
 * Created by PhpStorm.
 * User: joshcrawmer
 * Date: 2019-10-16
 * Time: 01:19
 */

namespace App\EventListener;

use App\Entity\HelpVideo;
use App\Entity\Site;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Entity\Video;
use App\Repository\HelpVideoRepository;
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

    protected $twig;

    private $entityManager;
    private $router;
    private $siteRepository;
    private $helpVideoRepository;
    private $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router,  SiteRepository $siteRepository, TokenStorageInterface $tokenStorage, HelpVideoRepository $helpVideoRepository, \Twig_Environment $twig)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->siteRepository = $siteRepository;
        $this->tokenStorage = $tokenStorage;
        $this->helpVideoRepository = $helpVideoRepository;
        $this->twig = $twig;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        // Load all help videos
        $default_help_videos = $this->helpVideoRepository->findBy(
            ['userRole' => 'ANY'],
            ['position' => 'ASC']
        );
        $this->twig->addGlobal('defaultHelpVideos', $default_help_videos);

        // Get help videos by user permission
        $user_help_videos['ProfessionalUser'] = $this->helpVideoRepository->findBy(['userRole' => 'ROLE_PROFESSIONAL_USER'],['position' => 'ASC']);
        $user_help_videos['EducatorUser'] = $this->helpVideoRepository->findBy(['userRole' => 'ROLE_EDUCATOR_USER'],['position' => 'ASC']);
        $user_help_videos['StudentUser'] = $this->helpVideoRepository->findBy(['userRole' => 'ROLE_STUDENT_USER'],['position' => 'ASC']);
        $user_help_videos['SchoolAdministrator'] = $this->helpVideoRepository->findBy(['userRole' => 'ROLE_SCHOOL_ADMINISTRATOR_USER'],['position' => 'ASC']);
        $user_help_videos['RegionalCoordinator'] = $this->helpVideoRepository->findBy(['userRole' => 'ROLE_REGIONAL_COORDINATOR_USER'],['position' => 'ASC']);
        $user_help_videos['StateCoordinator'] = $this->helpVideoRepository->findBy(['userRole' => 'ROLE_STATE_COORDINATOR_USER'],['position' => 'ASC']);

        $this->twig->addGlobal('userHelpVideos', $user_help_videos);

        // This is used to map the JSON to the request object
        $request = $event->getRequest();
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
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