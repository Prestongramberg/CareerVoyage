<?php

namespace App\MessageHandler;

use App\Entity\EducatorUser;
use App\Entity\StudentUser;
use App\Message\EducatorImportMessage;
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
class EducatorImportMessageHandler implements MessageHandlerInterface
{
    use ServiceHelper;
    use RandomStringGenerator;

    public function __invoke(EducatorImportMessage $message)
    {

        $fileName = $message->getFileName();
        $schoolId = $message->getSchoolId();
        $siteId = $message->getSiteId();

        if(!$schoolId || !$fileName || !$siteId) {
            die("school id or file name missing");
        }

        $filePath = $this->uploaderHelper->getPublicPath(UploaderHelper::EDUCATOR_IMPORT . '/' . $fileName);

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
        $expectedColumns = ['First Name', 'Last Name', 'Email'];
        if($columns != $expectedColumns) {
            die(sprintf('Column names need to be exactly: %s', implode(",", $expectedColumns)));
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
                die(sprintf('Error importing educators. Email %s already exists in the system and belongs to another educator', $existingUser->getEmail()));
            }
            $educatorObj = new EducatorUser();
            $educatorObj->setFirstName($educator['First Name']);
            $educatorObj->setLastName($educator['Last Name']);
            $educatorObj->setSchool($school);
            $educatorObj->setupAsEducator();
            $educatorObj->setSite($site);
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

        echo  sprintf('Educators successfully imported.');
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