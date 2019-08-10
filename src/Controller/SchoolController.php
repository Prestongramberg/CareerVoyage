<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\CompanyVideo;
use App\Entity\EducatorUser;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolAdministratorRequest;
use App\Entity\SchoolPhoto;
use App\Entity\SchoolVideo;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\EditSchoolType;
use App\Form\EducatorImportType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\NewSchoolType;
use App\Form\ProfessionalEditProfileFormType;
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
     * @Security("is_granted('ROLE_REGIONAL_COORDINATOR_USER')")
     * @Route("/schools/new", name="school_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function newAction(Request $request) {

        $user = $this->getUser();
        $school = new School();

        $form = $this->createForm(NewSchoolType::class, $school, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var School $school */
            $school = $form->getData();
            $this->entityManager->persist($school);

            $email = $form->get('schoolAdministratorEmail')->getData();
            $firstName = $form->get('schoolAdministratorFirstName')->getData();
            $lastName = $form->get('schoolAdministratorLastName')->getData();

            $existingUser = $this->userRepository->getByEmailAddress($email);

            if($existingUser) {
                $this->addFlash('error', 'That user already exists in the system.');
                return $this->redirectToRoute('school_new');
            } else {
                $schoolAdministrator = new SchoolAdministrator();
                $schoolAdministrator->setEmail($email);
                $schoolAdministrator->setFirstName($firstName);
                $schoolAdministrator->setLastName($lastName);
                $schoolAdministrator->setEmail($email);
                $schoolAdministrator->initializeNewUser();
                $schoolAdministrator->setPasswordResetToken();
                $this->entityManager->persist($schoolAdministrator);
            }

            $schoolAdministratorRequest = new SchoolAdministratorRequest();
            $schoolAdministratorRequest->setSchool($school);
            $schoolAdministratorRequest->setCreatedBy($this->getUser());
            $schoolAdministratorRequest->setNeedsApprovalBy($schoolAdministrator);
            $this->entityManager->persist($schoolAdministratorRequest);

            $this->entityManager->flush();
            $this->securityMailer->sendAccountActivation($schoolAdministrator);
            $this->requestsMailer->schoolAdministratorRequest($schoolAdministratorRequest);

            $this->addFlash('success', sprintf('School successfully created. Invite sent to %s', $email));
            return $this->redirectToRoute('school_new');
        }

        return $this->render('school/new.html.twig', [
            'user' => $user,
            'form' => $form->createView()
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
     * @Security("is_granted('ROLE_SCHOOL_ADMINISTRATOR_USER')")
     * @Route("/schools/{id}/edit", name="school_edit")
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
            $this->entityManager->persist($school);
            $this->entityManager->flush();


            $this->addFlash('success', sprintf('School successfully updated.'));
            return $this->redirectToRoute('school_edit');
        }

        return $this->render('school/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'school' => $school
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

        $user = $this->getUser();

        $form = $this->createForm(StudentImportType::class, null, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $uploadedFile */
            $file = $form->get('file')->getData();

            if($file) {
                $tempPathName = $file->getRealPath();
                $rowNo = 1;
                $students = [];
                if (($fp = fopen($tempPathName, "r")) !== FALSE) {
                    $keys = [];
                    while (($row = fgetcsv($fp, 1000, ",")) !== FALSE) {
                        if($rowNo === 1) {
                            $keys = $row;
                            $rowNo++;
                            continue;
                        }

                        if(trim(implode('', $row)) == '') {
                            continue;
                        }

                        if(count($row) !== count($keys)) {
                            $rowNo++;
                            continue;
                        }

                        $students[] = array_combine($keys, $row);
                        $rowNo++;
                    }
                    fclose($fp);
                }

                $studentObjs = [];
                foreach($students as $student) {

                    $studentId = $student['Student Id'];
                    $studentObj = $this->studentUserRepository->findOneBy([
                        'studentId' => $studentId
                    ]);

                    // only create the student if it doesn't exist
                    if(!$studentObj) {
                        $studentObj = new StudentUser();
                        $studentObj->setStudentId($student['Student Id']);
                        $studentObj->setSchool($school);
                        $studentObj->setupAsStudent();
                        $studentObj->initializeNewUser();
                        $studentObj->setActivated(true);
                        $studentObj->setUsername($this->determineUsername($studentObj->getTempUsername()));
                        $encodedPassword = $this->passwordEncoder->encodePassword($studentObj, $studentObj->getTempPassword());
                        $studentObj->setPassword($encodedPassword);
                    }

                    // we only allow overriding first name and last name by imports
                    $studentObj->setFirstName($student['First Name']);
                    $studentObj->setLastName($student['Last Name']);

                    // let's manually validate the object before importing
                    $errors = $this->validator->validate($studentObj,
                        null,
                        ['STUDENT_USER']
                    );

                    if (count($errors) > 0) {
                        $errorsString = (string) $errors;
                        continue;
                    }

                    $this->entityManager->persist($studentObj);
                    $studentObjs[] = $studentObj;
                }
                $this->entityManager->flush();
            }

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
            'school' => $school
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

        $user = $this->getUser();

        $form = $this->createForm(EducatorImportType::class, null, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $uploadedFile */
            $file = $form->get('file')->getData();

            if($file) {
                $tempPathName = $file->getRealPath();
                $rowNo = 1;
                $educators = [];
                if (($fp = fopen($tempPathName, "r")) !== FALSE) {
                    $keys = [];
                    while (($row = fgetcsv($fp, 1000, ",")) !== FALSE) {
                        if($rowNo === 1) {
                            $keys = $row;
                            $rowNo++;
                            continue;
                        }

                        if(trim(implode('', $row)) == '') {
                            continue;
                        }

                        if(count($row) !== count($keys)) {
                            $rowNo++;
                            continue;
                        }

                        $educators[] = array_combine($keys, $row);
                        $rowNo++;
                    }
                    fclose($fp);
                }

                $educatorObjs = [];
                foreach($educators as $educator) {

                    $educatorId = $educator['Educator Id'];
                    $educatorObj = $this->educatorUserRepository->findOneBy([
                        'educatorId' => $educatorId
                    ]);

                    // only create the educator if it doesn't exist
                    if(!$educatorObj) {
                        $educatorObj = new EducatorUser();
                        $educatorObj->setEducatorId($educator['Educator Id']);
                        $educatorObj->setSchool($school);
                        $educatorObj->setupAsEducator();
                        $educatorObj->initializeNewUser();
                        $educatorObj->setActivated(true);
                        $educatorObj->setUsername($this->determineUsername($educatorObj->getTempUsername()));
                        $encodedPassword = $this->passwordEncoder->encodePassword($educatorObj, $educatorObj->getTempPassword());
                        $educatorObj->setPassword($encodedPassword);
                    }

                    // we only allow overriding first name and last name by imports
                    $educatorObj->setFirstName($educator['First Name']);
                    $educatorObj->setLastName($educator['Last Name']);

                    // let's manually validate the object before importing
                    $errors = $this->validator->validate($educatorObj,
                        null,
                        ['EDUCATOR_USER']
                    );

                    if (count($errors) > 0) {
                        $errorsString = (string) $errors;
                        continue;
                    }

                    $this->entityManager->persist($educatorObj);
                    $educatorObjs[] = $educatorObj;
                }

                $this->entityManager->flush();
            }

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
            'school' => $school
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
                    'id' => $image->getId()
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
                    'id' => $video->getId()

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
                    'id' => $video->getId()

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

}