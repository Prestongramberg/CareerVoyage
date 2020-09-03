<?php

namespace App\MessageHandler;

use App\Entity\StudentUser;
use App\Message\NewEventNotificationMessage;
use App\Message\RecapMessage;
use App\Message\StudentImportMessage;
use App\Repository\UserRepository;
use App\Service\UploaderHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @see https://symfony.com/doc/4.2/messenger.html
 * Class NewEventNotificationHandler
 * @package App\MessageHandler
 */
class StudentImportMessageHandler implements MessageHandlerInterface
{
    use ServiceHelper;
    use RandomStringGenerator;

    public function __invoke(StudentImportMessage $message)
    {

        $fileName = $message->getFileName();
        $schoolId = $message->getSchoolId();
        $siteId = $message->getSiteId();

        if(!$schoolId || !$fileName || !$siteId) {
            die("school id or file name missing");
        }

        $filePath = $this->uploaderHelper->getPublicPath(UploaderHelper::STUDENT_IMPORT . '/' . $fileName);

        $school = $this->schoolRepository->find($schoolId);
        $site = $this->siteRepository->find($siteId);

        if(!$school) {
            die("school missing");
        }

        if(!$site) {
            die("site missing");
        }

        $file = new UploadedFile($filePath, $fileName);

        $columns = $this->phpSpreadsheetHelper->getColumnNames($file);
        // capitalize each word in each item in array so we can assure a proper comparision
        $columns = array_map('ucwords', $columns);
        $expectedColumns = ['First Name', 'Last Name', 'Graduating Year',  'Educator Number'];
        if($columns != $expectedColumns) {
            die(sprintf('Column names need to be exactly: %s', implode(",", $expectedColumns)));
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
                    die(sprintf('Error importing students. Educator Number %s does not belong to an educator for school %s. Check educator Number list below', $student['Educator Number'], $school->getName()));
                }
            }

            $studentObj->setSchool($school);
            $studentObj->setSite($site);
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

        echo 'completed...';
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
}