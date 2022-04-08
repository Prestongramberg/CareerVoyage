<?php

namespace App\Controller\Api;

use App\Entity\EducatorUser;
use App\Entity\ProfessionalUser;
use App\Entity\SchoolAdministrator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Entity\UserImport;
use App\Entity\UserImportUser;
use App\Form\SearchFilterType;
use App\Repository\UserImportRepository;
use App\Repository\UserImportUserRepository;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use App\Validator\Constraints\EducatorExists;
use App\Validator\Constraints\EmailAlreadyExists;
use App\Validator\Constraints\UsernameAlreadyExists;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Request as RequestEntity;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Class UserImportController
 *
 * @package App\Controller
 * @Route("/api/user-import")
 */
class UserImportController extends AbstractController
{

    use FileHelper;
    use ServiceHelper;

    /**
     * @Route("/{uuid}", name="user_import_get", methods={"GET", "POST"}, options = { "expose" = true })
     * @param  Request                                                     $request
     * @param  \App\Entity\UserImport                                      $userImport
     * @param  \Symfony\Component\HttpFoundation\Session\SessionInterface  $session
     * @param  \App\Repository\UserImportRepository                        $userImportRepository
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getUserImportAction(
        Request $request,
        UserImport $userImport,
        SessionInterface $session,
        UserImportRepository $userImportRepository
    ) {
        // todo you could add more initial data to this endpoint such as educator cache and more....
        // todo add loader to the final import page when you are pulling this info
        // todo pass up the UserImport ID here as well.
        // todo use some type of import id in the url of the main import route right?
        // todo don't look at the session here anymore.
        // todo will need to clear out all the importUsers everytime you do the file upload as you are essentially starting over.


        $userImportData = $this->serializer->serialize($userImport, 'json', ['groups' => ['USER_IMPORT']]);

        $data = [
            'userImport' => json_decode($userImportData, true)
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/{uuid}/users/save", name="user_import_save_user", methods={"GET", "POST"}, options = { "expose" = true })
     * @param  Request                                                     $request
     * @param  \App\Entity\UserImport                                      $userImport
     * @param  \Symfony\Component\HttpFoundation\Session\SessionInterface  $session
     * @param  \App\Repository\UserImportUserRepository                    $userImportUserRepository
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function saveUserAction(Request $request, UserImport $userImport, SessionInterface $session, UserImportUserRepository $userImportUserRepository)
    {
        // todo pass up the userImport object here right via uuid or id?

        // todo wire up and make sure you set all the roles, etc and persist and everything here and validation

        $educatorEmailCache = $this->educatorUserRepository->getAllEmailAddresses();
        $usernameCache      = $this->userRepository->getAllUsernames();
        $emailCache         = $this->userRepository->getAllEmailAddresses();
        $users              = $request->request->get('users', []);
        $data               = [];

        if (!$userImport) {
            throw new \Exception("User import object not defined on user to import.");
        }

        $school = $userImport->getSchool();

        if (!$school) {
            throw new \Exception("User import object not defined on user to import.");
        }

        foreach ($users as $userData) {
            $errors         = [];
            $userId         = $userData['id'];
            $userImportUser = $userImportUserRepository->find($userId);

            if (!$userImportUser) {
                continue;
            }

            $constraintViolationList = new ConstraintViolationList();

            if ($userImport && $userImport->getType() === 'Student') {
                $studentUser = new StudentUser();
                $studentUser->setActivated(true);
                $studentUser->setupAsStudent();
                $studentUser->addRole(User::ROLE_DASHBOARD_USER);
                $studentUser->setSchool($school);
                $studentUser->setTempPasswordEncrypted($school->getEncodedStudentTempPassword());
                $studentUser->fromDataImportArray($userData);


                // todo!!! Remove hardcoding for testing!
                //$errors1 = $this->validator->validate('josh+admin@pintex.com', new EducatorExists($educatorEmailCache, ['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                $errors1 = $this->validator->validate($studentUser->getEducatorEmail(), new EducatorExists($educatorEmailCache, ['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                if (count($errors1) > 0) {
                    $errorsString            = $errors1->get(0)
                                                       ->getMessage();
                    $errors['educatorEmail'] = $errorsString;
                }

                $errors2 = $this->validator->validate($studentUser->getUsername(), new UsernameAlreadyExists($usernameCache, ['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                if (count($errors2) > 0) {
                    $errorsString       = $errors2->get(0)
                                                  ->getMessage();
                    $errors['username'] = $errorsString;
                }

                $errors3 = $this->validator->validate($studentUser->getFirstName(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                if (count($errors3) > 0) {
                    $errorsString        = $errors3->get(0)
                                                   ->getMessage();
                    $errors['firstName'] = $errorsString;
                }

                $errors4 = $this->validator->validate($studentUser->getLastName(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                if (count($errors4) > 0) {
                    $errorsString       = $errors4->get(0)
                                                  ->getMessage();
                    $errors['lastName'] = $errorsString;
                }

                $errors5 = $this->validator->validate($studentUser->getUsername(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                if (count($errors5) > 0) {
                    $errorsString       = $errors5->get(0)
                                                  ->getMessage();
                    $errors['username'] = $errorsString;
                }

                $errors6 = $this->validator->validate($studentUser->getEducatorEmail(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                if (count($errors6) > 0) {
                    $errorsString            = $errors6->get(0)
                                                       ->getMessage();
                    $errors['educatorEmail'] = $errorsString;
                }

                $errors7 = $this->validator->validate($studentUser->getGraduatingYear(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                if (count($errors7) > 0) {
                    $errorsString             = $errors7->get(0)
                                                        ->getMessage();
                    $errors['graduatingYear'] = $errorsString;
                }

                $errors8 = $this->validator->validate($studentUser->getTempPassword(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                if (count($errors8) > 0) {
                    $errorsString           = $errors8->get(0)
                                                      ->getMessage();
                    $errors['tempPassword'] = $errorsString;
                }

                $constraintViolationList->addAll($errors1);
                $constraintViolationList->addAll($errors2);
                $constraintViolationList->addAll($errors3);
                $constraintViolationList->addAll($errors4);
                $constraintViolationList->addAll($errors5);
                $constraintViolationList->addAll($errors6);
                $constraintViolationList->addAll($errors7);
                $constraintViolationList->addAll($errors8);

                if (count($constraintViolationList) === 0) {
                    $educatorUser = $this->educatorUserRepository->findOneBy([
                        'email' => $studentUser->getEducatorEmail(),
                    ]);

                    if ($educatorUser) {
                        $studentUser->addEducatorUser($educatorUser);
                    }

                    $userImportUser->setIsImported(true);
                    $userImportUser->setUser($studentUser);
                    $this->entityManager->persist($studentUser);
                }
            }

            if ($userImport && $userImport->getType() === 'Educator') {
                $educatorUser = new EducatorUser();
                $educatorUser->setActivated(true);
                $educatorUser->setupAsEducator();
                $educatorUser->addRole(User::ROLE_DASHBOARD_USER);
                $educatorUser->setSchool($school);
                $educatorUser->setTempPasswordEncrypted($school->getEncodedEducatorTempPassword());
                $educatorUser->fromDataImportArray($userData);

                $errors1 = $this->validator->validate($educatorUser->getEmail(), new EmailAlreadyExists($emailCache, ['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                if (count($errors1) > 0) {
                    $errorsString    = $errors1->get(0)
                                               ->getMessage();
                    $errors['email'] = $errorsString;
                }

                $errors2 = $this->validator->validate($educatorUser->getFirstName(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                if (count($errors2) > 0) {
                    $errorsString        = $errors2->get(0)
                                                   ->getMessage();
                    $errors['firstName'] = $errorsString;
                }

                $errors3 = $this->validator->validate($educatorUser->getLastName(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                if (count($errors3) > 0) {
                    $errorsString       = $errors3->get(0)
                                                  ->getMessage();
                    $errors['lastName'] = $errorsString;
                }

                $errors4 = $this->validator->validate($educatorUser->getEmail(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                if (count($errors4) > 0) {
                    $errorsString    = $errors4->get(0)
                                               ->getMessage();
                    $errors['email'] = $errorsString;
                }

                $errors5 = $this->validator->validate($educatorUser->getTempPassword(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                if (count($errors5) > 0) {
                    $errorsString           = $errors5->get(0)
                                                      ->getMessage();
                    $errors['tempPassword'] = $errorsString;
                }

                $constraintViolationList->addAll($errors1);
                $constraintViolationList->addAll($errors2);
                $constraintViolationList->addAll($errors3);
                $constraintViolationList->addAll($errors4);
                $constraintViolationList->addAll($errors5);

                if (count($constraintViolationList) === 0) {
                    $userImportUser->setIsImported(true);
                    $userImportUser->setUser($educatorUser);
                    $this->entityManager->persist($educatorUser);
                }
            }

            $userImportUser->fromArray($userData);
            $userImportUser->setErrors($errors);
            $userImportUserData = $this->serializer->serialize($userImportUser, 'json', ['groups' => ['USER_IMPORT']]);

            $data[] = json_decode($userImportUserData, true);

        }


        // 1. First save the new data to the user import user object


        /*      if (count($errors)) {
                  return new JsonResponse(
                      [
                          'errors' => $errors,
                          'userImportUser' => json_decode($userImportUserData, true)
                      ], Response::HTTP_BAD_REQUEST
                  );
              }*/

        $this->entityManager->flush();

        return new JsonResponse(
            [
                'errors'    => $errors,
                'data'      => $data
            ], Response::HTTP_OK
        );
    }

}
