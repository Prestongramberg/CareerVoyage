<?php

namespace App\Controller\Api;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\CompanyPhoto;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonFavorite;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\StudentUser;
use App\Entity\SystemUser;
use App\Entity\TeachLessonExperience;
use App\Entity\TeachLessonRequest;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Repository\CompanyExperienceRepository;
use App\Repository\CompanyRepository;
use App\Repository\ExperienceRepository;
use App\Repository\IndustryRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\SchoolExperienceRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Asset\Packages;

/**
 * Class FilterController
 *
 * @package App\Controller
 * @Route("/api/filters")
 */
class FilterController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/", name="get_filters", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getFilters(Request $request)
    {
        $loggedInUser       = $this->getUser();

       /* $industries = $this->industryRepository->findAll();
        $secondaryIndustries = $this->secondaryIndustryRepository->findAll();
        $eventTypes = $this->rolesWillingToFulfillRepository->findAll();

        $json    = $this->serializer->serialize(
            $experiences, 'json', [
                            'groups' => [
                                'EXPERIENCE_DATA',
                                'ALL_USER_DATA',
                            ],
                        ]
        );
        $payload = json_decode($json, true);*/

        return new JsonResponse(
            [
                'success' => true,
                /*'data'    => $payload,*/
            ],
            Response::HTTP_OK
        );
    }
}
