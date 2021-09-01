<?php

namespace App\Controller\Api;

use App\Entity\Company;
use App\Entity\CompanyFavorite;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\EducatorUser;
use App\Entity\Image;
use App\Entity\ProfessionalUser;
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
 * Class CompanyController
 * @package App\Controller
 * @Route("/api")
 */
class CompanyController extends AbstractController
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
     * @var Geocoder
     */
    private $geocoder;

    /**
     * CompanyController constructor.
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
        $this->geocoder = $geocoder;
    }

    /**
     * @Route("/companies", name="get_companies", methods={"GET"}, options = { "expose" = true })
     */
    public function getCompanies() {

        /** @var User $user */
        $user = $this->getUser();

        $companies = $this->companyRepository->findBy([
            'approved' => true
        ]);

        foreach($companies as $company) {

            $favoriteCompany = $this->companyFavoriteRepository->findOneBy([
                'company' => $company,
                'user' => $user
            ]);

            if($favoriteCompany) {
                $company->setIsFavorite(true);
            } else {
                $company->setIsFavorite(false);
            }

            if($user->isProfessional() && $user->getCompany() && $user->getCompany()->getId() === $company->getId()) {
                $company->setIsMine(true);
            } else {
                $company->setIsMine(false);
            }
        }

        $json = $this->serializer->serialize($companies, 'json', ['groups' => ['RESULTS_PAGE']]);

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
     * Example Request: http://pintex.test/api/companies-by-radius?zipcode=54017
     *
     * @Route("/companies-by-radius", name="get_companies_by_radius", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException*@throws \Doctrine\DBAL\Driver\Exception
     */
    public function getCompaniesByRadius(Request $request) {

        /** @var User $user */
        $user = $this->getUser();
        // todo how do we know the users default zipcode? Probably just return all results if zipcode is null right?
        $zipcode = $request->query->get('zipcode',  null);
        $radius = $request->query->get('radius', 70);
        $lng = null;
        $lat = null;

        if($zipcode &&  $coordinates = $this->geocoder->geocode($zipcode)) {
            $lng = $coordinates['lng'];
            $lat = $coordinates['lat'];
            list($latN, $latS, $lonE, $lonW) = $this->geocoder->calculateSearchSquare($lat, $lng, $radius);
            $companies = $this->companyRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng);
            $companyIds = [];
            foreach($companies as $company) {
                $companyIds[] = $company['id'];
            }
            $companies = $this->companyRepository->getByArrayOfIds($companyIds);
        } else {
            $companies = $this->companyRepository->findBy([
                'approved' => true
            ]);
        }

        foreach($companies as $company) {

            $favoriteCompany = $this->companyFavoriteRepository->findOneBy([
                'company' => $company,
                'user' => $user
            ]);

            if($favoriteCompany) {
                $company->setIsFavorite(true);
            } else {
                $company->setIsFavorite(false);
            }

            if($user->isProfessional() && $user->getCompany() && $user->getCompany()->getId() === $company->getId()) {
                $company->setIsMine(true);
            } else {
                $company->setIsMine(false);
            }
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
            $companies = array_filter($companies, function(Company $company) use($regions) {

                if($company->getRegions()->count() === 0) {
                    return false;
                }

                foreach($company->getRegions() as $region) {
                    if(in_array($region->getId(), $regions)) {
                        return true;
                    }
                }

                return false;
            });
        }

        $companies = array_values($companies);

        $json = $this->serializer->serialize($companies, 'json', ['groups' => ['RESULTS_PAGE']]);
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
     * @Route("/industries", name="get_industries", methods={"GET"}, options = { "expose" = true })
     */
    public function getIndustries() {

        $industries = $this->industryRepository->findAll();

        $json = $this->serializer->serialize($industries, 'json', ['groups' => ['RESULTS_PAGE']]);

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
     * @Route("/courses", name="get_courses", methods={"GET"}, options = { "expose" = true })
     */
    public function getCourses() {

        $courses = $this->courseRepository->findAll();

        $json = $this->serializer->serialize($courses, 'json', ['groups' => ['LESSON_DATA']]);

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
     * @Route("/companies/{companyID}/video/{videoID}/remove", name="remove_company_video", methods={"POST"}, options = { "expose" = true })
     * @ParamConverter("company", options={"id" = "companyID"})
     * @ParamConverter("video", options={"id" = "videoID"})
     * @param Company $company
     * @param Video $video
     * @return JsonResponse
     */
    public function removeCompanyVideo(Company $company, Video $video) {

        $this->denyAccessUnlessGranted('delete', $video);

        $this->entityManager->remove($video);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/companies/{companyID}/resource/{resourceID}/remove", name="remove_company_resource", methods={"POST"}, options = { "expose" = true })
     * @ParamConverter("company", options={"id" = "companyID"})
     * @ParamConverter("companyResource", options={"id" = "resourceID"})
     * @param Company $company
     * @param CompanyResource $companyResource
     * @return JsonResponse
     */
    public function removeCompanyResource(Company $company, CompanyResource $companyResource) {

        $this->denyAccessUnlessGranted('delete', $companyResource);

        $this->entityManager->remove($companyResource);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/companies/{company_id}/photos/{image_id}/remove", name="company_photo_remove", options = { "expose" = true })
     * @ParamConverter("image", options={"id" = "image_id"})
     * @ParamConverter("company", options={"id" = "company_id"})
     * @param Company $company
     * @param Request $request
     * @param CompanyPhoto $image
     * @return JsonResponse
     */
    public function removeCompanyPhotoAction(Company $company, Request $request, CompanyPhoto $image) {

        $this->denyAccessUnlessGranted('delete', $image);

        $this->entityManager->remove($image);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/companies/{id}/favorite", name="favorite_company", methods={"GET"}, options = { "expose" = true })
     * @param Company $company
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function favoriteCompany(Company $company) {


        $companyObj = $this->companyFavoriteRepository->findOneBy([
           'user' => $this->getUser(),
           'company' => $company
        ]);

        if($companyObj) {
            return $this->redirectToRoute('company_results_page');
        }

        $companyFavorite = new CompanyFavorite();
        $companyFavorite->setUser($this->getUser());
        $companyFavorite->setCompany($company);

        $this->entityManager->persist($companyFavorite);
        $this->entityManager->flush();

        return $this->redirectToRoute('company_results_page');
    }

    /**
     * @Route("/companies/{id}/unfavorite", name="unfavorite_company", methods={"GET"}, options = { "expose" = true })
     * @param Company $company
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unFavoriteCompany(Company $company) {


        $companyObj = $this->companyFavoriteRepository->findOneBy([
            'user' => $this->getUser(),
            'company' => $company
        ]);

        if($companyObj) {
            $this->entityManager->remove($companyObj);
            $this->entityManager->flush();

            return $this->redirectToRoute('company_results_page');
        }

        return $this->redirectToRoute('company_results_page');
    }

    /**
     * @Route("/companies/favorites", name="get_favorite_companies", methods={"GET"}, options = { "expose" = true })
     * @return JsonResponse
     */
    public function getFavoriteCompanies() {

        $favorites = $this->companyFavoriteRepository->findBy(
            [
                'user' => $this->getUser()
            ]
        );

        $json = $this->serializer->serialize($favorites, 'json', ['groups' => ['RESULTS_PAGE']]);

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