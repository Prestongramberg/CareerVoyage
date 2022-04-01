<?php

namespace App\Form\UserImport;

use App\Entity\EducatorUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\SchoolAdministrator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Entity\UserImport;
use App\Entity\UserImportUser;
use App\Repository\EducatorUserRepository;
use App\Service\PhpSpreadsheetHelper;
use App\Util\RandomStringGenerator;
use App\Validator\Constraints\UserImportFile;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class FileInfoFormType extends AbstractType
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
     * @var SessionInterface
     */
    private $session;

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
        FlashBagInterface $flash,
        SessionInterface $session
    ) {
        $this->phpSpreadsheetHelper   = $phpSpreadsheetHelper;
        $this->passwordEncoder        = $passwordEncoder;
        $this->educatorUserRepository = $educatorUserRepository;
        $this->entityManager          = $entityManager;
        $this->flash                  = $flash;
        $this->session                = $session;

        $this->loggedInUser = $tokenStorage->getToken()
                                           ->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', FileType::class, [
            'label'       => '(CSV or Excel file)',
            'required'    => true,
            'constraints' => [
                new NotNull(['message' => 'Please select a valid file.', 'groups' => ['USER_IMPORT_FILE_INFO']]),
                new UserImportFile(['groups' => ['USER_IMPORT_FILE_INFO']]),
            ],
        ]);

        $builder->add('skipColumnMappingStep', HiddenType::class, [
            'empty_data' => false,
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {

            $form = $event->getForm();
            $data = $event->getData();
            /** @var UserImport $userImport */
            $userImport = $form->getData();
            /** @var \App\Entity\School $school */
            $school = $userImport->getSchool();

            if (!$studentTempPassword = $school->getStudentTempPasssword()) {
                $generator    = new \Nubs\RandomNameGenerator\Alliteration();
                $tempPassword = $generator->getName();
                $tempPassword = str_replace(" ", "", strtolower($tempPassword));
                $school->setStudentTempPasssword($tempPassword);
                $this->entityManager->persist($school);
                $this->entityManager->flush();
                $studentTempPassword = $tempPassword;
            }

            if (!$educatorTempPassword = $school->getEducatorTempPassword()) {
                $generator    = new \Nubs\RandomNameGenerator\Alliteration();
                $tempPassword = $generator->getName();
                $tempPassword = str_replace(" ", "", strtolower($tempPassword));
                $school->setEducatorTempPassword($tempPassword);
                $this->entityManager->persist($school);
                $this->entityManager->flush();
                $educatorTempPassword = $tempPassword;
            }


            $hasErrors = false;

            /** @var UploadedFile|null $file */
            $file = $data['file'] ?? null;

            if (!$file) {
                return;
            }

            try {
                $columns = $this->phpSpreadsheetHelper->getColumns($file);
            } catch (\Exception $exception) {
                return;
            }

            if ($userImport->getType() === 'Student') {
                $firstNameKey      = $this->firstNameKeyLookup($columns);
                $lastNameKey       = $this->lastNameKeyLookup($columns);
                $educatorEmailKey  = $this->educatorEmailKeyLookup($columns);
                $graduatingYearKey = $this->graduatingYearlKeyLookup($columns);

                $skip = ($firstNameKey === false
                    || $lastNameKey === false
                    || $educatorEmailKey === false
                    || $graduatingYearKey === false);

                if ($skip) {
                    $data['skipColumnMappingStep'] = false;
                    $event->setData($data);

                    return;
                }
            }

            if ($userImport->getType() === 'Educator') {
                $firstNameKey = $this->firstNameKeyLookup($columns);
                $lastNameKey  = $this->lastNameKeyLookup($columns);
                $emailKey     = $this->emailKeyLookup($columns);

                $skip = ($firstNameKey === false
                    || $lastNameKey === false
                    || $emailKey === false);

                if ($skip) {
                    $data['skipColumnMappingStep'] = false;
                    $event->setData($data);

                    return;
                }
            }

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
                        $choice = [];

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

                            if ($userImport->getType() === 'Student') {
                                $choice['tempPassword'] = $studentTempPassword;

                                if ($firstNameKey !== false && array_key_exists($firstNameKey, $values)) {
                                    $choice['firstName'] = trim($values[$firstNameKey]);
                                } else {
                                    $hasErrors = true;
                                    $this->flash->add('importError', self::ERROR_COLUMN_MAPPING_MESSAGE);
                                }

                                if ($lastNameKey !== false && array_key_exists($lastNameKey, $values)) {
                                    $choice['lastName'] = trim($values[$lastNameKey]);
                                } else {
                                    $hasErrors = true;
                                    $this->flash->add('importError', self::ERROR_COLUMN_MAPPING_MESSAGE);
                                }

                                if ($educatorEmailKey !== false && array_key_exists($educatorEmailKey, $values)) {
                                    $choice['educatorEmail'] = trim($values[$educatorEmailKey]);
                                } else {
                                    $hasErrors = true;
                                    $this->flash->add('importError', self::ERROR_COLUMN_MAPPING_MESSAGE);
                                }

                                if ($graduatingYearKey !== false && array_key_exists($graduatingYearKey, $values)) {
                                    $choice['graduatingYear'] = trim($values[$graduatingYearKey]);
                                } else {
                                    $hasErrors = true;
                                    $this->flash->add('importError', self::ERROR_COLUMN_MAPPING_MESSAGE);
                                }

                                if (!$hasErrors) {
                                    $this->flash->clear();
                                }

                                if (!empty($choice['firstName']) && !empty($choice['lastName'])) {
                                    $username = preg_replace('/\s+/', '', sprintf("%s_%s", $choice['firstName'] .'_'. $choice['lastName'], $this->generateRandomNumber(3)));
                                } elseif (!empty($choice['lastName'])) {
                                    $username = preg_replace('/\s+/', '', sprintf("%s_%s", $choice['lastName'], $this->generateRandomNumber(3)));
                                } else {
                                    $username = preg_replace('/\s+/', '', sprintf("%s", $this->generateRandomString(10)));
                                }

                                $choice['username'] = strtolower($username);

                                $choices[] = $choice;
                            }

                            if ($userImport->getType() === 'Educator') {

                                $choice['tempPassword'] = $educatorTempPassword;

                                if ($firstNameKey !== false && array_key_exists($firstNameKey, $values)) {
                                    $choice['firstName'] = trim($values[$firstNameKey]);
                                } else {
                                    $hasErrors = true;
                                    $this->flash->add('importError', self::ERROR_COLUMN_MAPPING_MESSAGE);
                                }

                                if ($lastNameKey !== false && array_key_exists($lastNameKey, $values)) {
                                    $choice['lastName'] = trim($values[$lastNameKey]);
                                } else {
                                    $hasErrors = true;
                                    $this->flash->add('importError', self::ERROR_COLUMN_MAPPING_MESSAGE);
                                }

                                if ($emailKey !== false && array_key_exists($emailKey, $values)) {
                                    $choice['email'] = trim($values[$emailKey]);
                                } else {
                                    $hasErrors = true;
                                    $this->flash->add('importError', self::ERROR_COLUMN_MAPPING_MESSAGE);
                                }

                                if (!$hasErrors) {
                                    $this->flash->clear();
                                }

                                $choices[] = $choice;
                            }
                        }
                    }
                }
            } catch (\Exception $exception) {
                // do nothing
            }

            if ($form->has('userImportUsers')) {
                $form->remove('userImportUsers');
            }

            $form->add('userImportUsers', CollectionType::class, [
                'entry_type'    => UserFormType::class,
                'entry_options' => [
                    'userImport' => $userImport,
                ],
                'allow_add' => true,
            ]);

            $data['userImportUsers'] = $choices;
            $data['skipColumnMappingStep'] = true;

            $event->setData($data);
        });
    }

    private function firstNameKeyLookup(array $columns)
    {
        $possibleColumnNames = [
            'First Name',
            'first name',
            'First_Name',
            'first_name',
            'First name',
            'first Name',
            'firstName',
            'FirstName',
            'firstname',
            'first',
        ];

        foreach ($possibleColumnNames as $possibleColumnName) {
            $key = array_search($possibleColumnName, $columns, true);

            if ($key !== false) {
                return $key;
            }
        }

        return false;
    }

    private function lastNameKeyLookup(array $columns)
    {
        $possibleColumnNames = [
            'Last Name',
            'last name',
            'Last_Name',
            'last_name',
            'Last name',
            'last Name',
            'lastName',
            'LastName',
            'lastname',
            'last',
        ];

        foreach ($possibleColumnNames as $possibleColumnName) {
            $key = array_search($possibleColumnName, $columns, true);

            if ($key !== false) {
                return $key;
            }
        }

        return false;
    }

    private function educatorEmailKeyLookup(array $columns)
    {
        $possibleColumnNames = [
            'Educator Email',
            'Educator',
            'Supervisor Email',
            'Supervisor',
            'Email',
            'EducatorEmail',
            'educator',
            'email',
            'educator email',
            'Educator email',
            'EducatorEmail',
            'educatoremail',
            'Supervisor email',
            'supervisoremail',
        ];

        foreach ($possibleColumnNames as $possibleColumnName) {
            $key = array_search($possibleColumnName, $columns, true);

            if ($key !== false) {
                return $key;
            }
        }

        return false;
    }

    private function graduatingYearlKeyLookup(array $columns)
    {
        $possibleColumnNames = [
            'Graduating Year',
            'Graduation Year',
            'Graduation',
            'Graduating',
            'Graduating year',
            'Graduation year',
            'graduation year',
            'graduation year',
            'graduating',
            'graduation',
            'grad year',
            'Grad Year',
            'Grad year',
        ];

        foreach ($possibleColumnNames as $possibleColumnName) {
            $key = array_search($possibleColumnName, $columns, true);

            if ($key !== false) {
                return $key;
            }
        }

        return false;
    }

    private function emailKeyLookup(array $columns)
    {
        $possibleColumnNames = [
            'Email',
            'email',
            'Email Address',
            'Email address',
            'emailaddress',
            'email address',
        ];

        foreach ($possibleColumnNames as $possibleColumnName) {
            $key = array_search($possibleColumnName, $columns, true);

            if ($key !== false) {
                return $key;
            }
        }

        return false;
    }


    public function getBlockPrefix()
    {
        return 'fileInfo';
    }

}
