<?php

namespace App\Form\UserImport;

use App\Entity\StudentUser;
use App\Entity\UserImport;
use App\Repository\EducatorUserRepository;
use App\Repository\StudentUserRepository;
use App\Repository\UserRepository;
use App\Validator\Constraints\EducatorExists;
use App\Validator\Constraints\UsernameAlreadyExists;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserInfoFormType extends AbstractType implements DataMapperInterface
{

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EducatorUserRepository
     */
    private $educatorUserRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    private $educatorEmailCache = [];
    private $usernameCache = [];
    private $totalUsers = 0;
    private $totalUsersWithErrors = 0;

    /**
     * @param  \Symfony\Component\Validator\Validator\ValidatorInterface  $validator
     * @param  \App\Repository\EducatorUserRepository                     $educatorUserRepository
     * @param  \App\Repository\UserRepository                             $userRepository
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(ValidatorInterface $validator, EducatorUserRepository $educatorUserRepository, UserRepository $userRepository)
    {
        $this->validator              = $validator;
        $this->educatorUserRepository = $educatorUserRepository;
        $this->userRepository = $userRepository;

        $this->educatorEmailCache = $this->educatorUserRepository->getAllEmailAddresses();
        $this->usernameCache = $this->userRepository->getAllUsernames();
    }


    public function mapDataToForms($viewData, $forms): void
    {
        /** @var UserImport $viewData */

        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof UserImport) {
            throw new UnexpectedTypeException($viewData, UserImport::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        if (isset($forms['userItems'])) {
            $forms['userItems']->setData(new ArrayCollection($viewData->getUsers()));
        }
    }

    public function mapFormsToData($forms, &$viewData): void
    {
        /** @var UserImport $viewData */

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        if (isset($forms['userItems'])) {
            $userItems = $forms['userItems']->getData();
            $viewData->setUserItems($userItems);
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setDataMapper($this);

        $builder->add('userItems', CollectionType::class, [
            // each entry in the array will be an "email" field
            'entry_type'    => UserFormType::class,
            'entry_options' => [
                'educatorEmailCache' => $this->educatorEmailCache,
                'usernameCache' => $this->usernameCache
            ],
        ]);

        /**
         * The idea here is pretty simple. Remove the user rows that pass validation on submit.
         */
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form      = $event->getForm();
            $data      = $event->getData();
            $userItems = $data['userItems'] ?? [];
            /** @var UserImport $userImport */
            $userImport = $form->getData();

            $userItemFormElements = $form->get('userItems');

            $userItemKey = 0;
            $totalUsersWithImportErrors = 0;
            $userItemKeysToRemove = [];
            foreach ($userItems as $userItem) {

                if ($userImport->getType() === 'Student') {
                    $studentUser = new StudentUser();
                    $studentUser->fromDataImportArray($userItem);

                    $constraintViolationList = new ConstraintViolationList();
                    $errors1 = $this->validator->validate($studentUser->getEducatorEmail(), new EducatorExists($this->educatorEmailCache, ['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);
                    $errors2 = $this->validator->validate($studentUser->getUsername(), new UsernameAlreadyExists($this->usernameCache, ['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);
                    $errors3 = $this->validator->validate($studentUser->getFirstName(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);
                    $errors4 = $this->validator->validate($studentUser->getLastName(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);
                    $errors5 = $this->validator->validate($studentUser->getUsername(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);
                    $errors6 = $this->validator->validate($studentUser->getEducatorEmail(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);
                    $errors7 = $this->validator->validate($studentUser->getGraduatingYear(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);
                    $errors8 = $this->validator->validate($studentUser->getTempPassword(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                    $constraintViolationList->addAll($errors1);
                    $constraintViolationList->addAll($errors2);
                    $constraintViolationList->addAll($errors3);
                    $constraintViolationList->addAll($errors4);
                    $constraintViolationList->addAll($errors5);
                    $constraintViolationList->addAll($errors6);
                    $constraintViolationList->addAll($errors7);
                    $constraintViolationList->addAll($errors8);

                    if (count($constraintViolationList) === 0) {
                        $userItemKeysToRemove[] = $userItemKey;
                    } else {
                        $totalUsersWithImportErrors++;
                    }
                }


                if ($userImport->getType() === 'Educator') {
                    // todo.........
                }

                $userItemKey++;
            }

            if($totalUsersWithImportErrors > 0) {
                foreach($userItemKeysToRemove as $userItemKey) {

                    $f = $userItemFormElements->get($userItemKey);
                    $f->remove('firstName');
                    $f->remove('lastName');
                    $f->remove('graduatingYear');
                    $f->remove('educatorEmail');
                    $f->remove('username');
                    $f->remove('tempPassword');
                    $f->add('firstName', HiddenType::class);
                    $f->add('lastName', HiddenType::class);
                    $f->add('graduatingYear', HiddenType::class);
                    $f->add('educatorEmail', HiddenType::class);
                    $f->add('username', HiddenType::class);
                    $f->add('tempPassword', HiddenType::class);

                }
            }

            $this->totalUsersWithErrors = $totalUsersWithImportErrors;
            $this->totalUsers = count($data['userItems']);


            $event->setData($data);
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['notice'] = null;

        if($this->totalUsersWithErrors > 0) {
            $view->vars['notice'] = sprintf("%s out of the %s users have errors and need to be addressed before importing all the users.", $this->totalUsersWithErrors, $this->totalUsers);
        }

    }


    public function getBlockPrefix()
    {
        return 'userInfo';
    }

}
