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
use App\Form\ManageStudentsFilterType;
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
     * @Route("/{id}/update_educators", name="update_student_educators", methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateStudentEducators(Request $request, StudentUser $studentUser) {
        $school_id = $request->request->get('schoolId');
        $new_educators = $request->request->get('educatorUser');

        $new_educator_array = array();

        // Remove current student / educator association
        $educators = $studentUser->getEducatorUsers();
        if(sizeof($educators) > 0) {
            foreach($educators as $educator) {
                $studentUser->removeEducatorUser($educator);
                $educator->removeStudentUser($studentUser);
            }

            $this->entityManager->persist($studentUser);
            $this->entityManager->persist($educator);
            $this->entityManager->flush();
        }

        // Create new student / educator associations
        foreach($new_educators as $educator) {
            $educatorUser = $this->educatorUserRepository->findById($educator);
            $educatorUser[0]->addStudentUser($studentUser);
            $studentUser->addEducatorUser($educatorUser[0]);

            $new_educator_array[] = array('id' => $educatorUser[0]->getId(), 'name' => $educatorUser[0]->getLastName().', '.$educatorUser[0]->getfirstName());

            // Save the record for each user indivudally
            $this->entityManager->persist($studentUser);
            $this->entityManager->persist($educatorUser[0]);
            $this->entityManager->flush();
        }

        // $this->addFlash('success', 'Students successfully re-assigned');

        return new JsonResponse(
            [
                'success' => true,
                'student_id' => $studentUser->getId(),
                'student_name' => $studentUser->getFirstName().' '.$studentUser->getLastName(),
                'educators' => $new_educator_array
            ],
            Response::HTTP_OK
        );
    }


    /**
     * @Route("/bulk_update_educators", name="bulk_update_student_educators", methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bulkUpdateStudentEducators(Request $request) {
        $school_id = $request->request->get('schoolId');
        $educators = $request->request->get('educatorUser');
        $students = $request->request->get('student');

        // Remove current student / educator association
        if(sizeof($students) > 0) {
            foreach($students as $student) {
                $studentUser = $this->studentUserRepository->findById($student);
                $studentEducatorList = $studentUser[0]->getEducatorUsers();

                if(sizeof($studentEducatorList) > 0){
                    foreach($studentEducatorList as $studentEducator) {
                        $studentUser[0]->removeEducatorUser($studentEducator);
                        $studentEducator->removeStudentUser($studentUser[0]);

                        // Save the record for each user individually
                        $this->entityManager->persist($studentUser[0]);
                        $this->entityManager->persist($studentEducator);
                        $this->entityManager->flush();
                    }
                }
            }
        }

        // Create new student / educator associations

        $studentList = [];

        if(sizeof($students) > 0) {
            foreach($students as $student) {
                $studentUser = $this->studentUserRepository->findById($student);

                $educatorsList = [];
                if(sizeof($educators) > 0) {
                    // Get individual educator
                    foreach($educators as $educator) {
                        $educatorUser = $this->educatorUserRepository->findById($educator);

                        $educatorsList[] = ["id" => $educatorUser[0]->getId(), "name" =>$educatorUser[0]->getLastName().', '.$educatorUser[0]->getFirstName()];

                        // Assign the educator to the student user
                        $studentUser[0]->addEducatorUser($educatorUser[0]);
                        $educatorUser[0]->addStudentUser($studentUser[0]);

                        // Save the record
                        $this->entityManager->persist($studentUser[0]);
                        $this->entityManager->persist($educatorUser[0]);
                        $this->entityManager->flush();
                    }
                }

                // Add the new combination to the student array
                $studentList[] = [
                        "student_id" => $studentUser[0]->getId(), 
                        "student_name" => $studentUser[0]->getFirstName().' '.$studentUser[0]->getLastName(),
                        "educators" => $educatorsList
                ];
            }
        }

        return new JsonResponse(
            [
                'success' => true,
                'students' => $studentList
            ],
            Response::HTTP_OK
        );
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