<?php

namespace App\Form\UserImport;

use App\Entity\EducatorUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Entity\UserImport;
use App\Service\PhpSpreadsheetHelper;
use App\Util\RandomStringGenerator;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class FileInfoFormType extends AbstractType
{
    use RandomStringGenerator;

    /**
     * @var PhpSpreadsheetHelper;
     */
    private $phpSpreadsheetHelper;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @param  \App\Service\PhpSpreadsheetHelper                                      $phpSpreadsheetHelper
     * @param  \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface  $passwordEncoder
     */
    public function __construct(PhpSpreadsheetHelper $phpSpreadsheetHelper, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->phpSpreadsheetHelper = $phpSpreadsheetHelper;
        $this->passwordEncoder      = $passwordEncoder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', FileType::class, [
            'label'       => '(CSV or Excel file)',
            'mapped'      => false,
            'required'    => true,
            'constraints' => [
                new NotNull(['message' => 'Please select a valid file.', 'groups' => ['USER_IMPORT_FILE_INFO']]),
            ],
        ]);

     /*   $builder->add('users', ChoiceType::class, [
            'choices'  => [],
            'expanded' => false,
            'multiple' => true
        ]);*/


        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            /** @var UserImport $userImport */
            $userImport = $form->getData();
            $firstNameMapping = $userImport->getFirstNameMapping();
            $lastNameMapping = $userImport->getLastNameMapping();
            $emailMapping = $userImport->getEmailMapping();
            $usernameMapping = $userImport->getUsernameMapping();

            /** @var UploadedFile|null $file */
            $file = $data['file'] ?? null;

            if(!$file) {
                return;
            }

            if(!isset($data['users'])) {
                $data['users'] = [];
            }

            $columns = $this->phpSpreadsheetHelper->getColumns($file);
            $firstNameKey = array_search($firstNameMapping, $columns, true);
            $lastNameKey = array_search($lastNameMapping, $columns, true);
            $usernameKey = array_search($usernameMapping, $columns, true);
            $emailKey = array_search($emailMapping, $columns, true);

            try {
                $reader = $this->phpSpreadsheetHelper->getReader($file);
            } catch (\Exception $exception) {
                $name = "test";
            }

            /**
             * Generating passwords inside a loop is extremely expensive and uses up too much cpu/ram. Create initial temp
             * shared password for all users being imported
             */
            $tempPassword    = sprintf('student.%s', $this->generateRandomString(5));
            $encodedPassword = $this->passwordEncoder->encodePassword(new User(), $tempPassword);

            try {
                $choices = [];

                /** @var \Box\Spout\Reader\SheetInterface $sheet */
                foreach ($reader->getSheetIterator() as $sheet) {
                    /** @var \Box\Spout\Common\Entity\Row $row */
                    $columns   = [];
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

                            // todo look at type on form object to determine whether or not to create student objects / or educator objects
                            $studentObj = new StudentUser();

                            if($firstNameKey !== false && array_key_exists($firstNameKey, $values)) {
                                $studentObj->setFirstName(trim($values[$firstNameKey]));
                            }

                            if($lastNameKey !== false && array_key_exists($lastNameKey, $values)) {
                                $studentObj->setLastName(trim($values[$lastNameKey]));
                            }

                            if($studentObj->getFirstName() && $studentObj->getLastName()) {
                                $username = preg_replace('/\s+/', '', sprintf("%s.%s", trim($studentObj->getFirstName()).'.'.trim($studentObj->getLastName()), $this->generateRandomString(5)));
                            } elseif ($studentObj->getLastName()) {
                                $username = preg_replace('/\s+/', '', sprintf("%s.%s", trim($studentObj->getLastName()), $this->generateRandomString(5)));
                            } else {
                                $username = preg_replace('/\s+/', '', sprintf("%s", $this->generateRandomString(10)));
                            }

                            $username = strtolower($username);
                            $studentObj->setUsername($username);

                            $choices[] = $studentObj;


                            /*$student = array_combine($columns, $values);

                            if (!empty($student['First Name']) && !empty($student['Last Name'])) {
                                $username = preg_replace('/\s+/', '', sprintf("%s.%s", strtolower(trim($student['First Name']).'.'.trim($student['Last Name'])), $this->generateRandomString(1)));

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

                                $studentObjs[] = $studentObj;
                            }*/


                        }
                    }
                }

            } catch (\Exception $exception) {
                $name = "test";
            }

            if($form->has('users')) {
                $form->remove('users');
            }

            $form->add('users', ChoiceType::class, [
                'choices'  => $choices,
                'expanded' => false,
                'multiple' => true
            ]);

            $data['users'] = array_keys($choices);

            $event->setData($data);
        });
    }

    public function getBlockPrefix()
    {
        return 'fileInfo';
    }

}
