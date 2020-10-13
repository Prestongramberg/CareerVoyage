<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\EducatorUser;
use App\Entity\Image;
use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\School;
use App\Entity\SecondaryIndustry;
use App\Entity\StateCoordinator;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\ManageEducatorsFilterType;
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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Asset\Packages;

/**
 * Class EducatorUserController
 * @package App\Controller
 * @Route("/dashboard/educators")
 */
class EducatorUserController extends AbstractController
{
    use FileHelper;
    use RandomStringGenerator;
    use ServiceHelper;

    /**
     * @Route("/{id}/industries/add", name="educator_industry_add")
     * @param Request $request
     * @param EducatorUser $educatorUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addIndustry(Request $request, EducatorUser $educatorUser) {

        $this->denyAccessUnlessGranted('edit', $educatorUser);

        $secondaryIndustryId = $request->request->get('secondaryIndustry');
        $secondaryIndustry = $this->secondaryIndustryRepository->find($secondaryIndustryId);

        if($secondaryIndustry) {
            $educatorUser->addSecondaryIndustry($secondaryIndustry);
            $this->entityManager->persist($educatorUser);
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
     * @Route("/{id}/industries/remove", name="educator_industry_remove")
     * @param Request $request
     * @param EducatorUser $educatorUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeIndustry(Request $request, EducatorUser $educatorUser) {

        $this->denyAccessUnlessGranted('edit', $educatorUser);

        $secondaryIndustryId = $request->request->get('secondaryIndustry');
        $secondaryIndustry = $this->secondaryIndustryRepository->find($secondaryIndustryId);

        $educatorUser->removeSecondaryIndustry($secondaryIndustry);
        $this->entityManager->persist($educatorUser);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/{id}/industries", name="educator_industries")
     * @param Request $request
     * @param EducatorUser $educatorUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndustries(Request $request, EducatorUser $educatorUser) {

        $secondaryIndustries = $educatorUser->getSecondaryIndustries();

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
     * @Route("/", name="educator_index", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request) {

        $educatorUsers = $this->educatorUserRepository->getAll();

        $user = $this->getUser();
        return $this->render('educators/index.html.twig', [
            'user' => $user,
            'educatorUsers' => $educatorUsers
        ]);
    }

    /**
     * @Route("/schools/{id}/manage", name="educator_manage", methods={"GET"})
     * @param School $school
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageAction(School $school, Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->buildFilterForm(EducatorUser::class, $this->generateUrl('educator_manage', ['id' => $school->getId()]));
        $form->handleRequest($request);

        $filterBuilder = $this->educatorUserRepository->createQueryBuilder('u');
        $filterBuilder->addOrderBy('u.lastName', 'ASC');
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

        $educatorUsers = $this->educatorUserRepository->findBy([
            'school' => $school
        ]);

        usort($educatorUsers, function($a, $b) {
            return strcmp($a->getLastName(), $b->getLastName());
        });

        $user = $this->getUser();
        return $this->render('educators/manage.html.twig', [
            'user' => $user,
            'educatorUsers' => $educatorUsers,
            'school' => $school,
            'pagination' => $pagination,
            'form' => $form->createView(),
            'clearFormUrl' => $this->generateUrl('educator_manage', ['id' => $school->getId()])
        ]);
    }

    /**
     * @Route("/schools/{id}/reassign", name="educator_students_reassign", methods={"POST"})
     * @param School $school
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function educatorStudentsReassignAction(School $school, Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $studentIds = $request->request->get('students');
        $educatorIds = $request->request->get('educators');
        $originalEducatorId = $request->request->get('originalEducator');
        $schoolId = $request->request->get('school');

        $originalEducator = $this->educatorUserRepository->find($originalEducatorId);

        if($originalEducator) {

            foreach($originalEducator->getStudentUsers() as $studentUser) {
                if(in_array($studentUser->getId(), $studentIds)) {
                    $originalEducator->removeStudentUser($studentUser);
                }
            }

            $this->entityManager->persist($originalEducator);
        }

        $students = $this->studentUserRepository->findBy([
            'id' => $studentIds
        ]);

        $educators = $this->educatorUserRepository->findBy([
            'id' => $educatorIds
        ]);

        foreach($educators as $educator) {
            foreach($students as $student) {
                if(!$educator->hasStudentUserInClass($student)) {
                    $educator->addStudentUser($student);
                }
            }
            $this->entityManager->persist($educator);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Students successfully re-assigned');

        return $this->redirectToRoute('educator_manage', ['id' => $schoolId]);
    }


    /**
     * Builds the manage users filter form
     *
     * @param $filterType
     * @param $action
     * @return FormInterface The form
     */
    private function buildFilterForm($filterType, $action)
    {
        $form = $this->createForm(ManageEducatorsFilterType::class, null, [
            'action' => $action,
            'method' => 'GET',
            'filter_type' => $filterType
        ]);

        return $form;
    }
}