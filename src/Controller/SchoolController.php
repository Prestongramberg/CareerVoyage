<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\CompanyVideo;
use App\Entity\EducatorUser;
use App\Entity\ExperienceFile;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\SchoolPhoto;
use App\Entity\SchoolResource;
use App\Entity\SchoolVideo;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Entity\UserRegisterForSchoolExperienceRequest;
use App\Form\ChatFilterType;
use App\Form\ChatMessageFilterType;
use App\Form\EditCompanyFormType;
use App\Form\EditSchoolExperienceType;
use App\Form\EditSchoolType;
use App\Form\EducatorImportType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\NewSchoolExperienceType;
use App\Form\NewSchoolType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\SchoolAdminFormType;
use App\Form\SchoolCommunicationType;
use App\Form\StudentImportType;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
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
    use ServiceHelper;
    use RandomStringGenerator;

    /**
     * @Route("/schools", name="school_index", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request) {

        $user = $this->getUser();
        return $this->render('school/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Security("is_granted('ROLE_REGIONAL_COORDINATOR_USER')")
     * @Route("/schools/admin/new", name="school_admin_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function newAdminAction(Request $request) {

        /** @var RegionalCoordinator $user */
        $user = $this->getUser();
        $schoolAdmin = new SchoolAdministrator();

        $form = $this->createForm(SchoolAdminFormType::class, $schoolAdmin, [
            'method' => 'POST',
            'site' => $user->getSite(),
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var SchoolAdministrator $schoolAdmin */
            $schoolAdmin = $form->getData();
            $existingUser = $this->userRepository->getByEmailAddress($schoolAdmin->getEmail());

            if($existingUser) {
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

                foreach($schoolAdmin->getSchools() as $school) {
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
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newSchool(Request $request) {

        /** @var RegionalCoordinator $user */
        $user = $this->getUser();
        $school = new School();

        $form = $this->createForm(NewSchoolType::class, $school, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var School $school */
            $school = $form->getData();
            $school->setState($user->getRegion()->getState());
            $school->setRegion($user->getRegion());
            $school->setSite($user->getSite());

            $this->entityManager->persist($school);
            $this->entityManager->flush();


            $zipcode = $school->getZipcode();
            $radius = 50;
            $lng = null;
            $lat = null;

            if($zipcode &&  $coordinates = $this->geocoder->geocode($zipcode)) {
                $lng = $coordinates['lng'];
                $lat = $coordinates['lat'];
                list($latN, $latS, $lonE, $lonW) = $this->geocoder->calculateSearchSquare($lat, $lng, $radius);
                $professionals = $this->professionalUserRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng);
                $professionalIds = [];
                foreach($professionals as $professional) {
                    $professionalIds[] = $professional['id'];
                }
                $professionals = $this->professionalUserRepository->getByArrayOfIds($professionalIds);

                /** @var ProfessionalUser $professional */
                foreach($professionals as $professional) {
                    $professional->addSchool($school);
                }


                $companies = $this->companyRepository->findByRadius($latN, $latS, $lonE, $lonW, $lat, $lng);
                $companyIds = [];
                foreach($companies as $company) {
                    $companyIds[] = $company['id'];
                }
                $companies = $this->companyRepository->getByArrayOfIds($companyIds);

                /** @var Company $company */
                foreach($companies as $company) {
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
     * @param School $school
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function educatorsAction(Request $request, School $school) {

        $this->denyAccessUnlessGranted('edit', $school);

        return new Response("educators");
    }

    /**
     * @Route("/schools/educators/{id}/remove", name="remove_educator", methods={"POST"})
     * @param Request $request
     * @param EducatorUser $educatorUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeEducatorAction(Request $request, EducatorUser $educatorUser) {

        $this->denyAccessUnlessGranted('edit', $educatorUser->getSchool());

        $schoolAdminId = $request->request->get('schoolAdminId');

        $this->entityManager->remove($educatorUser);
        $this->entityManager->flush();

        $this->addFlash('success', 'Educator removed from school');

        return $this->redirectToRoute('dashboard');

    }

    /**
     * @Route("/schools/{id}/students", name="school_students")
     * @param Request $request
     * @param School $school
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function studentsAction(Request $request, School $school) {

        $this->denyAccessUnlessGranted('edit', $school);

        return new Response("students");
    }

    /**
     * @Route("/schools/{id}/delete", name="school_delete", options = { "expose" = true })
     * @param Request $request
     * @param School $school
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteSchoolAction(Request $request, School $school) {

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
     * @param Request $request
     * @param StudentUser $studentUser
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeStudentAction(Request $request, StudentUser $studentUser) {

        $this->denyAccessUnlessGranted('edit', $studentUser->getSchool());

        $schoolAdminId = $request->request->get('schoolAdminId');

        $this->entityManager->remove($studentUser);
        $this->entityManager->flush();

        return $this->redirectToRoute('dashboard');
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_STATE_COORDINATOR_USER", "ROLE_REGIONAL_COORDINATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER"})
     * @Route("/schools/{id}/edit", name="school_edit", options = { "expose" = true })
     * @param Request $request
     * @param School $school
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, School $school) {

        $this->denyAccessUnlessGranted('edit', $school);

        $user = $this->getUser();

        $form = $this->createForm(EditSchoolType::class, $school, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var School $school */
            $school = $form->getData();
            if($coordinates = $this->geocoder->geocode($school->getAddress())) {
                $school->setLongitude($coordinates['lng']);
                $school->setLatitude($coordinates['lat']);
            }
            $this->entityManager->persist($school);
            $this->entityManager->flush();
            $this->addFlash('success', sprintf('School successfully updated.'));
            return $this->redirectToRoute('school_edit', ['id' => $school->getId()]);
        }

        return $this->render('school/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'school' => $school,
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_STATE_COORDINATOR_USER", "ROLE_REGIONAL_COORDINATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER"})
     * @Route("/schools/{id}/communication-type", name="school_communication_type", options = { "expose" = true })
     * @param Request $request
     * @param School $school
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function communicationType(Request $request, School $school) {
        $this->denyAccessUnlessGranted('edit', $school);
        $user = $this->getUser();
        $form = $this->createForm(SchoolCommunicationType::class, $school, [
            'method' => 'POST',
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            /** @var School $school */
            $school = $form->getData();
            $this->entityManager->persist($school);
            $this->entityManager->flush();
            $this->addFlash('success', sprintf('School communication type successfully updated.'));
            return $this->redirectToRoute('school_communication_type', ['id' => $school->getId()]);
        }
        return $this->render('school/communication_type.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'school' => $school,
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_STATE_COORDINATOR_USER", "ROLE_REGIONAL_COORDINATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER", "ROLE_EDUCATOR_USER"})
     * @Route("/schools/{id}/chats", name="school_chat", options = { "expose" = true })
     * @param Request $request
     * @param School $school
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function chats(Request $request, School $school) {
    	/** @var User $user */
        $user = $this->getUser();
		if($user->isEducator() && $user->getSchool()->getId() !== $school->getId()) {
			throw new AccessDeniedException();
		} else if(!$user->isEducator()) {
			$this->denyAccessUnlessGranted('edit', $school);
		}
        $form = $this->createForm(ChatFilterType::class, null, [
            'action' => $this->generateUrl('school_chat', ['id' => $school->getId()]),
            'method' => 'GET',
        ]);

        $form->handleRequest($request);
        $studentIds = [];
        foreach($school->getStudentUsers() as $studentUser) {
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

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('school/chat.html.twig', [
            'user' => $user,
            'school' => $school,
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_SITE_ADMIN_USER", "ROLE_STATE_COORDINATOR_USER", "ROLE_REGIONAL_COORDINATOR_USER", "ROLE_SCHOOL_ADMINISTRATOR_USER", "ROLE_EDUCATOR_USER"})
     * @Route("/schools/{id}/chats/{chatId}/messages", name="school_chat_messages", options = { "expose" = true })
     * @param Request $request
     * @param School $school
     * @param Chat $chat
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function chatMessages(Request $request, School $school, Chat $chat) {

	    $user = $this->getUser();
	    if($user->isEducator() && $user->getSchool()->getId() !== $school->getId()) {
		    throw new AccessDeniedException();
	    } else if(!$user->isEducator()) {
		    $this->denyAccessUnlessGranted('edit', $school);
	    }

        $form = $this->createForm(ChatMessageFilterType::class, null, [
            'action' => $this->generateUrl('school_chat_messages', ['id' => $school->getId(), 'chatId' => $chat->getId()]),
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

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('school/chat_messages.html.twig', [
            'user' => $user,
            'school' => $school,
            'pagination' => $pagination,
            'chat' => $chat,
            'form' => $form->createView(),
            'clearFormUrl' => $this->generateUrl('school_chat_messages', ['id' => $school->getId(), 'chatId' => $chat->getId()]),
        ]);
    }

    /**
     * @Route("/schools/{id}/view", name="school_view", options = { "expose" = true })
     * @param Request $request
     * @param School $school
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, School $school) {

        $user = $this->getUser();

        $volunteeringCompanies = $this->companyRepository->getBySchool($school);

        return $this->render('school/view.html.twig', [
            'user' => $user,
            'school' => $school,
	        'volunteeringCompanies' => $volunteeringCompanies,
        ]);
    }

    /**
     * @Security("is_granted('ROLE_SCHOOL_ADMINISTRATOR_USER')")
     * @Route("/schools/{id}/students/import", name="school_student_import")
     * @param Request $request
     * @param School $school
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function studentImportAction(Request $request, School $school) {

        $this->denyAccessUnlessGranted('edit', $school);

        /** @var SchoolAdministrator $user */
        $user = $this->getUser();

        $form = $this->createForm(StudentImportType::class, null, [
            'method' => 'POST',
            'school' => $school,
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $file = $form->get('file')->getData();
            $columns = $this->phpSpreadsheetHelper->getColumnNames($file);
            // capitalize each word in each item in array so we can assure a proper comparision
            $columns = array_map('ucwords', $columns);
            $expectedColumns = ['First Name', 'Last Name', 'Graduating Year',  'Educator Number'];
            if($columns != $expectedColumns) {
                $this->addFlash('error', sprintf('Column names need to be exactly: %s', implode(",", $expectedColumns)));
                return $this->redirectToRoute('school_student_import', ['id' => $school->getId()]);
            }
            $rows = $this->phpSpreadsheetHelper->getAllRows($file);
            $columns = array_shift($rows);
            $columns = array_map('ucwords', $columns);
            $students = [];
            for($i = 0; $i < count($rows); $i++) {
                $students[] = array_combine($columns, $rows[$i]);
            }
            foreach($students as $student) {

                // if any column in the student array is null let's assume they were not setup properly and skip adding them
                if(in_array(null, $student)) {
                    continue;
                }

                $usernameToFind = strtolower($student['First Name'] . '.' . $student['Last Name']);
                $similarUsernames = $this->userRepository->createQueryBuilder('u')
                    ->where('u.username LIKE :username')
                    ->setParameter('username', '%'.$usernameToFind.'%')
                    ->getQuery()
                    ->getResult();
                $similarUsernames = count($similarUsernames);

                $studentObj = new StudentUser();
                $studentObj->setFirstName($student['First Name']);
                $studentObj->setLastName($student['Last Name']);
                $studentObj->setGraduatingYear($student['Graduating Year']);
                // add the educator to the user if the educator id is included in the import
                if(!empty($student['Educator Number'])) {
                    $educator = $this->educatorUserRepository->findOneBy([
                        'id' => $student['Educator Number'],
                        'school' => $school,
                    ]);
                    if($educator) {
                        $studentObj->addEducatorUser($educator);
                    } else {
                        $this->addFlash('error', sprintf('Error importing students. Educator Number %s does not belong to an educator for school %s. Check educator Number list below', $student['Educator Number'], $school->getName()));
                        return $this->redirectToRoute('school_student_import', ['id' => $school->getId()]);
                    }
                }

                $studentObj->setSchool($school);
                $studentObj->setSite($user->getSite());
                $studentObj->setupAsStudent();
                $studentObj->initializeNewUser();
                $studentObj->setActivated(true);
                $studentObj->setUsername($this->determineUsername($studentObj->getTempUsername($similarUsernames++)));
                $tempPassword = $this->determinePassword();
                $encodedPassword = $this->passwordEncoder->encodePassword($studentObj, $tempPassword);
                $studentObj->setTempPassword($tempPassword);
                $studentObj->setPassword($encodedPassword);

                $this->entityManager->persist($studentObj);
                $studentObjs[] = $studentObj;
            }

            $this->entityManager->flush();

            $data = $this->serializer->serialize($studentObjs, 'json', ['groups' => ['STUDENT_USER']]);
            $data = json_decode($data, true);
            $attachmentFilePath = sys_get_temp_dir() . '/students.csv';
            file_put_contents(
                $attachmentFilePath,
                $this->serializer->encode($data, 'csv')
            );

            foreach($school->getSchoolAdministrators() as $schoolAdministrator) {
                $this->importMailer->studentImportMailer($schoolAdministrator, $attachmentFilePath);
            }

            $this->addFlash('success', sprintf('Students successfully imported.'));
            return $this->redirectToRoute('school_student_import', ['id' => $school->getId()]);
        }

        return $this->render('school/student_import.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'school' => $school,
        ]);
    }

    /**
     * @Security("is_granted('ROLE_SCHOOL_ADMINISTRATOR_USER')")
     * @Route("/schools/{id}/educators/import", name="school_educator_import")
     * @param Request $request
     * @param School $school
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function educatorImportAction(Request $request, School $school) {

        $this->denyAccessUnlessGranted('edit', $school);

        /** @var SchoolAdministrator $user */
        $user = $this->getUser();

        $form = $this->createForm(EducatorImportType::class, null, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $file = $form->get('file')->getData();
            $columns = $this->phpSpreadsheetHelper->getColumnNames($file);
            // capitalize each word in each item in array so we can assure a proper comparision
            $columns = array_map('ucwords', $columns);
            $expectedColumns = ['First Name', 'Last Name', 'Email'];
            if($columns != $expectedColumns) {
                $this->addFlash('error', sprintf('Column names need to be exactly: %s', implode(",", $expectedColumns)));
                return $this->redirectToRoute('school_educator_import', ['id' => $school->getId()]);
            }
            $rows = $this->phpSpreadsheetHelper->getAllRows($file);
            $columns = array_shift($rows);
            $columns = array_map('ucwords', $columns);
            $educators = [];
            for($i = 0; $i < count($rows); $i++) {
                $educators[] = array_combine($columns, $rows[$i]);
            }
            $educatorObjs = [];
            foreach($educators as $educator) {

                // if any column in the student array is null let's assume they were not setup properly and skip adding them
                if(in_array(null, $educator)) {
                    continue;
                }

                $email = $educator['Email'];
                $existingUser = $this->userRepository->findOneBy([
                    'email' => $email,
                ]);

                $usernameToFind = strtolower($educator['First Name'] . '.' . $educator['Last Name']);
                $similarUsernames = $this->userRepository->createQueryBuilder('u')
                    ->where('u.username LIKE :username')
                    ->setParameter('username', '%'.$usernameToFind.'%')
                    ->getQuery()
                    ->getResult();
                $similarUsernames = count($similarUsernames);

                if($existingUser) {
                    $this->addFlash('error', sprintf('Error importing educators. Email %s already exists in the system and belongs to another educator', $existingUser->getEmail()));
                    return $this->redirectToRoute('school_educator_import', ['id' => $school->getId()]);
                }
                $educatorObj = new EducatorUser();
                $educatorObj->setFirstName($educator['First Name']);
                $educatorObj->setLastName($educator['Last Name']);
                $educatorObj->setSchool($school);
                $educatorObj->setupAsEducator();
                $educatorObj->setSite($user->getSite());
                $educatorObj->initializeNewUser();
                $educatorObj->setActivated(true);
                $educatorObj->setEmail($educator['Email']);
                $educatorObj->setUsername($this->determineUsername($educatorObj->getTempUsername($similarUsernames++)));
                $tempPassword = $this->determinePassword();
                $encodedPassword = $this->passwordEncoder->encodePassword($educatorObj, $tempPassword);
                $educatorObj->setTempPassword($tempPassword);
                $educatorObj->setPassword($encodedPassword);
                $this->entityManager->persist($educatorObj);
                $educatorObjs[] = $educatorObj;
            }

            $this->entityManager->flush();

            $data = $this->serializer->serialize($educatorObjs, 'json', ['groups' => ['EDUCATOR_USER']]);
            $data = json_decode($data, true);
            $attachmentFilePath = sys_get_temp_dir() . '/educators.csv';
            file_put_contents(
                $attachmentFilePath,
                $this->serializer->encode($data, 'csv')
            );

            foreach($school->getSchoolAdministrators() as $schoolAdministrator) {
                $this->importMailer->educatorImportMailer($schoolAdministrator, $attachmentFilePath);
            }

            $this->addFlash('success', sprintf('Educators successfully imported.'));
            return $this->redirectToRoute('school_educator_import', ['id' => $school->getId()]);
        }

        return $this->render('school/educator_import.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'school' => $school,
        ]);
    }

    /**
     * @Route("/schools/{id}/photos/add", name="school_photos_add", options = { "expose" = true })
     * @param Request $request
     * @param School $school
     * @return JsonResponse
     */
    public function schoolAddPhotosAction(Request $request, School $school) {

        $this->denyAccessUnlessGranted('edit', $school);

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $photo = $request->files->get('file');

        if($photo) {
            $mimeType = $photo->getMimeType();
            $newFilename = $this->uploaderHelper->upload($photo, UploaderHelper::SCHOOL_PHOTO);
            $image = new SchoolPhoto();
            $image->setOriginalName($photo->getClientOriginalName() ?? $newFilename);
            $image->setMimeType($mimeType ?? 'application/octet-stream');
            $image->setFileName($newFilename);
            $image->setSchool($school);
            $this->entityManager->persist($image);

            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::SCHOOL_PHOTO) .'/'. $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->cacheManager->getBrowserPath('uploads/'.UploaderHelper::SCHOOL_PHOTO.'/'.$newFilename, 'squared_thumbnail_small'),
                    'id' => $image->getId(),
                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,
            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/schools/{id}/resource/add", name="school_resource_add", options = { "expose" = true })
     * @param Request $request
     * @param School $school
     * @return JsonResponse
     */
    public function schoolAddResourceAction(Request $request, School $school) {

        $this->denyAccessUnlessGranted('edit', $school);

        /** @var UploadedFile $file */
        $file = $request->files->get('resource');
        $title = $request->request->get('title');
        $description = $request->request->get('description');

        if($file && $title) {
            $mimeType = $file->getMimeType();
            $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::SCHOOL_RESOURCE);
            $schoolResource = new SchoolResource();
            $schoolResource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
            $schoolResource->setMimeType($mimeType ?? 'application/octet-stream');
            $schoolResource->setFileName($newFilename);
            $schoolResource->setFile(null);
            $schoolResource->setSchool($school);
            $schoolResource->setDescription($description ? $description : null);
            $schoolResource->setTitle($title);
            $this->entityManager->persist($schoolResource);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->getFullQualifiedBaseUrl() . '/uploads/'.UploaderHelper::SCHOOL_RESOURCE.'/'.$newFilename,
                    'id' => $schoolResource->getId(),
                    'title' => $title,
                    'description' => $description,

                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,

            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/schools/resource/{id}/remove", name="school_resource_remove", options = { "expose" = true })
     * @param Request $request
     * @param SchoolResource $schoolResource
     * @return JsonResponse
     */
    public function schoolRemoveResourceAction(Request $request, SchoolResource $schoolResource) {

        $this->denyAccessUnlessGranted('edit', $schoolResource->getSchool());

        $this->entityManager->remove($schoolResource);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/schools/resources/{id}/edit", name="school_resource_edit", options = { "expose" = true })
     * @param Request $request
     * @param SchoolResource $schoolResource
     * @return JsonResponse
     */
    public function schoolEditResourceAction(Request $request, SchoolResource $schoolResource) {

        $this->denyAccessUnlessGranted('edit', $schoolResource->getSchool());

        /** @var UploadedFile $file */
        $file = $request->files->get('resource');
        $title = $request->request->get('title');
        $description = $request->request->get('description');

        if($file && $title && $description) {
            $mimeType = $file->getMimeType();
            $newFilename = $this->uploaderHelper->upload($file, UploaderHelper::SCHOOL_RESOURCE);
            $schoolResource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
            $schoolResource->setMimeType($mimeType ?? 'application/octet-stream');
            $schoolResource->setFileName($newFilename);
            $schoolResource->setFile(null);
            $schoolResource->setDescription($description);
            $schoolResource->setTitle($title);
            $this->entityManager->persist($schoolResource);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => 'uploads/'.UploaderHelper::SCHOOL_RESOURCE.'/'.$newFilename,
                    'id' => $schoolResource->getId(),
                    'title' => $title,
                    'description' => $description,

                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,

            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/schools/photos/{id}/remove", name="school_photo_remove", options = { "expose" = true })
     * @param Request $request
     * @param SchoolPhoto $schoolPhoto
     * @return JsonResponse
     */
    public function schoolRemovePhotoAction(Request $request, SchoolPhoto $schoolPhoto) {

        $this->denyAccessUnlessGranted('edit', $schoolPhoto->getSchool());

        $this->entityManager->remove($schoolPhoto);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/schools/videos/{id}/edit", name="school_video_edit", options = { "expose" = true })
     * @param Request $request
     * @param SchoolVideo $video
     * @return JsonResponse
     */
    public function schoolEditVideoAction(Request $request, SchoolVideo $video) {

        $this->denyAccessUnlessGranted('edit', $video->getSchool());

        $name = $request->request->get('name');
        $videoId = $request->request->get('videoId');

        if($name && $videoId) {
            $video->setName($name);
            $video->setVideoId($videoId);
            $this->entityManager->persist($video);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'id' => $video->getId(),
                    'name' => $name,
                    'videoId' => $videoId,

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
     * @Route("/schools/{id}/video/add", name="school_video_add", options = { "expose" = true })
     * @param Request $request
     * @param School $school
     * @return JsonResponse
     */
    public function schoolAddVideoAction(Request $request, School $school) {

        $this->denyAccessUnlessGranted('edit', $school);

        $name = $request->request->get('name');
        $videoId = $request->request->get('videoId');

        if($name && $videoId) {
            $video = new SchoolVideo();
            $video->setName($name);
            $video->setVideoId($videoId);
            $video->setSchool($school);
            $this->entityManager->persist($video);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'id' => $video->getId(),
                    'name' => $name,
                    'videoId' => $videoId,

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
     * @Route("/schools/videos/{id}/remove", name="school_video_remove", options = { "expose" = true })
     * @param Request $request
     * @param SchoolVideo $schoolVideo
     * @return JsonResponse
     */
    public function schoolRemoveVideoAction(Request $request, SchoolVideo $schoolVideo) {

        $this->denyAccessUnlessGranted('edit', $schoolVideo->getSchool());

        $this->entityManager->remove($schoolVideo);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/schools/{id}/experiences", name="get_school_experiences", options = { "expose" = true })
     * @param Request $request
     * @param School $school
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function schoolExperiencesAction(Request $request, School $school) {

        /*$user = $this->getUser();*/

        $experiences = $this->schoolExperienceRepository->findBy([
            'school' => $school->getId(),
        ]);

        $data = $this->serializer->serialize($experiences, 'json', ['groups' => ['EXPERIENCE_DATA']]);
        $data = json_decode($data, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $data,

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/schools/{id}/experiences/create", name="school_experience_create", options = { "expose" = true })
     * @param Request $request
     * @param School $school
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createSchoolExperienceAction(Request $request, School $school) {

        $this->denyAccessUnlessGranted('edit', $school);

        $user = $this->getUser();

        $experience = new SchoolExperience();
        $form = $this->createForm(NewSchoolExperienceType::class, $experience, [
            'method' => 'POST',
            'school' => $school,
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var SchoolExperience $experience */
            $experience = $form->getData();

            $shouldAttemptGeocode = $experience->getStreet() && $experience->getCity() && $experience->getState() && $experience->getZipcode();
            if($shouldAttemptGeocode && $coordinates = $this->geocoder->geocode($experience->getFormattedAddress())) {
                $experience->setLongitude($coordinates['lng']);
                $experience->setLatitude($coordinates['lat']);
            }

            $this->entityManager->persist($experience);

            $experience->setSchool($school);

            $this->entityManager->flush();

            $this->addFlash('success', 'Experience successfully created!');

            return $this->redirectToRoute('school_experience_view', ['id' => $experience->getId()]);
        }

        return $this->render('school/new_experience.html.twig', [
            'school' => $school,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/schools/experiences/{id}/edit", name="school_experience_edit", options = { "expose" = true })
     * @param Request $request
     * @param SchoolExperience $experience
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editSchoolExperienceAction(Request $request, SchoolExperience $experience) {

        $school = $experience->getSchool();
        $this->denyAccessUnlessGranted('edit', $school);

        $user = $this->getUser();

        $form = $this->createForm(EditSchoolExperienceType::class, $experience, [
            'method' => 'POST',
            'school' => $school,
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var SchoolExperience $experience */
            $experience = $form->getData();

            $shouldAttemptGeocode = $experience->getStreet() && $experience->getCity() && $experience->getState() && $experience->getZipcode();
            if($shouldAttemptGeocode && $coordinates = $this->geocoder->geocode($experience->getFormattedAddress())) {
                $experience->setLongitude($coordinates['lng']);
                $experience->setLatitude($coordinates['lat']);
            }

            $this->entityManager->persist($experience);
            $experience->setSchool($school);

            $this->entityManager->flush();

            $this->addFlash('success', 'Experience successfully updated!');

            return $this->redirectToRoute('school_experience_edit', ['id' => $experience->getId()]);
        }

        return $this->render('school/edit_experience.html.twig', [
            'school' => $school,
            'form' => $form->createView(),
            'user' => $user,
	        'experience' => $experience,
        ]);
    }

    /**
     * @Route("/schools/experiences/{id}/remove", name="school_experience_remove", options = { "expose" = true })
     * @param Request $request
     * @param SchoolExperience $experience
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function experienceRemoveAction(Request $request, SchoolExperience $experience) {

        $this->denyAccessUnlessGranted('edit', $experience->getSchool());

        $message = $request->request->get('cancellationMessage');

        $registrations = $experience->getRegistrations();

        foreach ($registrations as $registration) {
            $this->experienceMailer->experienceCancellationMessage($experience, $registration->getUser(), $message);
        }

        $this->entityManager->remove($experience);
        $this->entityManager->flush();

        $this->entityManager->remove($experience);
        $this->entityManager->flush();

        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/schools/experiences/{id}/notify-companies", name="school_notify_companies", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     * @param SchoolExperience $experience
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function experienceNotifyCompaniesAction(Request $request, SchoolExperience $experience) {

        $this->denyAccessUnlessGranted('edit', $experience->getSchool());

        $companyIds = $request->request->get('companies');

        if(empty($companyIds)) {
            $this->addFlash('error', 'Please select at least one company to notify.');
            return $this->redirectToRoute('school_experience_view', ['id' => $experience->getId()]);
        }

        $customMessage = $request->request->get('customMessage', '');

        $companies = $this->companyRepository->findBy([
            'id' => $companyIds,
        ]);

        /** @var Company $company */
        foreach($companies as $company) {
            $this->notificationsMailer->notifyCompanyOwnerOfSchoolEvent($company->getOwner(), $experience, $customMessage);
        }
        $this->addFlash('success', 'Companies notified of event.');
        return $this->redirectToRoute('school_experience_view', ['id' => $experience->getId()]);
    }

    /**
     * @Route("/schools/experiences/{id}/view", name="school_experience_view", options = { "expose" = true })
     * @param Request $request
     * @param SchoolExperience $experience
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewExperienceAction(Request $request, SchoolExperience $experience) {

        $user = $this->getUser();

        return $this->render('school/view_experience.html.twig', [
            'user' => $user,
            'experience' => $experience,
            'school' => $experience->getSchool(),
        ]);
    }

    /**
     * @Route("/schools/experiences/{id}/register", name="school_experience_register", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     * @param SchoolExperience $experience
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function schoolExperienceRegisterAction(Request $request, SchoolExperience $experience) {
        /** @var User $user */
        $user = $this->getUser();

        $userIdToRegister = $request->request->get('userId');
        $userToRegister = $this->userRepository->find($userIdToRegister);

        $request = $this->userRegisterForSchoolExperienceRequestRepository->findOneBy([
            'user' => $userToRegister,
            'schoolExperience' => $experience,
        ]);
        if($request) {
            $this->addFlash('error', 'Registration request already sent for this event.');
            return $this->redirectToRoute('school_experience_view', ['id' => $experience->getId()]);
        }
        if($userToRegister->isProfessional() && $experience->getAvailableProfessionalSpaces() === 0) {
            $this->addFlash('error', 'Could not register for event. 0 spots left.');
            return $this->redirectToRoute('school_experience_view', ['id' => $experience->getId()]);
        }

        if($userToRegister->isStudent() && $experience->getAvailableStudentSpaces() === 0) {
            $this->addFlash('error', 'Could not register for event. 0 spots left.');
            return $this->redirectToRoute('school_experience_view', ['id' => $experience->getId()]);
        }
        $registerRequest = new UserRegisterForSchoolExperienceRequest();
        $registerRequest->setCreatedBy($user);
        $registerRequest->setNeedsApprovalBy($experience->getSchoolContact());
        $registerRequest->setSchoolExperience($experience);
        $registerRequest->setUser($userToRegister);
        $this->entityManager->persist($registerRequest);
        $this->entityManager->flush();
        $this->requestsMailer->userRegisterForSchoolExperienceRequest($registerRequest);
        $this->addFlash('success', 'Registration request successfully sent.');
        return $this->redirectToRoute('school_experience_view', ['id' => $experience->getId()]);
    }

    /**
     * @Route("/schools/experiences/{id}/deregister", name="school_experience_deregister", options = { "expose" = true }, methods={"POST"})
     * @param Request $request
     * @param SchoolExperience $experience
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function schoolExperienceDeregisterAction(Request $request, SchoolExperience $experience) {
        $userIdToDeregister = $request->request->get('userId');
        $userToDeregister = $this->userRepository->find($userIdToDeregister);

        $deregisterUserForExperience = $this->userRegisterForSchoolExperienceRequestRepository->getByUserAndExperience($userToDeregister, $experience);

        $registration = $this->registrationRepository->getByUserAndExperience($userToDeregister, $experience);

        if($registration) {
            if ($userToDeregister->isStudent()) {
                /** @var StudentUser $userToDeregister */
                $experience->setAvailableStudentSpaces($experience->getAvailableStudentSpaces() + 1);

                if($userToDeregister->getEmail()) {
                    $this->requestsMailer->userDeregisterFromEvent($userToDeregister, $userToDeregister, $experience);
                }

                foreach($userToDeregister->getSchool()->getSchoolAdministrators() as $schoolAdministrator) {
                    $this->requestsMailer->userDeregisterFromEvent($userToDeregister, $schoolAdministrator, $experience);
                }

                foreach($userToDeregister->getEducatorUsers() as $educatorUser) {
                    $this->requestsMailer->userDeregisterFromEvent($userToDeregister, $educatorUser, $experience);
                }



            } else if ($userToDeregister->isProfessional()) {

                if($userToDeregister->getEmail()) {
                    $this->requestsMailer->userDeregisterFromEvent($userToDeregister, $userToDeregister, $experience);
                }

                foreach($experience->getSchool()->getSchoolAdministrators() as $schoolAdministrator) {
                    $this->requestsMailer->userDeregisterFromEvent($userToDeregister, $schoolAdministrator, $experience);
                }

                $experience->setAvailableProfessionalSpaces($experience->getAvailableProfessionalSpaces() + 1);
            }

            $this->entityManager->remove($deregisterUserForExperience);
            $this->entityManager->remove($registration);
            $this->entityManager->persist($experience);
            $this->entityManager->flush();
        }

        $this->addFlash('success', 'User has been removed from this experience.');
        return $this->redirectToRoute('school_experience_view', ['id' => $experience->getId()]);
    }

    /**
     * @Route("/schools/experiences/{id}/file/add", name="school_experience_file_add", options = { "expose" = true })
     * @param Request $request
     * @param SchoolExperience $experience
     * @return JsonResponse
     */
    public function schoolExperienceAddFileAction(Request $request, SchoolExperience $experience) {

        $this->denyAccessUnlessGranted('edit', $experience->getSchool());

        /** @var UploadedFile $resource */
        $resource = $request->files->get('resource');
        $title = $request->request->get('title');
        $description = $request->request->get('description');

        if($resource && $title && $description) {
            $mimeType = $resource->getMimeType();
            $newFilename = $this->uploaderHelper->upload($resource, UploaderHelper::EXPERIENCE_FILE);
            $file = new ExperienceFile();
            $file->setOriginalName($resource->getClientOriginalName() ?? $newFilename);
            $file->setMimeType($mimeType ?? 'application/octet-stream');
            $file->setFileName($newFilename);
            $file->setFile(null);
            $file->setExperience($experience);
            $file->setDescription($description);
            $file->setTitle($title);
            $this->entityManager->persist($file);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->getFullQualifiedBaseUrl() . '/uploads/'.UploaderHelper::EXPERIENCE_FILE.'/'.$newFilename,
                    'id' => $file->getId(),
                    'title' => $title,
                    'description' => $description,

                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,

            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/schools/experiences/file/{id}/edit", name="school_experience_file_edit", options = { "expose" = true })
     * @param Request $request
     * @param ExperienceFile $file
     * @return JsonResponse
     */
    public function schoolExperienceEditFileAction(Request $request, ExperienceFile $file) {

        $this->denyAccessUnlessGranted('edit', $file->getExperience()->getSchool());

        /** @var UploadedFile $resource */
        $resource = $request->files->get('resource');
        $title = $request->request->get('title');
        $description = $request->request->get('description');

        if($title) {
            $file->setTitle($title);
        }

        if($description) {
            $file->setDescription($description);
        }

        if($resource) {
            $mimeType = $resource->getMimeType();
            $newFilename = $this->uploaderHelper->upload($resource, UploaderHelper::EXPERIENCE_FILE);
            $file->setOriginalName($resource->getClientOriginalName() ?? $newFilename);
            $file->setMimeType($mimeType ?? 'application/octet-stream');
            $file->setFileName($newFilename);
            $file->setFile(null);
        }

        $this->entityManager->persist($file);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
                'url' => $this->getFullQualifiedBaseUrl() . '/uploads/'.UploaderHelper::EXPERIENCE_FILE.'/'. $file->getFileName(),
                'id' => $file->getId(),
                'title' => $file->getTitle(),
                'description' => $file->getDescription(),

            ], Response::HTTP_OK
        );
    }

    /**
     * @Route("/schools/experiences/files/{id}/remove", name="school_experience_file_remove", options = { "expose" = true })
     * @ParamConverter("company", options={"id" = "company_id"})
     * @ParamConverter("companyResource", options={"id" = "resource_id"})
     * @param Request $request
     * @param ExperienceFile $experienceFile
     * @return JsonResponse
     */
    public function schoolExperienceRemoveFileAction(Request $request, ExperienceFile $experienceFile) {

        $this->denyAccessUnlessGranted('edit', $experienceFile->getExperience()->getSchool());

        $this->entityManager->remove($experienceFile);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,

            ], Response::HTTP_OK
        );
    }

    /**
     * @param $tempUsername
     * @param int $i
     * @return mixed
     */
    private function determineUsername($tempUsername, $i = 1) {

        if($this->userRepository->loadUserByUsername($tempUsername)) {
            return $this->determineUsername(sprintf("%s%s", $tempUsername, $this->generateRandomNumber($i)), ++$i);
        }
        return $tempUsername;
    }

    /**
     * @return mixed
     */
    private function determinePassword() {
        return sprintf("TEST%s", $this->generateRandomNumber(5));
    }

    /**
     * @Route("/schools/{id}/featured/add", name="school_featured_add", options = { "expose" = true })
     * @param Request $request
     * @param School $school
     * @return JsonResponse
     */
    public function schoolAddFeaturedAction(Request $request, School $school) {

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $featuredImage = $request->files->get('file');

        if($featuredImage) {
            $mimeType = $featuredImage->getMimeType();
            $newFilename = $this->uploaderHelper->upload($featuredImage, UploaderHelper::FEATURE_IMAGE);
            $image = new Image();
            $image->setOriginalName($featuredImage->getClientOriginalName() ?? $newFilename);
            $image->setMimeType($mimeType ?? 'application/octet-stream');
            $image->setFileName($newFilename);
            $school->setFeaturedImage($image);
            $this->entityManager->persist($image);

            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::FEATURE_IMAGE) .'/'. $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            $this->entityManager->persist($school);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->cacheManager->getBrowserPath('uploads/'.UploaderHelper::FEATURE_IMAGE.'/'.$newFilename, 'squared_thumbnail_small'),
                    'id' => $image->getId(),
                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,
            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/schools/{id}/thumbnail/add", name="school_thumbnail_add", options = { "expose" = true })
     * @param Request $request
     * @param School $school
     * @return JsonResponse
     */
    public function schoolAddThumbnailAction(Request $request, School $school) {

        $user = $this->getUser();

        /** @var UploadedFile $uploadedFile */
        $thumbnailImage = $request->files->get('file');

        if($thumbnailImage) {
            $mimeType = $thumbnailImage->getMimeType();
            $newFilename = $this->uploaderHelper->upload($thumbnailImage, UploaderHelper::THUMBNAIL_IMAGE);
            $image = new Image();
            $image->setOriginalName($thumbnailImage->getClientOriginalName() ?? $newFilename);
            $image->setMimeType($mimeType ?? 'application/octet-stream');
            $image->setFileName($newFilename);
            $school->setThumbnailImage($image);
            $this->entityManager->persist($image);

            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::THUMBNAIL_IMAGE) .'/'. $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);

            $this->entityManager->persist($school);
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->cacheManager->getBrowserPath('uploads/'.UploaderHelper::THUMBNAIL_IMAGE.'/'.$newFilename, 'squared_thumbnail_small'),
                    'id' => $image->getId(),
                ], Response::HTTP_OK
            );
        }

        return new JsonResponse(
            [
                'success' => false,
            ], Response::HTTP_BAD_REQUEST
        );
    }

}
