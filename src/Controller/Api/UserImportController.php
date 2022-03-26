<?php

namespace App\Controller\Api;

use App\Entity\EducatorUser;
use App\Entity\ProfessionalUser;
use App\Entity\SchoolAdministrator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Form\SearchFilterType;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Request as RequestEntity;

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
     * @Route("/users", name="user_import_get_users", methods={"GET", "POST"}, options = { "expose" = true })
     * @param  Request                                                     $request
     * @param  \Symfony\Component\HttpFoundation\Session\SessionInterface  $session
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function usersAction(Request $request, SessionInterface $session)
    {
        // todo you could add more initial data to this endpoint such as educator cache and more....
        // todo add loader to the final import page when you are pulling this info
        // todo pass up the UserImport ID here as well.
        // todo use some type of import id in the url of the main import route right?
        // todo don't look at the session here anymore.
        // todo will need to clear out all the importUsers everytime you do the file upload as you are essentially starting over.


        $userItems = $session->get('userItems', []);
        $items = $this->serializer->serialize($userItems, 'json', ['groups' => ['USER_IMPORT']]);

        $data = [
            'userItems'         => json_decode($items, true)
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/save-user", name="user_import_save_user", methods={"GET", "POST"}, options = { "expose" = true })
     * @param  Request                                                     $request
     * @param  \Symfony\Component\HttpFoundation\Session\SessionInterface  $session
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveUserAction(Request $request, SessionInterface $session)
    {
        // todo wire up and make sure you set all the roles, etc and persist and everything here and validation



/*        if ($userImport->getType() === 'Student') {
            $userObj = new StudentUser();
            $userObj->setActivated(true);
            $userObj->setupAsStudent();
            $userObj->addRole(User::ROLE_DASHBOARD_USER);
            $userObj->setSchool($school);
            $userObj->setTempPasswordEncrypted($encodedStudentTempPassword);
            $userObj->setTempPassword($studentTempPassword);

            // todo ?????????? Not even sure why we are using the site concept anymore. But just so things don't break....
            if ($this->loggedInUser instanceof SchoolAdministrator && $site = $this->loggedInUser->getSite()) {
                $userObj->setSite($site);
            }

            if ($firstNameKey !== false && array_key_exists($firstNameKey, $values)) {
                $userObj->setFirstName(trim($values[$firstNameKey]));
            } else {
                $hasErrors = true;
                $this->flash->add('importError', self::ERROR_COLUMN_MAPPING_MESSAGE);
            }

            if ($lastNameKey !== false && array_key_exists($lastNameKey, $values)) {
                $userObj->setLastName(trim($values[$lastNameKey]));
            } else {
                $hasErrors = true;
                $this->flash->add('importError', self::ERROR_COLUMN_MAPPING_MESSAGE);
            }

            if ($educatorEmailKey !== false && array_key_exists($educatorEmailKey, $values)) {
                $educatorEmail = trim($values[$educatorEmailKey]);
                $userObj->setEducatorEmail($educatorEmail);

                if (array_key_exists($educatorEmail, $this->educatorCache)) {
                    $educatorUser = $this->educatorCache[$educatorEmail];

                    if ($educatorUser) {
                        $userObj->addEducatorUser($educatorUser);
                    }
                } else {
                    $educatorUser = $this->educatorUserRepository->findOneBy([
                        'email' => $educatorEmail,
                    ]);

                    $this->educatorCache[$educatorEmail] = null;

                    if ($educatorUser) {
                        $this->educatorCache[$educatorEmail] = $educatorUser;
                        $userObj->addEducatorUser($educatorUser);
                    }
                }
            } else {
                $hasErrors = true;
                $this->flash->add('importError', self::ERROR_COLUMN_MAPPING_MESSAGE);
            }

            if ($graduatingYearKey !== false && array_key_exists($graduatingYearKey, $values)) {
                $userObj->setGraduatingYear(trim($values[$graduatingYearKey]));
            } else {
                $hasErrors = true;
                $this->flash->add('importError', self::ERROR_COLUMN_MAPPING_MESSAGE);
            }

            if (!$hasErrors) {
                $this->flash->clear();
            }

            if ($userObj->getFirstName() && $userObj->getLastName()) {
                $username = preg_replace('/\s+/', '', sprintf("%s_%s", trim($userObj->getFirstName()).'_'.trim($userObj->getLastName()), $this->generateRandomNumber(3)));
            } elseif ($userObj->getLastName()) {
                $username = preg_replace('/\s+/', '', sprintf("%s_%s", trim($userObj->getLastName()), $this->generateRandomNumber(3)));
            } else {
                $username = preg_replace('/\s+/', '', sprintf("%s", $this->generateRandomString(10)));
            }

            $username = strtolower($username);
            $userObj->setUsername($username);

            $choices[] = $userObj;
        }

        if ($userImport->getType() === 'Educator') {
            $userObj = new EducatorUser();
            $userObj->setActivated(true);
            $userObj->setupAsEducator();
            $userObj->addRole(User::ROLE_DASHBOARD_USER);
            $userObj->setSchool($school);
            $userObj->setTempPasswordEncrypted($encodedEducatorTempPassword);
            $userObj->setTempPassword($educatorTempPassword);

            // todo ?????????? Not even sure why we are using the site concept anymore. But just so things don't break....
            if ($this->loggedInUser instanceof SchoolAdministrator && $site = $this->loggedInUser->getSite()) {
                $userObj->setSite($site);
            }

            if ($firstNameKey !== false && array_key_exists($firstNameKey, $values)) {
                $userObj->setFirstName(trim($values[$firstNameKey]));
            } else {
                $hasErrors = true;
                $this->flash->add('importError', self::ERROR_COLUMN_MAPPING_MESSAGE);
            }

            if ($lastNameKey !== false && array_key_exists($lastNameKey, $values)) {
                $userObj->setLastName(trim($values[$lastNameKey]));
            } else {
                $hasErrors = true;
                $this->flash->add('importError', self::ERROR_COLUMN_MAPPING_MESSAGE);
            }

            if ($emailKey !== false && array_key_exists($emailKey, $values)) {
                $email = trim($values[$emailKey]);
                $userObj->setEmail($email);
            } else {
                $hasErrors = true;
                $this->flash->add('importError', self::ERROR_COLUMN_MAPPING_MESSAGE);
            }

            if (!$hasErrors) {
                $this->flash->clear();
            }

            $choices[] = $userObj;
        }


        */





        $userItems = $session->get('userItems', []);
        $items = $this->serializer->serialize($userItems, 'json', ['groups' => ['USER_IMPORT']]);

        $data = [
            'userItems'         => json_decode($items, true)
        ];

        return new JsonResponse($data);
    }
}
