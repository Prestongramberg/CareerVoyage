<?php

// src/Security/LoginFormAuthenticator.php
namespace App\Security;

use App\Entity\Site;
use App\Entity\User;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $entityManager;
    private $router;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $baseUrl;
    private $siteRepository;
    private $session;
    private $env;

    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder, SiteRepository $siteRepository, SessionInterface $session, $env)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->siteRepository = $siteRepository;
        $this->session = $session;
        $this->env = $env;
    }

    public function supports(Request $request)
    {

        $name = 'welcome' === $request->attributes->get('_route')
            && $request->isMethod('POST')
            && !in_array($request->request->get('formType'),
                ['educatorRegistrationForm', 'professionalRegistrationForm', 'studentRegistrationForm']);


        return $name;
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(User::class)->loadUserByUsername($credentials['email']);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // Check to make sure the user account hasn't been deleted
        if($user->getDeleted()) {
            throw new CustomUserMessageAuthenticationException('Account has been deleted.');
        }

        if(!$user->getActivated()) {
            throw new CustomUserMessageAuthenticationException('Account needs to be activated first.');
        }

        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {

        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        /** @var User $user */
        $user = $token->getUser();
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
            throw new CustomUserMessageAuthenticationException('Issue locating a site connected to user.');
        }
        
        $user->incrementLoginCount();
        $user->setLastLoginDate(new \DateTime());

        $user->initializeTemporarySecurityToken();
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // if the user is not on the correct site URL and an admin isn't logged temporarily
        // logged in as another user then redirect to our router middleware
        //$sessionData = $this->session->has('previouslyLoggedInAs')

        if($this->env !== 'dev' && $site->getFullyQualifiedBaseUrl() !== $this->getFullyQualifiedBaseUrl() && !$this->session->get('previouslyLoggedInAs', null)) {
            return new RedirectResponse($site->getFullyQualifiedBaseUrl() . sprintf('/security-router/%s', $user->getTemporarySecurityToken()));
        } else {
            // once the user is on the correct site URL let's go ahead and direct them to the appropriate place
            if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
                return new RedirectResponse($targetPath);
            } else {
                $targetPath = $this->router->generate('dashboard');
                return new RedirectResponse($targetPath);
            }
        }
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('welcome');
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