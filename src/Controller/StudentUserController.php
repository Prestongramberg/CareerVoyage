<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\EducatorUser;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\School;
use App\Entity\SecondaryIndustry;
use App\Entity\StateCoordinator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\ManageStudentsFilterType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\RegionalCoordinatorFormType;
use App\Form\StateCoordinatorFormType;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\StateCoordinatorRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
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
 * Class StudentUserController
 * @package App\Controller
 * @Route("/dashboard/students")
 */
class StudentUserController extends AbstractController
{
    use FileHelper;
    use RandomStringGenerator;
    use ServiceHelper;

    /**
     * @Route("/{id}/industries/add", name="student_industry_add")
     * @param Request $request
     * @param StudentUser $studentUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addIndustry(Request $request, StudentUser $studentUser) {

        $this->denyAccessUnlessGranted('edit', $studentUser);

        $secondaryIndustryId = $request->request->get('secondaryIndustry');
        $secondaryIndustry = $this->secondaryIndustryRepository->find($secondaryIndustryId);

        if($secondaryIndustry) {
            $studentUser->addSecondaryIndustry($secondaryIndustry);
            $this->entityManager->persist($studentUser);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,

                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/{id}/industries/remove", name="student_industry_remove")
     * @param Request $request
     * @param StudentUser $studentUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeIndustry(Request $request, StudentUser $studentUser) {

        $this->denyAccessUnlessGranted('edit', $studentUser);

        $secondaryIndustryId = $request->request->get('secondaryIndustry');
        $secondaryIndustry = $this->secondaryIndustryRepository->find($secondaryIndustryId);

        $studentUser->removeSecondaryIndustry($secondaryIndustry);
        $this->entityManager->persist($studentUser);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/{id}/industries", name="student_industries")
     * @param Request $request
     * @param StudentUser $studentUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndustries(Request $request, StudentUser $studentUser) {

        $secondaryIndustries = $studentUser->getSecondaryIndustries();

        $json = $this->serializer->serialize($secondaryIndustries, 'json', ['groups' => ['RESULTS_PAGE']]);

        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/graduated", name="graduated")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function graduated(Request $request) {
            $user = $this->getUser();
        return $this->render('studentUser/graduated.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/schools/{id}/manage", name="students_manage", methods={"GET"})
     * @param School $school
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageAction(School $school, Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->buildFilterForm(StudentUser::class, $this->generateUrl('students_manage', ['id' => $school->getId()]), $school
        );
        $form->handleRequest($request);

        $filterBuilder = $this->studentUserRepository->createQueryBuilder('u');
        $filterBuilder->addOrderBy('u.firstName', 'ASC');
        $filterBuilder->andWhere('u.deleted = 0');
        $filterBuilder->andWhere('u.school = :school')->setParameter('school', $school->getId());


        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );


        $studentUsers = $this->studentUserRepository->findBy([
            'school' => $school
        ]);

        $user = $this->getUser();
        return $this->render('students/manage.html.twig', [
            'user' => $user,
            'studentUsers' => $studentUsers,
            'school' => $school,
            'pagination' => $pagination,
            'form' => $form->createView(),
            'clearFormUrl' => $this->generateUrl('students_manage', ['id' => $school->getId()])
        ]);
    }

    /**
     * Builds the manage users filter form
     *
     * @param $filterType
     * @param $action
     * @param School $school
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function buildFilterForm($filterType, $action, School $school)
    {
        $form = $this->createForm(ManageStudentsFilterType::class, null, [
            'action' => $action,
            'method' => 'GET',
            'filter_type' => $filterType,
            'school' => $school
        ]);

        return $form;
    }
}