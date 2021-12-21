<?php

namespace App\Controller;

use App\Entity\AdminUser;
use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\Company;
use App\Entity\EducatorUser;
use App\Entity\ExperienceFile;
use App\Entity\Image;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\Registration;
use App\Entity\RequestAction;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\SchoolPhoto;
use App\Entity\SchoolResource;
use App\Entity\SchoolVideo;
use App\Entity\StudentReviewCompanyExperienceFeedback;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Form\AdHocFormType;
use App\Form\AssignedStudentsFormType;
use App\Form\ChatFilterType;
use App\Form\ChatMessageFilterType;
use App\Form\DeleteStudentsFormType;
use App\Form\EditSchoolExperienceType;
use App\Form\EditSchoolType;
use App\Form\EducatorImportType;
use App\Form\ExperienceType;
use App\Form\Filter\SchoolFilterType;
use App\Form\ManageEducatorsFilterType;
use App\Form\ManageStudentsFilterType;
use App\Form\NewSchoolExperienceType;
use App\Form\NewSchoolType;
use App\Form\Request\SendMessageFormType;
use App\Form\ResetPasswordType;
use App\Form\SchoolAdminFormType;
use App\Form\SchoolCommunicationType;
use App\Form\StudentImportType;
use App\Form\SupervisingTeacherFormType;
use App\Model\ResetPassword;
use App\Service\RequestService;
use App\Service\UploaderHelper;
use App\Util\AuthorizationVoter;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SchoolController
 *
 * @package App\Controller
 * @Route("/dashboard")
 */
class SchoolController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;
    use RandomStringGenerator;

    /**
     * @Route("/schools", name="school_index", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {

        $user = $this->getUser();

        return $this->render('school/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/schools/results", name="school_results_page", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function schoolsResultsAction(Request $request)
    {

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(SchoolFilterType::class, null, [
            'method' => 'GET',
        ]);

        $form->handleRequest($request);

        $filterBuilder = $this->schoolRepository->createQueryBuilder('s')->addOrderBy('s.name', 'ASC');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $sql = $filterQuery->getSQL();

        $pagination = $this->paginator->paginate($filterQuery, /* query NOT result */ $request->query->getInt('page', 1), /*page number*/ 10 /*limit per page*/);

        return $this->render('school/results.html.twig', [
            'user'         => $user,
            'pagination'   => $pagination,
            'form'         => $form->createView(),
            'zipcode'      => $request->query->get('zipcode', ''),
            'clearFormUrl' => $this->generateUrl('school_results_page'),
        ]);
    }


    /**
     * @Security("is_granted('ROLE_REGIONAL_COORDINATOR_USER')")
     * @Route("/schools/admin/new", name="school_admin_new")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function newAdminAction(Request $request)
    {

        /** @var RegionalCoordinator $user */
        $user        = $this->getUser();
        $schoolAdmin = new SchoolAdministrator();

        $form = $this->createForm(SchoolAdminFormType::class, $schoolAdmin, [
            'method' => 'POST',
            'site'   => $user->getSite(),
            'user'   => $user,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SchoolAdministrator $schoolAdmin */
            $schoolAdmin  = $form->getData();
            $existingUser = $this->userRepository->getByEmailAddress($schoolAdmin->getEmail());

            if ($existingUser) {
                $this->addFlash('error', 'That user already exists in the system.');

                return $this->redirectToRoute('school_new');
            } else {
                $schoolAdministrator = new SchoolAdministrator();
                $schoolAdministrator->setEmail($schoolAdmin->getEmail());
                $schoolAdministrator->setFirstName($schoolAdmin->getFirstName());
                $schoolAdministrator->setLastName($schoolAdmin->getLastName());
                $schoolAdministrator->initializeNewUser(false, true);
                $schoolAdministrator->setupAsSchoolAdministrator();
                $schoolAdministrator->setSite($user->getSite());

                foreach ($schoolAdmin->getSchools() as $school) {
                    $schoolAdministrator->addSchool($school);
                }

                $this->entityManager->persist($schoolAdministrator);
            }
            $this->entityManager->flush();
            $this->securityMailer->sendPasswordSetupForSchoolAdministrator($schoolAdministrator);
            $this->addFlash('success', sprintf('School administrator invite sent to %s', $schoolAdmin->getEmail()));

            return $this->redirectToRoute('school_admin_new');
        }

        return $this->render('school/new_admin.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("is_granted('ROLE_REGIONAL_COORDINATOR_USER')")
     * @Route("/schools/new", name="school_new")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newSchool(Request $request)
    {

        /** @var RegionalCoordinator $user */
        $user   = $this->getUser();
        $school = new School();

        $form = $this->createForm(NewSchoolType::class, $school, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var School $school */
            $school = $form->getData();
            $school->setState($user->getRegion()->getState());
            $school->setRegion($user->getRegion());
            $school->setSite($user->getSite());

            if ($coordinates = $this->geocoder->geocode($school->getAddress())) {
                $lng = $coordinates['lng'];
                $lat = $coordinates['lat'];
                $school->setLongitude($lng);
                $school->setLatitude($lat);
                list($latN, $latS, $lonE, $lonW) = $this->geocoder->calculateSearchSquare($lat, $lng, 50);
                $companies     = $this->companyRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng);
                $professionals = $this->professionalUserRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng);
            }

            $this->entityManager->persist($school);

            if ($companies) {
                foreach ($companies as $company) {
                    $companyIds[] = $company['id'];
                }
                $companies = $this->companyRepository->getByArrayOfIds($companyIds);
                foreach ($companies as $company) {
                    $company->addSchool($school);
                }
            }

            if ($professionals) {
                foreach ($professionals as $professional) {
                    $professionalIds[] = $professional['id'];
                }
                $professionals = $this->professionalUserRepository->getByArrayOfIds($professionalIds);
                foreach ($professionals as $professional) {
                    $professional->addSchool($school);
                }
            }

            $this->entityManager->flush();


            $zipcode = $school->getZipcode();
            $radius  = 50;
            $lng     = null;
            $lat     = null;

            if ($zipcode && $coordinates = $this->geocoder->geocode($zipcode)) {
                $lng = $coordinates['lng'];
                $lat = $coordinates['lat'];
                list($latN, $latS, $lonE, $lonW) = $this->geocoder->calculateSearchSquare($lat, $lng, $radius);
                $professionals   = $this->professionalUserRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng);
                $professionalIds = [];
                foreach ($professionals as $professional) {
                    $professionalIds[] = $professional['id'];
                }
                $professionals = $this->professionalUserRepository->getByArrayOfIds($professionalIds);

                /** @var ProfessionalUser $professional */
                foreach ($professionals as $professional) {
                    $professional->addSchool($school);
                }


                $companies  = $this->companyRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng);
                $companyIds = [];
                foreach ($companies as $company) {
                    $companyIds[] = $company['id'];
                }
                $companies = $this->companyRepository->getByArrayOfIds($companyIds);

                /** @var Company $company */
                foreach ($companies as $company) {
                    $company->addSchool($school);
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'School successfully created.');

            return $this->redirectToRoute('school_new');
        }

        return $this->render('school/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/schools/{id}/educators", name="school_educators")
     * @param Request $request
     * @param School  $school
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function educatorsAction(Request $request, School $school)
    {

        $this->denyAccessUnlessGranted('edit', $school);

        return new Response("educators");
    }

    /**
     * @Route("/schools/educators/{id}/remove", name="remove_educator", methods={"POST"})
     * @param Request      $request
     * @param EducatorUser $educatorUser
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeEducatorAction(Request $request, EducatorUser $educatorUser)
    {

        $this->denyAccessUnlessGranted('edit', $educatorUser->getSchool());

        $schoolAdminId = $request->request->get('schoolAdminId');

        $school = $educatorUser->getSchool();
        $school->removeEducatorUser($educatorUser);
        $this->entityManager->persist($school);

        $students = $educatorUser->getStudentUsers();
        foreach ($students as $student) {
            $student->removeEducatorUser($educatorUser);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Educator removed from school');

        if ($request->request->get('iframe')) {

            return new JsonResponse([
                'success' => true,
                'id'      => $educatorUser->getId(),

            ], Response::HTTP_OK);

        } else {
            return $this->redirectToRoute('dashboard');
        }

    }

    /**
     * @Route("/schools/{id}/students", name="school_students")
     * @param Request $request
     * @param School  $school
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function studentsAction(Request $request, School $school)
    {

        $this->denyAccessUnlessGranted('edit', $school);

        return new Response("students");
    }

    /**
     * @Route("/schools/{id}/delete", name="school_delete", options = { "expose" = true })
     * @param Request $request
     * @param School  $school
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteSchoolAction(Request $request, School $school)
    {

        $this->denyAccessUnlessGranted('edit', $school);

        /** @var User $user */
        $user = $this->getUser();

        $this->entityManager->remove($school);
        $this->entityManager->flush();

        $this->addFlash('success', 'School deleted');

        return $this->redirectToRoute('school_index');
    }

    /**
     * @Route("/schools/students/{id}/remove", name="remove_student", methods={"POST"})
     * @param Request     $request
     * @param StudentUser $studentUser
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeStudentAction(Request $request, StudentUser $studentUser)
    {

        $this->denyAccessUnlessGranted('edit', $studentUser->getSchool());

        $schoolAdminId = $request->request->get('schoolAdminId');

        $studentUser->setArchived(true);
        $studentUser->setActivated(false);
        $this->entityManager->persist($studentUser);
        $this->entityManager->flush();

        if ($request->request->get('iframe')) {

            return new JsonResponse([
                'success' => true,
                'id'      => $studentUser->getId(),

            ], Response::HTTP_OK);

        } else {
            return $this->redirectToRoute('dashboard');
        }
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_STATE_COORDINATOR_USER", "ROLE_REGIONAL_COORDINATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER"})
     * @Route("/schools/{id}/edit", name="school_edit", options = { "expose" = true })
     * @param Request $request
     * @param School  $school
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, School $school)
    {

        $this->denyAccessUnlessGranted('edit', $school);

        $user = $this->getUser();

        $form = $this->createForm(EditSchoolType::class, $school, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var School $school */
            $school = $form->getData();
            if ($coordinates = $this->geocoder->geocode($school->getAddress())) {
                $school->setLongitude($coordinates['lng']);
                $school->setLatitude($coordinates['lat']);
            }
            $this->entityManager->persist($school);
            $this->entityManager->flush();
            $this->addFlash('success', sprintf('School successfully updated.'));

            return $this->redirectToRoute('school_edit', ['id' => $school->getId()]);
        }

        return $this->render('school/edit.html.twig', [
            'user'   => $user,
            'form'   => $form->createView(),
            'school' => $school,
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_STATE_COORDINATOR_USER", "ROLE_REGIONAL_COORDINATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER"})
     * @Route("/schools/{id}/communication-type", name="school_communication_type", options = { "expose" = true })
     * @param Request $request
     * @param School  $school
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function communicationType(Request $request, School $school)
    {
        $this->denyAccessUnlessGranted('edit', $school);
        $user = $this->getUser();
        $form = $this->createForm(SchoolCommunicationType::class, $school, [
            'method' => 'POST',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var School $school */
            $school = $form->getData();
            $this->entityManager->persist($school);
            $this->entityManager->flush();
            $this->addFlash('success', sprintf('School communication type successfully updated.'));

            return $this->redirectToRoute('school_communication_type', ['id' => $school->getId()]);
        }

        return $this->render('school/communication_type.html.twig', [
            'user'   => $user,
            'form'   => $form->createView(),
            'school' => $school,
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_STATE_COORDINATOR_USER", "ROLE_REGIONAL_COORDINATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER", "ROLE_EDUCATOR_USER"})
     * @Route("/schools/{id}/chats", name="school_chat", options = { "expose" = true })
     * @param Request $request
     * @param School  $school
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function chats(Request $request, School $school)
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->isEducator() && $user->getSchool()->getId() !== $school->getId()) {
            throw new AccessDeniedException();
        } else {
            if (!$user->isEducator()) {
                $this->denyAccessUnlessGranted('edit', $school);
            }
        }
        $form = $this->createForm(ChatFilterType::class, null, [
            'action' => $this->generateUrl('school_chat', ['id' => $school->getId()]),
            'method' => 'GET',
        ]);

        $form->handleRequest($request);
        $studentIds = [];
        foreach ($school->getStudentUsers() as $studentUser) {
            $studentIds[] = $studentUser->getId();
        }
        $filterBuilder = $this->chatRepository->createQueryBuilder('c')
                                              ->andWhere('c.userOne IN (:userOneIds) OR c.userTwo IN (:userTwoIds)')
                                              ->setParameter('userOneIds', $studentIds)
                                              ->setParameter('userTwoIds', $studentIds);

        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $pagination = $this->paginator->paginate($filterQuery, /* query NOT result */ $request->query->getInt('page', 1), /*page number*/ 10 /*limit per page*/);

        return $this->render('school/chat.html.twig', [
            'user'       => $user,
            'school'     => $school,
            'pagination' => $pagination,
            'form'       => $form->createView(),
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_STATE_COORDINATOR_USER", "ROLE_REGIONAL_COORDINATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER", "ROLE_EDUCATOR_USER"})
     * @Route("/schools/{id}/chats/{chatId}/messages", name="school_chat_messages", options = { "expose" = true })
     * @param Request $request
     * @param School  $school
     * @param Chat    $chat
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function chatMessages(Request $request, School $school, Chat $chat)
    {

        $user = $this->getUser();
        if ($user->isEducator() && $user->getSchool()->getId() !== $school->getId()) {
            throw new AccessDeniedException();
        } else {
            if (!$user->isEducator()) {
                $this->denyAccessUnlessGranted('edit', $school);
            }
        }

        $form = $this->createForm(ChatMessageFilterType::class, null, [
            'action' => $this->generateUrl('school_chat_messages', [
                'id'     => $school->getId(),
                'chatId' => $chat->getId(),
            ]),
            'method' => 'GET',
        ]);

        $form->handleRequest($request);
        $filterBuilder = $this->chatMessageRepository->createQueryBuilder('cm')
                                                     ->where('cm.chat = :chat')
                                                     ->setParameter('chat', $chat);

        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $pagination = $this->paginator->paginate($filterQuery, /* query NOT result */ $request->query->getInt('page', 1), /*page number*/ 10 /*limit per page*/);

        return $this->render('school/chat_messages.html.twig', [
            'user'         => $user,
            'school'       => $school,
            'pagination'   => $pagination,
            'chat'         => $chat,
            'form'         => $form->createView(),
            'clearFormUrl' => $this->generateUrl('school_chat_messages', [
                'id'     => $school->getId(),
                'chatId' => $chat->getId(),
            ]),
        ]);
    }

    /**
     * @Route("/schools/{id}/view", name="school_view", options = { "expose" = true })
     * @param Request $request
     * @param School  $school
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, School $school)
    {

        $user = $this->getUser();

        $volunteeringCompanies     = $this->companyRepository->getBySchool($school);
        $volunteeringProfessionals = $this->professionalUserRepository->getBySchool($school);


        return $this->render('school/view.html.twig', [
            'user'                      => $user,
            'school'                    => $school,
            'volunteeringCompanies'     => $volunteeringCompanies,
            'volunteeringProfessionals' => $volunteeringProfessionals,
        ]);
    }

    /**
     * @Security("is_granted('ROLE_SCHOOL_ADMINISTRATOR_USER')")
     * @Route("/schools/{id}/students/import", name="school_student_import")
     * @param Request $request
     * @param School  $school
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function studentImportAction(Request $request, School $school)
    {

        $this->denyAccessUnlessGranted('edit', $school);

        /** @var SchoolAdministrator $user */
        $user = $this->getUser();

        $form = $this->createForm(StudentImportType::class, null, [
            'method' => 'POST',
            'school' => $school,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $file    = $form->get('file')->getData();
            $columns = $this->phpSpreadsheetHelper->getColumnNames($file);
            // capitalize each word in each item in array so we can assure a proper comparision
            $columns         = array_map('strtolower', $columns);
            $columns         = array_map('ucwords', $columns);
            $expectedColumns = ['First Name', 'Last Name', 'Graduating Year', 'Educator Number'];
            if ($columns != $expectedColumns) {
                $this->addFlash('error', sprintf('Column names need to be exactly: %s', implode(",", $expectedColumns)));

                return $this->redirectToRoute('school_student_import', ['id' => $school->getId()]);
            }

            try {
                $reader = $this->phpSpreadsheetHelper->getReader($file);
            } catch (\Exception $exception) {
                $this->addFlash('error', sprintf('Error loading spreadsheet reader. (%s).', $exception->getMessage()));

                return $this->redirectToRoute('school_student_import', ['id' => $school->getId()]);
            }

            /**
             * Generating passwords inside a loop is extremely expensive and uses up too much cpu/ram. Create initial temp
             * shared password for all users being imported
             */
            $tempPassword    = sprintf('student.%s', $this->generateRandomString(5));
            $encodedPassword = $this->generateStudentTemporaryPassword($tempPassword);

            try {
                $this->entityManager->beginTransaction();
                $studentObjs      = [];
                $previousEducator = null;

                /** @var \Box\Spout\Reader\SheetInterface $sheet */
                foreach ($reader->getSheetIterator() as $sheet) {
                    /** @var \Box\Spout\Common\Entity\Row $row */
                    $columns   = [];
                    $batchSize = 20;
                    foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                        $values = [];

                        $cells = $row->getCells();
                        foreach ($cells as $cell) {
                            $value = $cell->getValue();
                            if ($rowIndex === 1) {
                                $columns[] = ucwords(strtolower($value));

                            } else {
                                $values[] = $value;
                            }
                        }

                        if ($rowIndex > 1) {

                            // Normalizing empty cell possibilities https://github.com/box/spout/issues/332
                            if (count($columns) !== count($values)) {
                                $values = $values + array_fill(count($values), count($columns) - count($values), '');
                            }
                            $student = array_combine($columns, $values);

                            if (!empty($student['First Name']) && !empty($student['Last Name'])) {

                                $username = preg_replace('/\s+/', '', sprintf("%s.%s", strtolower(trim($student['First Name']) . '.' . trim($student['Last Name'])), $this->generateRandomString(1)));

                                $studentObj = new StudentUser();
                                $studentObj->setFirstName(trim($student['First Name']));
                                $studentObj->setLastName(trim($student['Last Name']));
                                $studentObj->setGraduatingYear(trim($student['Graduating Year']));

                                if (!empty($student['Educator Number'])) {

                                    if ($previousEducator instanceof EducatorUser && $previousEducator->getId() === trim($student['Educator Number'])) {
                                        $studentObj->addEducatorUser($previousEducator);
                                        $studentObj->setEducatorNumber($previousEducator->getId());
                                    } else {

                                        $educator = $this->educatorUserRepository->findOneBy([
                                            'id'     => trim($student['Educator Number']),
                                            'school' => $school,
                                        ]);
                                        if ($educator) {
                                            $studentObj->addEducatorUser($educator);
                                            $studentObj->setEducatorNumber($educator->getId());
                                            $previousEducator = $educator;
                                        }
                                    }
                                }

                                $studentObj->setSchool($school);
                                $studentObj->setSite($user->getSite());
                                $studentObj->setupAsStudent();
                                $studentObj->initializeNewUser();
                                $studentObj->setTempPassword($tempPassword);
                                $studentObj->setActivated(true);
                                $studentObj->setUsername(trim($username));
                                $studentObj->setPassword($encodedPassword);

                                $this->entityManager->persist($studentObj);
                                $studentObjs[] = $studentObj;
                            }
                        }

                    }
                }

                // make sure any final records that came after the (($rowIndex % $batchSize) === 0) are flushed
                $this->entityManager->flush();
                $this->entityManager->commit();

            } catch (\Exception $exception) {
                $this->entityManager->rollback();
                $this->addFlash('error', sprintf('Error importing spreadsheet. (%s).', $exception->getMessage()));

                return $this->redirectToRoute('school_student_import', ['id' => $school->getId()]);
            }

            if (!empty($studentObjs)) {

                $data               = $this->serializer->serialize($studentObjs, 'json', ['groups' => ['STUDENT_USER']]);
                $data               = json_decode($data, true);
                $attachmentFilePath = sys_get_temp_dir() . '/students.csv';
                file_put_contents($attachmentFilePath, $this->serializer->encode($data, 'csv'));

                /*foreach ($school->getSchoolAdministrators() as $schoolAdministrator) {
                    $this->importMailer->studentImportMailer($schoolAdministrator, $attachmentFilePath);
                }*/
            }

            $this->addFlash('success', sprintf('(%s) Students successfully imported.', count($studentObjs)));

            return $this->redirectToRoute('school_student_import', ['id' => $school->getId()]);
        }

        return $this->render('school/student_import.html.twig', [
            'user'   => $user,
            'form'   => $form->createView(),
            'school' => $school,
        ]);
    }

    /**
     * @Security("is_granted('ROLE_SCHOOL_ADMINISTRATOR_USER')")
     * @Route("/schools/{id}/educators/import", name="school_educator_import")
     * @param Request $request
     * @param School  $school
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function educatorImportAction(Request $request, School $school)
    {


        $this->denyAccessUnlessGranted('edit', $school);

        /** @var SchoolAdministrator $user */
        $user = $this->getUser();

        $form = $this->createForm(EducatorImportType::class, null, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $errors       = 'Duplicate Users (The following users were not imported as they already have an account on the platform as a NON educator): ';
            $error_emails = [];

            /** @var UploadedFile $uploadedFile */
            $file    = $form->get('file')->getData();
            $columns = [];
            try {
                $columns = $this->phpSpreadsheetHelper->getColumns($file);
            } catch (\Exception $exception) {
                return;
            }

            // capitalize each word in each item in array so we can assure a proper comparision
            $columns         = array_map('strtolower', $columns);
            $columns         = array_map('ucwords', $columns);
            $expectedColumns = ['First Name', 'Last Name', 'Email'];
            if ($columns != $expectedColumns) {
                $this->addFlash('error', sprintf('Column names need to be exactly: %s', implode(",", $expectedColumns)));

                return $this->redirectToRoute('school_educator_import', ['id' => $school->getId()]);
            }

            try {
                $reader = $this->phpSpreadsheetHelper->getReader($file);
            } catch (\Exception $exception) {
                $this->addFlash('error', sprintf('Error loading spreadsheet reader. (%s).', $exception->getMessage()));

                return $this->redirectToRoute('school_educator_import', ['id' => $school->getId()]);
            }


            /**
             * Generating passwords inside a loop is extremely expensive and uses up too much cpu/ram. Create initial temp
             * shared password for all users being imported
             */
            $tempPassword    = sprintf('educator.%s', $this->generateRandomString(5));
            $encodedPassword = $this->generateEducatorTemporaryPassword($tempPassword);

            try {
                $this->entityManager->beginTransaction();
                $educatorObjs         = [];
                $existingEducatorObjs = [];

                /** @var \Box\Spout\Reader\SheetInterface $sheet */
                foreach ($reader->getSheetIterator() as $sheet) {
                    /** @var \Box\Spout\Common\Entity\Row $row */
                    $columns   = [];
                    $batchSize = 20;
                    foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                        $values = [];

                        $cells = $row->getCells();
                        foreach ($cells as $cell) {
                            $value = $cell->getValue();
                            if ($rowIndex === 1) {
                                $columns[] = ucwords(strtolower($value));

                            } else {
                                $values[] = $value;
                            }
                        }

                        if ($rowIndex > 1) {

                            // Normalizing empty cell possibilities https://github.com/box/spout/issues/332
                            if (count($columns) !== count($values)) {
                                $values = $values + array_fill(count($values), count($columns) - count($values), '');
                            }
                            $educator = array_combine($columns, $values);

                            $email = $educator['Email'];
                            /** @var User $existingUser */
                            $existingUser = $this->userRepository->findOneBy([
                                'email' => $email,
                            ]);

                            if ($existingUser) {

                                if (!$existingUser->isEducator()) {
                                    $error_emails[] = $existingUser->getEmail();
                                    continue;
                                }

                                // setup a temp password even for the existing user as odds are
                                // they won't remember their current password and this will help
                                // the school admin facilitate their login
                                $existingUser->setSchool($school);
                                $existingUser->setTempPassword($tempPassword);
                                $existingUser->setPassword($encodedPassword);
                                $this->entityManager->persist($existingUser);
                                $existingEducatorObjs[] = $existingUser;
                            } else {

                                $username = preg_replace('/\s+/', '', sprintf("%s.%s", strtolower(trim($educator['First Name']) . '.' . trim($educator['Last Name'])), $this->generateRandomString(1)));

                                $educatorObj = new EducatorUser();
                                $educatorObj->setFirstName(trim($educator['First Name']));
                                $educatorObj->setLastName(trim($educator['Last Name']));
                                $educatorObj->setSchool($school);
                                $educatorObj->setupAsEducator();
                                $educatorObj->setSite($user->getSite());
                                $educatorObj->initializeNewUser();
                                $educatorObj->setActivated(true);
                                $educatorObj->setEmail(trim($educator['Email']));
                                $educatorObj->setUsername(trim($username));
                                $educatorObj->setTempPassword($tempPassword);
                                $educatorObj->setPassword($encodedPassword);
                                $this->entityManager->persist($educatorObj);
                                $educatorObjs[] = $educatorObj;
                            }
                        }
                    }
                }

                // make sure any final records that came after the (($rowIndex % $batchSize) === 0) are flushed
                $this->entityManager->flush();
                $this->entityManager->commit();

            } catch (\Exception $exception) {
                $this->entityManager->rollback();
                $this->addFlash('error', sprintf('Error importing spreadsheet. (%s).', $exception->getMessage()));

                return $this->redirectToRoute('school_educator_import', ['id' => $school->getId()]);
            }

            $allEducators = array_merge($existingEducatorObjs, $educatorObjs);

            // send password reset emails
            /** @var EducatorUser $existingEducatorOb */
            foreach ($existingEducatorObjs as $existingEducatorObj) {
                $existingEducatorObj->setPasswordResetToken();
                $this->entityManager->persist($existingEducatorObj);
                //$this->securityMailer->sendPasswordReset($existingEducatorObj);
            }

            /** @var EducatorUser $educatorObj */
            foreach ($educatorObjs as $educatorObj) {
                $educatorObj->initializeNewUser(false, true);
                $this->entityManager->persist($educatorObj);
                //$this->securityMailer->sendPasswordSetup($educatorObj);
            }

            $this->entityManager->flush();

            if (!empty($allEducators)) {

                $data               = $this->serializer->serialize($allEducators, 'json', ['groups' => ['EDUCATOR_USER']]);
                $data               = json_decode($data, true);
                $attachmentFilePath = sys_get_temp_dir() . '/educators.csv';
                file_put_contents($attachmentFilePath, $this->serializer->encode($data, 'csv'));

                /* foreach ($school->getSchoolAdministrators() as $schoolAdministrator) {
                     $this->importMailer->educatorImportMailer($schoolAdministrator, $attachmentFilePath);
                 }*/
            }

            if (sizeof($error_emails) > 0) {
                $this->addFlash('error', $errors . join($error_emails, ', ') . ' they were not importred.');
            }
            $this->addFlash('success', sprintf('Educators successfully imported.'));

            return $this->redirectToRoute('school_educator_import', ['id' => $school->getId()]);
        }

        return $this->render('school/educator_import.html.twig', [
            'user'   => $user,
            'form'   => $form->createView(),
            'school' => $school,
        ]);
    }

    /**
     * @Route("/schools/{id}/photos/add", name="school_photos_add", options = { "expose" = true })
     * @param Request $request
     * @param School  $school
     *
     * @return JsonResponse
     */
    public function schoolAddPhotosAction(Request $request, School $school)
    {

        $this->denyAccessUnlessGranted('edit', $school);

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $photo = $request->files->get('file');

        if ($photo) {
            $mimeType    = $photo->getMimeType();
            $newFilename = $this->uploaderHelper->upload($photo, UploaderHelper::SCHOOL_PHOTO);
            $image       = new SchoolPhoto();
            $image->setOriginalName($photo->getClientOriginalName() ?? $newFilename);
            $image->setMimeType($mimeType ?? 'application/octet-stream');
            $image->setFileName($newFilename);
            $image->setSchool($school);
            $this->entityManager->persist($image);

            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::SCHOOL_PHOTO) . '/' . $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'url'     => $this->cacheManager->getBrowserPath('uploads/' . UploaderHelper::SCHOOL_PHOTO . '/' . $newFilename, 'squared_thumbnail_small'),
                'id'      => $image->getId(),
            ], Response::HTTP_OK);
        }

        return new JsonResponse([
            'success' => false,
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/schools/{id}/resource/add", name="school_resource_add", options = { "expose" = true })
     * @param Request $request
     * @param School  $school
     *
     * @return JsonResponse
     */
    public function schoolAddResourceAction(Request $request, School $school)
    {

        $this->denyAccessUnlessGranted('edit', $school);

        /** @var UploadedFile $file */
        $file          = $request->files->get('resource');
        $title         = $request->request->get('title');
        $linkToWebsite = $request->request->get('linkToWebsite');
        $description   = $request->request->get('description');

        if (!$file && !$linkToWebsite) {
            return new JsonResponse([
                'success' => false,

            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$title) {
            return new JsonResponse([
                'success' => false,

            ], Response::HTTP_BAD_REQUEST);
        }

        $schoolResource = new SchoolResource();

        if ($file) {
            $mimeType    = $file->getMimeType();
            $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::SCHOOL_RESOURCE);
            $schoolResource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
            $schoolResource->setMimeType($mimeType ?? 'application/octet-stream');
            $schoolResource->setFileName($newFilename);
            $schoolResource->setFile(null);
        }

        if ($linkToWebsite) {
            $schoolResource->setLinkToWebsite($linkToWebsite);
        }

        $schoolResource->setSchool($school);
        $schoolResource->setDescription($description ? $description : null);
        $schoolResource->setTitle($title);
        $this->entityManager->persist($schoolResource);
        $this->entityManager->flush();

        return new JsonResponse([
            'success'     => true,
            'url'         => $file ? $this->getFullQualifiedBaseUrl() . '/uploads/' . UploaderHelper::SCHOOL_RESOURCE . '/' . $newFilename : $schoolResource->getLinkToWebsite(),
            'id'          => $schoolResource->getId(),
            'title'       => $title,
            'description' => $description,

        ], Response::HTTP_OK);

        // $file = $request->files->get('resource');
        // $title = $request->request->get('title');
        // $description = $request->request->get('description');

        // if($file && $title) {
        //     $mimeType = $file->getMimeType();
        //     $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::SCHOOL_RESOURCE);
        //     $schoolResource = new SchoolResource();
        //     $schoolResource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
        //     $schoolResource->setMimeType($mimeType ?? 'application/octet-stream');
        //     $schoolResource->setFileName($newFilename);
        //     $schoolResource->setFile(null);
        //     $schoolResource->setSchool($school);
        //     $schoolResource->setDescription($description ? $description : null);
        //     $schoolResource->setTitle($title);
        //     $this->entityManager->persist($schoolResource);
        //     $this->entityManager->flush();

        //     return new JsonResponse(
        //         [
        //             'success' => true,
        //             'url' => $this->getFullQualifiedBaseUrl() . '/uploads/'.UploaderHelper::SCHOOL_RESOURCE.'/'.$newFilename,
        //             'id' => $schoolResource->getId(),
        //             'title' => $title,
        //             'description' => $description,

        //         ], Response::HTTP_OK
        //     );
        // }
    }

    /**
     * @Route("/schools/resource/{id}/remove", name="school_resource_remove", options = { "expose" = true })
     * @param Request        $request
     * @param SchoolResource $schoolResource
     *
     * @return JsonResponse
     */
    public function schoolRemoveResourceAction(Request $request, SchoolResource $schoolResource)
    {

        $this->denyAccessUnlessGranted('edit', $schoolResource->getSchool());

        $this->entityManager->remove($schoolResource);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,

        ], Response::HTTP_OK);
    }

    /**
     * @Route("/schools/resource/{id}/get", name="school_resource_get", options = { "expose" = true })
     * @param Request        $request
     * @param schoolResource $schoolResource
     *
     * @return JsonResponse
     */
    public function schoolGetResourceAction(Request $request, SchoolResource $schoolResource)
    {

        $this->denyAccessUnlessGranted('edit', $schoolResource->getSchool());

        if ($schoolResource->getFile() != null) {
            return new JsonResponse([
                'success'     => true,
                'url'         => $this->getFullQualifiedBaseUrl() . '/uploads/' . UploaderHelper::SCHOOL_RESOURCE . '/' . $schoolResource->getFileName(),
                'id'          => $schoolResource->getId(),
                'title'       => $schoolResource->getTitle(),
                'description' => $schoolResource->getDescription(),

            ], Response::HTTP_OK);
        } else {
            return new JsonResponse([
                'success'     => true,
                'website'     => $schoolResource->getLinkToWebsite(),
                'id'          => $schoolResource->getId(),
                'title'       => $schoolResource->getTitle(),
                'description' => $schoolResource->getDescription(),

            ], Response::HTTP_OK);
        }
    }

    /**
     * @Route("/schools/resources/{id}/edit", name="school_resource_edit", options = { "expose" = true })
     * @param Request        $request
     * @param SchoolResource $file
     *
     * @return JsonResponse
     */
    public function schoolEditResourceAction(Request $request, SchoolResource $file)
    {

        $this->denyAccessUnlessGranted('edit', $file->getSchool());

        /** @var UploadedFile $file */
        $resource      = $request->files->get('resource');
        $title         = $request->request->get('title');
        $description   = $request->request->get('description');
        $linkToWebsite = $request->request->get('linkToWebsite');

        if ($title) {
            $file->setTitle($title);
        }

        if ($description) {
            $file->setDescription($description);
        }

        if ($linkToWebsite && $linkToWebsite != "http://") {
            $file->setLinkToWebsite($linkToWebsite);
        } else {
            $file->setLinkToWebsite(null);
        }

        if ($resource) {
            $mimeType    = $resource->getMimeType();
            $newFilename = $this->uploaderHelper->upload($resource, UploaderHelper::SCHOOL_RESOURCE);
            $file->setOriginalName($resource->getClientOriginalName() ?? $newFilename);
            $file->setMimeType($mimeType ?? 'application/octet-stream');
            $file->setFileName($newFilename);
            $file->setFile(null);
        } else {
            $file->setOriginalName(null);
            $file->setMimeType(null);
            $file->setFileName(null);
        }

        $this->entityManager->persist($file);
        $this->entityManager->flush();

        if ($file->getFileName() != null) {
            return new JsonResponse([
                'success'     => true,
                'url'         => $this->getFullQualifiedBaseUrl() . '/uploads/' . UploaderHelper::SCHOOL_RESOURCE . '/' . $file->getFileName(),
                'id'          => $file->getId(),
                'title'       => $file->getTitle(),
                'description' => $file->getDescription(),

            ], Response::HTTP_OK);
        } else {
            return new JsonResponse([
                'success'     => true,
                'url'         => $file->getLinkToWebsite(),
                'id'          => $file->getId(),
                'title'       => $file->getTitle(),
                'description' => $file->getDescription(),

            ], Response::HTTP_OK);
        }


        // if($file && $title && $description) {
        //     $mimeType = $file->getMimeType();
        //     $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::SCHOOL_RESOURCE);
        //     $schoolResource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
        //     $schoolResource->setMimeType($mimeType ?? 'application/octet-stream');
        //     $schoolResource->setFileName($newFilename);
        //     $schoolResource->setFile(null);
        //     $schoolResource->setDescription($description);
        //     $schoolResource->setTitle($title);
        //     $this->entityManager->persist($schoolResource);
        //     $this->entityManager->flush();

        //     return new JsonResponse(
        //         [
        //             'success' => true,
        //             'url' => 'uploads/'.UploaderHelper::SCHOOL_RESOURCE.'/'.$newFilename,
        //             'id' => $schoolResource->getId(),
        //             'title' => $title,
        //             'description' => $description,

        //         ], Response::HTTP_OK
        //     );
        // }

        // return new JsonResponse(
        //     [
        //         'success' => false,

        //     ], Response::HTTP_BAD_REQUEST
        // );
    }

    /**
     * @Route("/schools/photos/{id}/remove", name="school_photo_remove", options = { "expose" = true })
     * @param Request     $request
     * @param SchoolPhoto $schoolPhoto
     *
     * @return JsonResponse
     */
    public function schoolRemovePhotoAction(Request $request, SchoolPhoto $schoolPhoto)
    {

        $this->denyAccessUnlessGranted('edit', $schoolPhoto->getSchool());

        $this->entityManager->remove($schoolPhoto);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,

        ], Response::HTTP_OK);
    }

    /**
     * @Route("/schools/videos/{id}/edit", name="school_video_edit", options = { "expose" = true })
     * @param Request     $request
     * @param SchoolVideo $video
     *
     * @return JsonResponse
     */
    public function schoolEditVideoAction(Request $request, SchoolVideo $video)
    {

        $this->denyAccessUnlessGranted('edit', $video->getSchool());

        $name    = $request->request->get('name');
        $videoId = $request->request->get('videoId');

        if ($name && $videoId) {
            $video->setName($name);
            $video->setVideoId($videoId);
            $this->entityManager->persist($video);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'id'      => $video->getId(),
                'name'    => $name,
                'videoId' => $videoId,

            ], Response::HTTP_OK);
        }

        return new JsonResponse([
            'success' => false,

        ], Response::HTTP_OK);
    }

    /**
     * @Route("/schools/{id}/video/add", name="school_video_add", options = { "expose" = true })
     * @param Request $request
     * @param School  $school
     *
     * @return JsonResponse
     */
    public function schoolAddVideoAction(Request $request, School $school)
    {

        $this->denyAccessUnlessGranted('edit', $school);

        $name    = $request->request->get('name');
        $videoId = $request->request->get('videoId');

        if ($name && $videoId) {
            $video = new SchoolVideo();
            $video->setName($name);
            $video->setVideoId($videoId);
            $video->setSchool($school);
            $this->entityManager->persist($video);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'id'      => $video->getId(),
                'name'    => $name,
                'videoId' => $videoId,

            ], Response::HTTP_OK);
        }

        return new JsonResponse([
            'success' => false,

        ], Response::HTTP_OK);
    }

    /**
     * @Route("/schools/videos/{id}/remove", name="school_video_remove", options = { "expose" = true })
     * @param Request     $request
     * @param SchoolVideo $schoolVideo
     *
     * @return JsonResponse
     */
    public function schoolRemoveVideoAction(Request $request, SchoolVideo $schoolVideo)
    {

        $this->denyAccessUnlessGranted('edit', $schoolVideo->getSchool());

        $this->entityManager->remove($schoolVideo);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,

        ], Response::HTTP_OK);
    }

    /**
     * @Route("/schools/{id}/experiences", name="get_school_experiences", options = { "expose" = true })
     * @param Request $request
     * @param School  $school
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function schoolExperiencesAction(Request $request, School $school)
    {
        /*$user = $this->getUser();*/

        $experiences = $this->schoolExperienceRepository->findBy([
            'school' => $school->getId(),
        ]);

        $data = $this->serializer->serialize($experiences, 'json', ['groups' => ['EXPERIENCE_DATA']]);
        $data = json_decode($data, true);

        return new JsonResponse([
            'success' => true,
            'data'    => $data,

        ], Response::HTTP_OK);
    }

    /**
     * @Route("/schools/experiences/{id}/data", name="school_experience_data", options = { "expose" = true })
     * @param Request          $request
     * @param SchoolExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dataExperienceAction(Request $request, SchoolExperience $experience)
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($experience->getSchoolContact() && $user->getId() === $experience->getSchoolContact()->getId()) {
            return new JsonResponse(['user_id' => $experience->getSchoolContact()->getId(), 'allow_edit' => true]);
        } else {
            return new JsonResponse(['user_id' => $experience->getSchoolContact()->getId(), 'allow_edit' => false]);
        }
    }

    /**
     * @param     $tempUsername
     * @param int $i
     *
     * @return mixed
     */
    private function determineUsername($tempUsername, $i = 1)
    {

        if ($this->userRepository->loadUserByUsername($tempUsername)) {
            return $this->determineUsername(sprintf("%s%s", $tempUsername, $this->generateRandomNumber($i)), ++$i);
        }

        return $tempUsername;
    }

    /**
     * @return mixed
     */
    private function determinePassword()
    {
        return sprintf("TEST%s", $this->generateRandomNumber(5));
    }

    /**
     * @param $tempPassword
     *
     * @return string
     */
    private function generateStudentTemporaryPassword($tempPassword)
    {

        return $this->passwordEncoder->encodePassword(new StudentUser(), $tempPassword);
    }

    /**
     * @param $tempPassword
     *
     * @return string
     */
    private function generateEducatorTemporaryPassword($tempPassword)
    {

        return $this->passwordEncoder->encodePassword(new EducatorUser(), $tempPassword);
    }

    /**
     * @Route("/schools/{id}/featured/add", name="school_featured_add", options = { "expose" = true })
     * @param Request $request
     * @param School  $school
     *
     * @return JsonResponse
     */
    public function schoolAddFeaturedAction(Request $request, School $school)
    {

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $featuredImage = $request->files->get('file');

        if ($featuredImage) {
            $mimeType    = $featuredImage->getMimeType();
            $newFilename = $this->uploaderHelper->upload($featuredImage, UploaderHelper::FEATURE_IMAGE);
            $image       = new Image();
            $image->setOriginalName($featuredImage->getClientOriginalName() ?? $newFilename);
            $image->setMimeType($mimeType ?? 'application/octet-stream');
            $image->setFileName($newFilename);
            $school->setFeaturedImage($image);
            $this->entityManager->persist($image);

            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::FEATURE_IMAGE) . '/' . $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            $this->entityManager->persist($school);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'url'     => $this->cacheManager->getBrowserPath('uploads/' . UploaderHelper::FEATURE_IMAGE . '/' . $newFilename, 'squared_thumbnail_small'),
                'id'      => $image->getId(),
            ], Response::HTTP_OK);
        }

        return new JsonResponse([
            'success' => false,
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/schools/{id}/thumbnail/add", name="school_thumbnail_add", options = { "expose" = true })
     * @param Request $request
     * @param School  $school
     *
     * @return JsonResponse
     */
    public function schoolAddThumbnailAction(Request $request, School $school)
    {

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $thumbnailImage = $request->files->get('file');

        if ($thumbnailImage) {
            $mimeType    = $thumbnailImage->getMimeType();
            $newFilename = $this->uploaderHelper->upload($thumbnailImage, UploaderHelper::THUMBNAIL_IMAGE);
            $image       = new Image();
            $image->setOriginalName($thumbnailImage->getClientOriginalName() ?? $newFilename);
            $image->setMimeType($mimeType ?? 'application/octet-stream');
            $image->setFileName($newFilename);
            $school->setThumbnailImage($image);
            $this->entityManager->persist($image);

            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::THUMBNAIL_IMAGE) . '/' . $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            $this->entityManager->persist($school);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'url'     => $this->cacheManager->getBrowserPath('uploads/' . UploaderHelper::THUMBNAIL_IMAGE . '/' . $newFilename, 'squared_thumbnail_small'),
                'id'      => $image->getId(),
            ], Response::HTTP_OK);
        }

        return new JsonResponse([
            'success' => false,
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/schools/experiences/{id}/toggle-feedback-view", name="toggle_school_feedback_view", options = { "expose" = true })
     * @param Request          $request
     * @param SchoolExperience $experience
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleCanViewFeedback(Request $request, SchoolExperience $experience)
    {

        $experience->setCanViewFeedback($request->request->get('val'));
        $this->entityManager->persist($experience);
        $this->entityManager->flush();

        return new JsonResponse(["status" => "success", "canView" => $request->request->get('val')]);
    }

    /**
     * @Route("/schools/{id}/educators/manage", name="educators_manage", methods={"GET"})
     * @param School  $school
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageEducatorsAction(School $school, Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $authorizationVoter = new AuthorizationVoter();

        if (!$authorizationVoter->canManageEducators($user)) {
            throw new AccessDeniedException();
        }

        $schools = new ArrayCollection();
        if ($user instanceof SchoolAdministrator) {
            $schools = $user->getSchools();
        } elseif ($user instanceof AdminUser) {
            $schools = $this->schoolRepository->findAll();
        }

        $schoolIds = [$school->getId()];

        $form = $this->createForm(ManageEducatorsFilterType::class, null, [
            'action'      => $this->generateUrl('educators_manage', ['id' => $school->getId()]),
            'method'      => 'GET',
            'filter_type' => EducatorUser::class,
            'schoolIds'   => $schoolIds,
        ]);

        $form->handleRequest($request);

        $filterBuilder = $this->educatorUserRepository->createQueryBuilder('u');
        $filterBuilder->addOrderBy('u.lastName', 'ASC');
        $filterBuilder->addOrderBy('u.firstName', 'ASC');
        $filterBuilder->andWhere('u.deleted = :deleted');
        $filterBuilder->setParameter('deleted', false);

        if (!empty($schoolIds)) {
            $filterBuilder->andWhere('u.school in (:schools)')->setParameter('schools', $schoolIds);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        if ($request->query->get('limit') === 'all') {
            $pagination = $this->paginator->paginate($filterQuery, /* query NOT result */ $request->query->getInt('page', 1), 100000000);
        } else {
            $pagination = $this->paginator->paginate($filterQuery, /* query NOT result */ $request->query->getInt('page', 1), /*page number*/ $request->query->getInt('limit', 10));
        }

        $user = $this->getUser();

        return $this->render('school/manage_educators.html.twig', [
            'user'         => $user,
            'pagination'   => $pagination,
            'form'         => $form->createView(),
            'schools'      => $schools,
            'school'       => $school,
            'clearFormUrl' => $this->generateUrl('educators_manage', ['id' => $school->getId()]),
        ]);
    }

    /**
     * @Route("/schools/students/manage-entry", name="students_manage_entry", methods={"GET"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageStudentsEntryAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $authorizationVoter = new AuthorizationVoter();

        if (!$authorizationVoter->canManageStudents($user)) {
            throw new AccessDeniedException();
        }

        $school = null;
        if ($user instanceof SchoolAdministrator) {
            $school = $user->getSchools()->first();
        } elseif ($user instanceof EducatorUser) {
            $school = $user->getSchool();
        }

        if($school) {

            if ($eventRegister = $request->query->get('event-register')) {
                return $this->redirectToRoute('students_manage', ['id' => $school->getId(), 'event-register' => $eventRegister]);
            }

            return $this->redirectToRoute('students_manage', ['id' => $school->getId()]);
        }

        throw new AccessDeniedException();
    }


    /**
     * @Route("/schools/{id}/students/manage", name="students_manage", methods={"GET"})
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageStudentsAction(School $school, Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $authorizationVoter = new AuthorizationVoter();

        if (!$authorizationVoter->canManageStudents($user)) {
            throw new AccessDeniedException();
        }

        if ($eventRegister = $request->query->get('event-register')) {
            $eventRegister = $this->experienceRepository->find($eventRegister);
        }

        if ($user instanceof SchoolAdministrator) {
            $schools = $user->getSchools();
        } elseif ($user instanceof EducatorUser) {
            $schools = new ArrayCollection([$user->getSchool()]);
        } elseif ($user instanceof AdminUser) {
            $schools = $this->schoolRepository->findAll();
        }

        $schoolIds = [$school->getId()];

        $form = $this->createForm(ManageStudentsFilterType::class, null, [
            'action'      => $this->generateUrl('students_manage', ['id' => $school->getId()]),
            'method'      => 'GET',
            'filter_type' => StudentUser::class,
            'schoolIds'   => $schoolIds,
            'eventRegister' => $eventRegister
        ]);

        $form->handleRequest($request);

        $filterBuilder = $this->studentUserRepository->createQueryBuilder('u');
        $filterBuilder->addOrderBy('u.lastName', 'ASC');
        $filterBuilder->addOrderBy('u.firstName', 'ASC');
        $filterBuilder->andWhere('u.deleted = :deleted and u.archived = :archived');
        $filterBuilder->setParameter('deleted', false);
        $filterBuilder->setParameter('archived', false);

        if (!empty($schoolIds)) {
            $filterBuilder->andWhere('u.school in (:schools)')->setParameter('schools', $schoolIds);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        if ($request->query->get('limit') === 'all') {
            $pagination = $this->paginator->paginate($filterQuery, /* query NOT result */ $request->query->getInt('page', 1), 100000000);
        } else {
            $pagination = $this->paginator->paginate($filterQuery, /* query NOT result */ $request->query->getInt('page', 1), /*page number*/ $request->query->getInt('limit', 10));
        }

        $user = $this->getUser();

        $clearFormUrl = $this->generateUrl('students_manage', ['id' => $school->getId()]);
        if($eventRegister) {
            $clearFormUrl = $this->generateUrl('students_manage', ['id' => $school->getId(), 'event-register' => $eventRegister->getId()]);
        }

        return $this->render('school/manage_students.html.twig', [
            'user'          => $user,
            'pagination'    => $pagination,
            'form'          => $form->createView(),
            'schools'       => $schools,
            'school'        => $school,
            'eventRegister' => $eventRegister,
            'clearFormUrl'  => $clearFormUrl,
        ]);
    }

    /**
     * @Route("/schools/{id}/users/bulk-action", name="school_users_bulk_action", methods={"GET", "POST"}, options = { "expose" = true })
     * @param School  $school
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function schoolUsersBulkAction(School $school, Request $request)
    {

        /** @var User $loggedInUser */
        $loggedInUser  = $this->getUser();
        $action        = $request->query->get('action');
        $redirectRoute = $request->query->get('redirectRoute', 'students_manage');

        if (!$action) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $bulkActionHandler = function () {
        };

        switch ($action) {

            case 'delete':

                $template = 'school/modal/bulk_action_delete_users.html.twig';

                $action = $this->generateUrl('school_users_bulk_action', array_merge_recursive(['id' => $school->getId()], $request->query->all()));

                $form = $this->createForm(AdHocFormType::class, null, [
                    'method' => 'post',
                    'action' => $action,
                ]);

                $context = [
                    'form'          => $form->createView(),
                    'loggedInUser'  => $loggedInUser,
                    'userCount'     => $request->query->get('userCount', 0),
                    'redirectRoute' => $redirectRoute,
                ];

                $bulkActionHandler = function () use (
                    $form, $loggedInUser, $request, $template, $school, &$context, $redirectRoute
                ) {

                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {

                        $userIds = $request->request->get('userIds', []);
                        $users   = $this->userRepository->findBy(['id' => $userIds]);

                        foreach ($users as $user) {
                            $this->entityManager->remove($user);
                        }

                        $this->entityManager->flush();

                        $queryParams = $request->query->all();
                        unset($queryParams['action']);
                        unset($queryParams['userCount']);

                        return new JsonResponse([
                            'redirectUrl' => $this->generateUrl($redirectRoute, array_merge_recursive(['id' => $school->getId()], $queryParams)),
                        ], Response::HTTP_OK);
                    }

                    $context = [
                        'form'          => $form->createView(),
                        'loggedInUser'  => $loggedInUser,
                        'userCount'     => $request->query->get('userCount', 0),
                        'redirectRoute' => $redirectRoute,
                    ];

                    return new JsonResponse([
                        'formMarkup' => $this->renderView($template, $context),
                    ], Response::HTTP_BAD_REQUEST);

                };

                break;

            case 'register':

                $template = 'school/modal/bulk_action_register_users.html.twig';

                $action = $this->generateUrl('school_users_bulk_action', array_merge_recursive(['id' => $school->getId()], $request->query->all()));

                $form = $this->createForm(AdHocFormType::class, null, [
                    'method' => 'post',
                    'action' => $action,
                ]);

                if ($eventRegister = $request->query->get('event-register')) {
                    $eventRegister = $this->experienceRepository->find($eventRegister);
                }

                $context = [
                    'form'          => $form->createView(),
                    'loggedInUser'  => $loggedInUser,
                    'userCount'     => $request->query->get('userCount', 0),
                    'redirectRoute' => $redirectRoute,
                    'eventRegister' => $eventRegister
                ];

                $bulkActionHandler = function () use (
                    $form, $loggedInUser, $request, $template, $school, &$context, $redirectRoute, $eventRegister
                ) {

                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {

                        $userIds = $request->request->get('userIds', []);
                        $users   = $this->userRepository->findBy(['id' => $userIds]);

                        foreach ($users as $user) {

                            if($eventRegister->isRegistered($user)) {
                                continue;
                            }

                            $registration = new Registration();
                            $registration->setExperience($eventRegister);
                            $registration->setUser($user);
                            $registration->setApproved(true);
                            $this->entityManager->persist($registration);
                        }

                        $this->entityManager->flush();

                        $queryParams = $request->query->all();
                        unset($queryParams['action']);
                        unset($queryParams['userCount']);
                        unset($queryParams['userId']);

                        return new JsonResponse([
                            'redirectUrl' => $this->generateUrl($redirectRoute, array_merge_recursive(['id' => $school->getId()], $queryParams)),
                        ], Response::HTTP_OK);
                    }

                    $context = [
                        'form'          => $form->createView(),
                        'loggedInUser'  => $loggedInUser,
                        'userCount'     => $request->query->get('userCount', 0),
                        'redirectRoute' => $redirectRoute,
                        'eventRegister' => $eventRegister
                    ];

                    return new JsonResponse([
                        'formMarkup' => $this->renderView($template, $context),
                    ], Response::HTTP_BAD_REQUEST);

                };

                break;

            case 'unregister':

                $template = 'school/modal/bulk_action_unregister_users.html.twig';

                $action = $this->generateUrl('school_users_bulk_action', array_merge_recursive(['id' => $school->getId()], $request->query->all()));

                $form = $this->createForm(AdHocFormType::class, null, [
                    'method' => 'post',
                    'action' => $action,
                ]);

                if ($eventRegister = $request->query->get('event-register')) {
                    $eventRegister = $this->experienceRepository->find($eventRegister);
                }

                $context = [
                    'form'          => $form->createView(),
                    'loggedInUser'  => $loggedInUser,
                    'userCount'     => $request->query->get('userCount', 0),
                    'redirectRoute' => $redirectRoute,
                    'eventRegister' => $eventRegister
                ];

                $bulkActionHandler = function () use (
                    $form, $loggedInUser, $request, $template, $school, &$context, $redirectRoute, $eventRegister
                ) {

                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {

                        $userIds = $request->request->get('userIds', []);
                        $users   = $this->userRepository->findBy(['id' => $userIds]);

                        foreach ($users as $user) {
                            if($registration = $eventRegister->getRegistrationForUser($user)) {
                                $this->entityManager->remove($registration);
                            }
                        }

                        $this->entityManager->flush();

                        $queryParams = $request->query->all();
                        unset($queryParams['action']);
                        unset($queryParams['userCount']);
                        unset($queryParams['userId']);

                        return new JsonResponse([
                            'redirectUrl' => $this->generateUrl($redirectRoute, array_merge_recursive(['id' => $school->getId()], $queryParams)),
                        ], Response::HTTP_OK);
                    }

                    $context = [
                        'form'          => $form->createView(),
                        'loggedInUser'  => $loggedInUser,
                        'userCount'     => $request->query->get('userCount', 0),
                        'redirectRoute' => $redirectRoute,
                        'eventRegister' => $eventRegister
                    ];

                    return new JsonResponse([
                        'formMarkup' => $this->renderView($template, $context),
                    ], Response::HTTP_BAD_REQUEST);

                };

                break;

            case 'reset_password':

                $template = 'school/modal/reset_password.html.twig';

                $action = $this->generateUrl('school_users_bulk_action', array_merge_recursive(['id' => $school->getId()], $request->query->all()));

                $resetPassword = new ResetPassword();

                $form = $this->createForm(ResetPasswordType::class, $resetPassword, [
                    'method' => 'post',
                    'action' => $action,
                ]);

                $context = [
                    'form'          => $form->createView(),
                    'loggedInUser'  => $loggedInUser,
                    'userCount'     => $request->query->get('userCount', 0),
                    'redirectRoute' => $redirectRoute,
                ];

                $bulkActionHandler = function () use (
                    $form, $loggedInUser, $request, $template, $school, &$context, $redirectRoute
                ) {

                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {

                        $userIds = $request->request->get('userIds', []);
                        $users   = $this->userRepository->findBy(['id' => $userIds]);

                        foreach ($users as $user) {
                            /** @var ResetPassword $resetPassword */
                            $resetPassword = $form->getData();

                            $user->setTempPasswordEncrypted($this->passwordEncoder->encodePassword($user, $resetPassword->getPassword()));

                            $user->setTempPassword($resetPassword->getPassword());
                            $user->clearPasswordResetToken();
                            $this->entityManager->persist($user);
                        }

                        $this->entityManager->flush();

                        $queryParams = $request->query->all();
                        unset($queryParams['action']);
                        unset($queryParams['userCount']);
                        unset($queryParams['userId']);

                        return new JsonResponse([
                            'redirectUrl' => $this->generateUrl($redirectRoute, array_merge_recursive(['id' => $school->getId()], $queryParams)),
                        ], Response::HTTP_OK);
                    }

                    $context = [
                        'form'          => $form->createView(),
                        'loggedInUser'  => $loggedInUser,
                        'userCount'     => $request->query->get('userCount', 0),
                        'redirectRoute' => $redirectRoute,
                    ];

                    return new JsonResponse([
                        'formMarkup' => $this->renderView($template, $context),
                    ], Response::HTTP_BAD_REQUEST);

                };

                break;

            case 'change_supervising_teacher':

                $template  = 'school/modal/supervising_teacher.html.twig';
                $studentId = $request->query->get('userId');
                $data      = null;

                if ($studentId && $student = $this->studentUserRepository->find($studentId)) {

                    $originalSupervisoringTeachers = new ArrayCollection();
                    foreach ($student->getEducatorUsers() as $educatorUser) {
                        $originalSupervisoringTeachers->add($educatorUser);
                    }

                    $data = [
                        'supervisingTeachers' => $originalSupervisoringTeachers,
                    ];
                }

                $action = $this->generateUrl('school_users_bulk_action', array_merge_recursive(['id' => $school->getId()], $request->query->all()));

                $form = $this->createForm(SupervisingTeacherFormType::class, $data, [
                    'method' => 'post',
                    'action' => $action,
                    'school' => $school,
                ]);

                $context = [
                    'form'         => $form->createView(),
                    'loggedInUser' => $loggedInUser,
                    'userCount'    => $request->query->get('userCount', 0),
                ];

                $bulkActionHandler = function () use (
                    $form, $loggedInUser, $request, $template, $school, &$context, $redirectRoute
                ) {

                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {

                        $studentIds = $request->request->get('userIds', []);
                        $students   = $this->studentUserRepository->findBy(['id' => $studentIds]);
                        $strategy   = $form->get('strategy')->getData();

                        foreach ($students as $student) {

                            $originalSupervisoringTeachers = new ArrayCollection();
                            foreach ($student->getEducatorUsers() as $educatorUser) {
                                $originalSupervisoringTeachers->add($educatorUser);
                            }

                            $supervisingTeachers = $form->get('supervisingTeachers')->getData();

                            if ($strategy === 'replace') {
                                foreach ($originalSupervisoringTeachers as $originalSupervisoringTeacher) {
                                    if (false === $supervisingTeachers->contains($originalSupervisoringTeacher)) {
                                        $student->removeEducatorUser($originalSupervisoringTeacher);
                                        $this->entityManager->persist($student);
                                    }
                                }
                            }

                            /** @var EducatorUser $supervisingTeacher */
                            foreach ($supervisingTeachers as $supervisingTeacher) {
                                if (false === $originalSupervisoringTeachers->contains($supervisingTeacher)) {
                                    $student->addEducatorUser($supervisingTeacher);
                                }
                            }

                            $this->entityManager->persist($student);
                        }

                        $this->entityManager->flush();

                        $queryParams = $request->query->all();
                        unset($queryParams['action']);
                        unset($queryParams['userCount']);
                        unset($queryParams['userId']);

                        return new JsonResponse([
                            'redirectUrl' => $this->generateUrl($redirectRoute, array_merge_recursive(['id' => $school->getId()], $queryParams)),
                        ], Response::HTTP_OK);
                    }

                    $context = [
                        'form'         => $form->createView(),
                        'loggedInUser' => $loggedInUser,
                        'userCount'    => $request->query->get('userCount', 0),
                    ];

                    return new JsonResponse([
                        'formMarkup' => $this->renderView($template, $context),
                    ], Response::HTTP_BAD_REQUEST);

                };

                break;

            case 'assign_students':

                $template   = 'school/modal/assign_students.html.twig';
                $educatorId = $request->query->get('userId');
                $data       = null;

                if ($educatorId && $educator = $this->educatorUserRepository->find($educatorId)) {

                    $originalAssignedStudents = new ArrayCollection();
                    foreach ($educator->getStudentUsers() as $studentUser) {
                        $originalAssignedStudents->add($studentUser);
                    }

                    $data = [
                        'assignedStudents' => $originalAssignedStudents,
                    ];
                }

                $action = $this->generateUrl('school_users_bulk_action', array_merge_recursive(['id' => $school->getId()], $request->query->all()));

                $form = $this->createForm(AssignedStudentsFormType::class, $data, [
                    'method' => 'post',
                    'action' => $action,
                    'school' => $school,
                ]);

                $context = [
                    'form'         => $form->createView(),
                    'loggedInUser' => $loggedInUser,
                    'userCount'    => $request->query->get('userCount', 0),
                ];

                $bulkActionHandler = function () use (
                    $form, $loggedInUser, $request, $template, $school, &$context, $redirectRoute
                ) {

                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {

                        $educatorIds = $request->request->get('userIds', []);
                        $educators   = $this->educatorUserRepository->findBy(['id' => $educatorIds]);
                        $strategy    = $form->get('strategy')->getData();

                        foreach ($educators as $educator) {

                            $originalAssignedStudents = new ArrayCollection();
                            foreach ($educator->getStudentUsers() as $studentUser) {
                                $originalAssignedStudents->add($studentUser);
                            }

                            $assignedStudents = $form->get('assignedStudents')->getData();

                            if ($strategy === 'replace') {
                                foreach ($originalAssignedStudents as $originalAssignedStudent) {
                                    if (false === $assignedStudents->contains($originalAssignedStudent)) {
                                        $educator->removeStudentUser($originalAssignedStudent);
                                        $this->entityManager->persist($educator);
                                    }
                                }
                            }

                            /** @var StudentUser $assignedStudent */
                            foreach ($assignedStudents as $assignedStudent) {
                                if (false === $originalAssignedStudents->contains($assignedStudent)) {
                                    $educator->addStudentUser($assignedStudent);
                                }
                            }

                            $this->entityManager->persist($educator);
                        }

                        $this->entityManager->flush();

                        $queryParams = $request->query->all();
                        unset($queryParams['action']);
                        unset($queryParams['userCount']);
                        unset($queryParams['userId']);

                        return new JsonResponse([
                            'redirectUrl' => $this->generateUrl($redirectRoute, array_merge_recursive(['id' => $school->getId()], $queryParams)),
                        ], Response::HTTP_OK);
                    }

                    $context = [
                        'form'         => $form->createView(),
                        'loggedInUser' => $loggedInUser,
                        'userCount'    => $request->query->get('userCount', 0),
                    ];

                    return new JsonResponse([
                        'formMarkup' => $this->renderView($template, $context),
                    ], Response::HTTP_BAD_REQUEST);

                };


                break;

        }


        if ($request->getMethod() === 'POST') {
            $response = $bulkActionHandler();

            if ($response instanceof JsonResponse) {
                return $response;
            }
        }

        return new JsonResponse([
            'formMarkup' => $this->renderView($template, $context),
        ], Response::HTTP_OK);
    }


}
