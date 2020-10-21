<?php

namespace App\Twig;

use App\Entity\CompanyResource;
use App\Entity\Company;
use App\Entity\EducatorRegisterStudentForCompanyExperienceRequest;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\JoinCompanyRequest;
use App\Entity\Lesson;
use App\Entity\NewCompanyRequest;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\Site;
use App\Entity\SiteAdminUser;
use App\Entity\StateCoordinator;
use App\Entity\StudentToMeetProfessionalRequest;
use App\Entity\StudentUser;
use App\Entity\TeachLessonRequest;
use App\Entity\User;
use App\Entity\UserRegisterForSchoolExperienceRequest;
use App\Repository\EducatorRegisterStudentForExperienceRequestRepository;
use App\Repository\ChatMessageRepository;
use App\Repository\ChatRepository;
use App\Repository\RequestRepository;
use App\Repository\SiteRepository;
use App\Repository\UserRepository;
use App\Security\ProfileVoter;
use App\Service\UploaderHelper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Environment;

class RoutingExtension extends AbstractExtension
{
    /**
     * @var string
     */
    private $baseHost;

    /**
     * @var string
     */
    private $baseScheme;

    /**
     * @var UrlGeneratorInterface $generator
     */
    private $generator;

    /**
     * RoutingExtension constructor.
     * @param string $baseHost
     * @param string $baseScheme
     * @param UrlGeneratorInterface $generator
     */
    public function __construct(string $baseHost, string $baseScheme, UrlGeneratorInterface $generator)
    {
        $this->baseHost = $baseHost;
        $this->baseScheme = $baseScheme;
        $this->generator = $generator;
    }


    public function getFunctions(): array
    {
        return [
            new TwigFunction('email_path', [$this, 'getEmailPath'])
        ];
    }

    public function getEmailPath($name, $parameters = [], $relative = false)
    {
        return $this->baseScheme . '://' . $this->baseHost . $this->generator->generate($name, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }
}