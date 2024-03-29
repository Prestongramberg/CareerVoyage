<?php

namespace App\Controller\Api;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\EducatorUser;
use App\Entity\SchoolAdministrator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Form\ProfessionalEditProfileFormType;
use App\Repository\CompanyRepository;
use App\Repository\IndustryRepository;
use App\Repository\LessonRepository;
use App\Repository\ProfessionalUserRepository;
use App\Repository\EducatorUserRepository;
use App\Repository\CourseRepository;
use App\Repository\SchoolRepository;
use App\Service\FileUploader;
use App\Service\Geocoder;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
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
 * Class EducatorController
 * @package App\Controller
 * @Route("/api")
 */
class EducatorController extends AbstractController
{
    use FileHelper;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FileUploader $fileUploader
     */
    private $fileUploader;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var ImageCacheGenerator
     */
    private $imageCacheGenerator;

    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;

    /**
     * @var Packages
     */
    private $assetsManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;


    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * @var IndustryRepository
     */
    private $industryRepository;

    /**
     * @var LessonRepository
     */
    private $lessonRepository;

    /**
     * @var ProfessionalUserRepository
     */
    private $professionalUserRepository;

    /**
     * @var EducatorUserRepository
     */
    private $educatorUserRepository;

    /**
     * @var CourseRepository
     */
    private $courseRepository;

    /**
     * @var SchoolRepository
     */
    private $schoolRepository;

    /**
     * @var Geocoder
     */
    private $geocoder;

    /**
     * EducatorController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param Packages $assetsManager
     * @param SerializerInterface $serializer
     * @param CompanyRepository $companyRepository
     * @param IndustryRepository $industryRepository
     * @param LessonRepository $lessonRepository
     * @param ProfessionalUserRepository $professionalUserRepository
     * @param EducatorUserRepository $educatorUserRepository
     * @param CouseRepository $courseRepository
     * @param SchoolRepository $schoolRepository
     * @param Geocoder $geocoder
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        UserPasswordEncoderInterface $passwordEncoder,
        ImageCacheGenerator $imageCacheGenerator,
        UploaderHelper $uploaderHelper,
        Packages $assetsManager,
        SerializerInterface $serializer,
        CompanyRepository $companyRepository,
        IndustryRepository $industryRepository,
        LessonRepository $lessonRepository,
        ProfessionalUserRepository $professionalUserRepository,
        EducatorUserRepository $educatorUserRepository,
        CourseRepository $courseRepository,
        SchoolRepository $schoolRepository,
        Geocoder $geocoder
    ) {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->passwordEncoder = $passwordEncoder;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->assetsManager = $assetsManager;
        $this->serializer = $serializer;
        $this->companyRepository = $companyRepository;
        $this->industryRepository = $industryRepository;
        $this->lessonRepository = $lessonRepository;
        $this->professionalUserRepository = $professionalUserRepository;
        $this->educatorUserRepository = $educatorUserRepository;
        $this->courseRepository = $courseRepository;
        $this->schoolRepository = $schoolRepository;
        $this->geocoder = $geocoder;
    }

    /**
     * @Route("/educators", name="get_educators", methods={"GET"}, options = { "expose" = true })
     */
    public function getEducators() {

        $educators = $this->educatorUserRepository->findAll();

        $json = $this->serializer->serialize($educators, 'json', ['groups' => ['EDUCATOR_USER_DATA']]);

        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Example Request: http://pintex.test/api/educators-by-radius?zipcode=54017
     *
     * @Route("/educators-by-radius", name="get_educators_by_radius", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getEducatorsByRadius(Request $request) {

        /** @var User $user */
        $user = $this->getUser();
        // todo how do we know the users default zipcode? Probably just return all results if zipcode is null right?
        $zipcode = $request->query->get('zipcode',  null);
        $radius = $request->query->get('radius', 70);
        $lng = null;
        $lat = null;

        if($zipcode && $coordinates = $this->geocoder->geocode($zipcode)) {
            $lng = $coordinates['lng'];
            $lat = $coordinates['lat'];
            list($latN, $latS, $lonE, $lonW) = $this->geocoder->calculateSearchSquare($lat, $lng, $radius);
            $schools = $this->schoolRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng);
            $schoolIds = [];
            foreach($schools as $school) {
                $schoolIds[] = $school['id'];
            }
            $educators = $this->educatorUserRepository->findByArrayOfSchoolIds($schoolIds);
            $educatorIds = [];
            foreach($educators as $educator) {
                $educatorIds[] = $educator['id'];
            }
            $educators = $this->educatorUserRepository->getByArrayOfIds($educatorIds);
        } else {
            $educators = $this->educatorUserRepository->getAll();
        }

        $useRegionFiltering = false;
        $regions = [];
        if($user->isSchoolAdministrator()) {

            $useRegionFiltering = true;

            /** @var SchoolAdministrator $user */
            foreach($user->getSchools() as $school) {

                if(!$school->getRegion()) {
                    continue;
                }

                $regions[] = $school->getRegion()->getId();
            }
        }

        if($user->isStudent() || $user->isEducator()) {

            $useRegionFiltering = true;

            /** @var StudentUser|EducatorUser $user */

            if($user->getSchool() && $user->getSchool()->getRegion()) {
                $regions[] = $user->getSchool()->getRegion()->getId();
            }
        }

        if($user->isProfessional()) {

            $useRegionFiltering = true;

            /** @var ProfessionalUser $user */

            foreach($user->getRegions() as $region) {

                $regions[] = $region->getId();
            }
        }

        $regions = array_unique($regions);

        if($useRegionFiltering) {
            $educators = array_filter($educators, function(EducatorUser $educatorUser) use($regions) {

                if(!$educatorUser->getSchool()) {
                    return false;
                }

                if(!$educatorUser->getSchool()->getRegion()) {
                    return false;
                }

                if(in_array($educatorUser->getSchool()->getRegion()->getId(), $regions)) {
                    return true;
                }

                return false;
            });
        }

        $educators = array_values($educators);

        $json = $this->serializer->serialize($educators, 'json', ['groups' => ['EDUCATOR_USER_DATA']]);
        $educators = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $educators
            ],
            Response::HTTP_OK
        );
    }
}
