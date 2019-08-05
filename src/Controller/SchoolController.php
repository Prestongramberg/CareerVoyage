<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\ProfessionalDeactivateProfileFormType;
use App\Form\ProfessionalDeleteProfileFormType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\ProfessionalReactivateProfileFormType;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
 * @Route("/dashboard")
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
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * @var CompanyPhotoRepository
     */
    private $companyPhotoRepository;

    /**
     * @var LessonFavoriteRepository
     */
    private $lessonFavoriteRepository;

    /**
     * @var LessonTeachableRepository
     */
    private $lessonTeachableRepository;

    /**
     * LessonController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param UploaderHelper $uploaderHelper
     * @param Packages $assetsManager
     * @param CompanyRepository $companyRepository
     * @param CompanyPhotoRepository $companyPhotoRepository
     * @param LessonFavoriteRepository $lessonFavoriteRepository
     * @param LessonTeachableRepository $lessonTeachableRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        UserPasswordEncoderInterface $passwordEncoder,
        ImageCacheGenerator $imageCacheGenerator,
        UploaderHelper $uploaderHelper,
        Packages $assetsManager,
        CompanyRepository $companyRepository,
        CompanyPhotoRepository $companyPhotoRepository,
        LessonFavoriteRepository $lessonFavoriteRepository,
        LessonTeachableRepository $lessonTeachableRepository
    ) {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->passwordEncoder = $passwordEncoder;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->uploaderHelper = $uploaderHelper;
        $this->assetsManager = $assetsManager;
        $this->companyRepository = $companyRepository;
        $this->companyPhotoRepository = $companyPhotoRepository;
        $this->lessonFavoriteRepository = $lessonFavoriteRepository;
        $this->lessonTeachableRepository = $lessonTeachableRepository;
    }

    /**
     * @Route("/schools", name="school_index", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request) {


        return new Response("hi");

        $user = $this->getUser();
        return $this->render('experience/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/state-coordinator/new", name="school_state_coordinator_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stateCoordinatorAction(Request $request) {


        $user = $this->getUser();
        $lesson = new Lesson();

        $options = [
            'method' => 'POST',
            'skip_validation' => $request->request->get('skip_validation', false)
        ];

        $form = $this->createForm(NewLessonType::class, $lesson, $options);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var Lesson $lesson */
            $lesson = $form->getData();

            $uploadedFile = $form->get('thumbnailImage')->getData();

            if($uploadedFile) {
                $newFilename = $this->uploaderHelper->upload($uploadedFile, UploaderHelper::LESSON_THUMBNAIL);
                $lesson->setThumbnailImage($newFilename);

                $path = $this->uploaderHelper->getPublicPath(UploaderHelper::LESSON_THUMBNAIL) .'/'. $newFilename;
                $this->imageCacheGenerator->cacheImageForAllFilters($path);
            }

            $uploadedFile = $form->get('featuredImage')->getData();

            if($uploadedFile) {
                $newFilename = $this->uploaderHelper->upload($uploadedFile, UploaderHelper::LESSON_FEATURED);
                $lesson->setFeaturedImage($newFilename);
            }

            $lesson->setUser($user);
            $this->entityManager->persist($lesson);

            /** @var LessonResource $resource */
            $resource = $form->get('resources')->getData();
            if($resource->getFile() && $resource->getDescription() && $resource->getTitle()) {
                $file = $resource->getFile();
                $mimeType = $file->getMimeType();
                $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::EXPERIENCE_FILE);
                $resource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
                $resource->setMimeType($mimeType ?? 'application/octet-stream');
                $resource->setFileName($newFilename);
                $resource->setFile(null);
                $resource->setLesson($lesson);
                $this->entityManager->persist($resource);
            }

            $teachableLesson = new LessonTeachable();
            $teachableLesson->setLesson($lesson);
            $teachableLesson->setUser($user);
            $this->entityManager->persist($teachableLesson);

            $this->entityManager->flush();

            $this->addFlash('success', 'Lesson successfully created');
            return $this->redirectToRoute('lesson_index');
        }

        if($request->request->has('primary_industry_change')) {
            return new JsonResponse(
                [
                    'success' => false,
                    'formMarkup' => $this->renderView('api/form/secondary_industry_form_field.html.twig', [
                        'form' => $form->createView()
                    ])
                ], Response::HTTP_BAD_REQUEST
            );
        }

        return $this->render('lesson/new.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/schools/regional-coordinator/new", name="school_regional_coordinator_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requestToRegionalCoordinatorAction(Request $request) {

        $user = $this->getUser();
        return $this->render('school/request_to_regional_coordinator.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/schools/administrator/new", name="school_administrator_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function schoolAdministratorAction(Request $request) {


        return new Response("school administrator");

        $user = $this->getUser();
        return $this->render('experience/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/schools/{id}/students", name="school_students")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function schoolStudentsAction(Request $request) {


        return new Response("school students");

        $user = $this->getUser();
        return $this->render('experience/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/schools/{id}/educators", name="school_educators")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function schoolEducatorsAction(Request $request) {


        return new Response("school educators");

        $user = $this->getUser();
        return $this->render('experience/index.html.twig', [
            'user' => $user,
        ]);
    }
}