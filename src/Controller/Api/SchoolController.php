<?php

namespace App\Controller\Api;

use App\Entity\Company;
use App\Entity\CompanyFavorite;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\EducatorUser;
use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Entity\Video;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Repository\CompanyFavoriteRepository;
use App\Repository\CompanyRepository;
use App\Repository\CourseRepository;
use App\Repository\IndustryRepository;
use App\Repository\SchoolRepository;
use App\Service\FileUploader;
use App\Service\Geocoder;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
 * Class SchoolController
 * @package App\Controller
 * @Route("/api")
 */
class SchoolController extends AbstractController
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
     * @var CompanyFavoriteRepository
     */
    private $companyFavoriteRepository;

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
     * SchoolController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param Packages $assetsManager
     * @param SerializerInterface $serializer
     * @param CompanyRepository $companyRepository
     * @param IndustryRepository $industryRepository
     * @param CompanyFavoriteRepository $companyFavoriteRepository
     * @param CourseRepository $courseRepository
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
        CompanyFavoriteRepository $companyFavoriteRepository,
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
        $this->companyFavoriteRepository = $companyFavoriteRepository;
        $this->courseRepository = $courseRepository;
        $this->schoolRepository = $schoolRepository;
        $this->geocoder = $geocoder;
    }

    /**
     * @Route("/schools", name="get_schools", methods={"GET"}, options = { "expose" = true })
     */
    public function getSchools() {

        /** @var User $user */
        $user = $this->getUser();

        $schools = $this->schoolRepository->findAll();

        $json = $this->serializer->serialize($schools, 'json', ['groups' => ['RESULTS_PAGE']]);

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
     * Example Request: http://pintex.test/api/schools-by-radius?zipcode=54017
     *
     * @Route("/schools-by-radius", name="get_schools_by_radius", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getSchoolsByRadius(Request $request) {

        /** @var User $user */
        $user = $this->getUser();
        // todo how do we know the users default zipcode? Probably just return all results if zipcode is null right?
        $zipcode = $request->query->get('zipcode',  null);
        $radius = $request->query->get('radius', null);
        $regions = $request->query->get('regions', []);

        if(!empty($regions)) {
            $regions = json_decode($regions);
        }

        if($radius === 'Filter by Radius...') {
            $radius = null;
        }

        $lng = null;
        $lat = null;

        if($zipcode && $radius && $coordinates = $this->geocoder->geocode($zipcode)) {
            $lng = $coordinates['lng'];
            $lat = $coordinates['lat'];
            list($latN, $latS, $lonE, $lonW) = $this->geocoder->calculateSearchSquare($lat, $lng, $radius);
            $schools = $this->schoolRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng);
            $schoolIds = [];
            foreach($schools as $school) {
                $schoolIds[] = $school['id'];
            }
            $schools = $this->schoolRepository->getByArrayOfIds($schoolIds);
        } else {
            $schools = $this->schoolRepository->findAll();
        }

        $schoolsArray = [];
        // Filter by region if necessary
        if(!empty($regions)) {

            $schools = array_filter($schools, function(School $school) use($regions) {

                if(!$school->getRegion()) {
                    return false;
                }

                if(in_array($school->getRegion()->getId(), $regions)) {
                    return true;
                }

                return false;
            });

        } else {

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

            if($user->isProfessional()) {

                $useRegionFiltering = true;

                /** @var ProfessionalUser $user */

                foreach($user->getRegions() as $region) {

                    $regions[] = $region->getId();
                }
            }

            if($user->isStudent() || $user->isEducator()) {

                $useRegionFiltering = true;

                /** @var StudentUser|EducatorUser $user */

                if($user->getSchool() && $user->getSchool()->getRegion()) {
                    $regions[] = $user->getSchool()->getRegion()->getId();
                }
            }

            $regions = array_unique($regions);

            if($useRegionFiltering) {
                $schools = array_filter($schools, function(School $school) use($regions) {

                    if(!$school->getRegion()) {
                        return false;
                    }

                    if(in_array($school->getRegion()->getId(), $regions)) {
                        return true;
                    }

                    return false;
                });
            }
        }

        $schools = array_values($schools);

        $json = $this->serializer->serialize($schools, 'json', ['groups' => ['RESULTS_PAGE']]);
        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload
            ],
            Response::HTTP_OK
        );
    }
}