<?php

namespace App\Form\UserImport;

use App\Entity\EducatorUser;
use App\Entity\SchoolAdministrator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Entity\UserImport;
use App\Repository\EducatorUserRepository;
use App\Service\PhpSpreadsheetHelper;
use App\Util\RandomStringGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class ColumnMappingInfoFormType extends AbstractType
{

    use RandomStringGenerator;

    public const ERROR_COLUMN_MAPPING_MESSAGE = 'Some of the column title names from your uploaded spreadsheet/csv file do not match the Column Mapping you entered in Step 2 which can result in missing data. Please go back to Step 2, fix your column mapping names - Or manually enter any missing data below.';

    /**
     * @var PhpSpreadsheetHelper;
     */
    private $phpSpreadsheetHelper;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var EducatorUserRepository
     */
    private $educatorUserRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var FlashBagInterface $flash
     */
    private $flash;

    /**
     * Cached educator user map for increased performance
     *
     * @var array
     */
    private $educatorCache = [];

    /**
     * @var User
     */
    private $loggedInUser;

    /**
     * @param  \App\Service\PhpSpreadsheetHelper                                                    $phpSpreadsheetHelper
     * @param  \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface                $passwordEncoder
     * @param  \App\Repository\EducatorUserRepository                                               $educatorUserRepository
     * @param  \Doctrine\ORM\EntityManagerInterface                                                 $entityManager
     * @param  \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface  $tokenStorage
     * @param  \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface                    $flash
     */
    public function __construct(
        PhpSpreadsheetHelper $phpSpreadsheetHelper,
        UserPasswordEncoderInterface $passwordEncoder,
        EducatorUserRepository $educatorUserRepository,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        FlashBagInterface $flash
    ) {
        $this->phpSpreadsheetHelper   = $phpSpreadsheetHelper;
        $this->passwordEncoder        = $passwordEncoder;
        $this->educatorUserRepository = $educatorUserRepository;
        $this->entityManager          = $entityManager;
        $this->flash                  = $flash;

        $this->loggedInUser = $tokenStorage->getToken()
                                           ->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var UserImport $userImport */
        $userImport = $builder->getData();

        $builder->add('firstNameMapping', TextType::class, [
            'required' => true,
            'label'    => 'First Name',
            'constraints' => [
                new NotNull(['message' => 'Please enter a value.', 'groups' => ['USER_IMPORT_COLUMN_MAPPING_INFO']]),
            ],
        ]);

        $builder->add('lastNameMapping', TextType::class, [
            'required' => true,
            'label'    => 'First Name',
            'constraints' => [
                new NotNull(['message' => 'Please enter a value.', 'groups' => ['USER_IMPORT_COLUMN_MAPPING_INFO']]),
            ],
        ]);

        if($userImport->getType() === 'Student') {

            $builder->add('educatorEmailMapping', TextType::class, [
                'required' => true,
                'label'    => 'Educator Email',
                'constraints' => [
                    new NotNull(['message' => 'Please enter a value.', 'groups' => ['USER_IMPORT_COLUMN_MAPPING_INFO']]),
                ],
            ]);

            $builder->add('graduatingYearMapping', TextType::class, [
                'required' => true,
                'label'    => 'Graduating Year',
                'constraints' => [
                    new NotNull(['message' => 'Please enter a value.', 'groups' => ['USER_IMPORT_COLUMN_MAPPING_INFO']]),
                ],
            ]);
        }

        if($userImport->getType() === 'Educator') {
            $builder->add('emailMapping', TextType::class, [
                'required' => true,
                'label'    => 'Email',
                'constraints' => [
                    new NotNull(['message' => 'Please enter a value.', 'groups' => ['USER_IMPORT_COLUMN_MAPPING_INFO']]),
                ],
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            /** @var UserImport $userImport */
            $userImport = $form->getData();

            if($userImport->getSkipColumnMappingStep()) {
                return;
            }

            /** @var UploadedFile $file */
            $file = $userImport->getFile();
            /** @var \App\Entity\School $school */
            $school = $userImport->getSchool();

            if (!$studentTempPassword = $school->getStudentTempPasssword()) {
                $generator    = new \Nubs\RandomNameGenerator\Alliteration();
                $tempPassword = $generator->getName();
                $tempPassword = str_replace(" ", "", strtolower($tempPassword));
                $school->setStudentTempPasssword($tempPassword);
                $this->entityManager->persist($school);
                $this->entityManager->flush();
            }

            if (!$educatorTempPassword = $school->getEducatorTempPassword()) {
                $generator    = new \Nubs\RandomNameGenerator\Alliteration();
                $tempPassword = $generator->getName();
                $tempPassword = str_replace(" ", "", strtolower($tempPassword));
                $school->setEducatorTempPassword($tempPassword);
                $this->entityManager->persist($school);
                $this->entityManager->flush();
            }

            $encodedStudentTempPassword  = $this->passwordEncoder->encodePassword(new StudentUser(), $studentTempPassword);
            $encodedEducatorTempPassword = $this->passwordEncoder->encodePassword(new EducatorUser(), $educatorTempPassword);
            $firstNameMapping            = $data['firstNameMapping'] ?? null;
            $lastNameMapping             = $data['lastNameMapping'] ?? null;
            $educatorEmailMapping        = $data['educatorEmailMapping'] ?? null;
            $graduatingYearMapping       = $data['graduatingYearMapping'] ?? null;
            $emailMapping                = $data['emailMapping'] ?? null;
            $hasErrors = false;

            /** @var UploadedFile|null $file */
            //$file = $data['file'] ?? null;

            if (!$file) {
                return;
            }

            if (!isset($data['users'])) {
                $data['users'] = [];
            }

            $columns           = $this->phpSpreadsheetHelper->getColumns($file);
            $firstNameKey      = array_search($firstNameMapping, $columns, true);
            $lastNameKey       = array_search($lastNameMapping, $columns, true);
            $educatorEmailKey  = array_search($educatorEmailMapping, $columns, true);
            $graduatingYearKey = array_search($graduatingYearMapping, $columns, true);
            $emailKey = array_search($emailMapping, $columns, true);

            try {
                $reader = $this->phpSpreadsheetHelper->getReader($file);
            } catch (\Exception $exception) {
                // do nothing
            }

            try {
                $choices = [];

                /** @var \Box\Spout\Reader\SheetInterface $sheet */
                foreach ($reader->getSheetIterator() as $sheet) {
                    /** @var \Box\Spout\Common\Entity\Row $row */
                    $columns = [];
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

                            $school = $userImport->getSchool();

                            if ($userImport->getType() === 'Student') {
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

                                if(!$hasErrors) {
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

                                if(!$hasErrors) {
                                    $this->flash->clear();
                                }

                                $choices[] = $userObj;

                            }
                        }
                    }
                }
            } catch (\Exception $exception) {
                // do nothing
            }

            if ($form->has('users')) {
                $form->remove('users');
            }

            $form->add('users', ChoiceType::class, [
                'choices'  => $choices,
                'expanded' => false,
                'multiple' => true,
            ]);

            $data['users'] = array_keys($choices);

            $event->setData($data);
        });

    }

    public function getBlockPrefix()
    {
        return 'columnMappingInfo';
    }

}
