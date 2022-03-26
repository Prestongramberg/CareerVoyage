<?php

namespace App\Form\UserImport;

use App\Entity\EducatorUser;
use App\Entity\StudentUser;
use App\Entity\UserImport;
use App\Repository\EducatorUserRepository;
use App\Repository\StudentUserRepository;
use App\Repository\UserRepository;
use App\Validator\Constraints\EducatorExists;
use App\Validator\Constraints\EmailAlreadyExists;
use App\Validator\Constraints\UsernameAlreadyExists;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\Component\Pager\PaginatorInterface;
use phpDocumentor\Reflection\Types\Iterable_;
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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var SessionInterface
     */
    private $session;

    private $educatorEmailCache = [];
    private $usernameCache = [];
    private $emailCache = [];
    private $totalUsers = 0;
    private $totalUsersWithErrors = 0;
    private $userItems = [];

    /**
     * @param  \Symfony\Component\Validator\Validator\ValidatorInterface   $validator
     * @param  \App\Repository\EducatorUserRepository                      $educatorUserRepository
     * @param  \App\Repository\UserRepository                              $userRepository
     * @param  \Knp\Component\Pager\PaginatorInterface                     $paginator
     * @param  \Symfony\Component\HttpFoundation\RequestStack              $requestStack
     * @param  \Symfony\Component\HttpFoundation\Session\SessionInterface  $session
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(
        ValidatorInterface $validator,
        EducatorUserRepository $educatorUserRepository,
        UserRepository $userRepository,
        PaginatorInterface $paginator,
        RequestStack $requestStack,
        SessionInterface $session
    )
    {
        $this->validator                = $validator;
        $this->educatorUserRepository   = $educatorUserRepository;
        $this->userRepository           = $userRepository;
        $this->paginator                = $paginator;
        $this->requestStack             = $requestStack;
        $this->session                  = $session;

        $this->educatorEmailCache = $this->educatorUserRepository->getAllEmailAddresses();
        $this->usernameCache = $this->userRepository->getAllUsernames();
        $this->emailCache = $this->userRepository->getAllEmailAddresses();
        $this->userItems = $this->session->get('userItems', []);
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


            $request = $this->requestStack->getCurrentRequest();
            $page = $request->query->getInt('page', 1);

            $userItems = new ArrayCollection($this->userItems);
            $pagination = $this->paginator->paginate(
                $userItems,
                $page,
                100
            );

            $userItems = $pagination->getItems();

            //$userItems = $userItems->slice(0, 100);
            $forms['userItems']->setData($userItems);
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
        /** @var UserImport $userImport */
        $userImport = $builder->getData();

   /*     $builder->setDataMapper($this);

        $builder->add('userItems', CollectionType::class, [
            // each entry in the array will be an "email" field
            'entry_type'    => UserFormType::class,
            'entry_options' => [
                'educatorEmailCache' => $this->educatorEmailCache,
                'usernameCache' => $this->usernameCache,
                'emailCache' => $this->emailCache,
                'userImport' => $userImport
            ],
        ]);*/

        /**
         * The idea here is pretty simple. Remove the user rows that pass validation on submit.
         */
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {

            return;


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
                    $educatorUser = new EducatorUser();
                    $educatorUser->fromDataImportArray($userItem);

                    $constraintViolationList = new ConstraintViolationList();

                    $errors1 = $this->validator->validate($educatorUser->getEmail(), new EmailAlreadyExists($this->emailCache, ['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);
                    $errors2 = $this->validator->validate($educatorUser->getFirstName(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);
                    $errors3 = $this->validator->validate($educatorUser->getLastName(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);
                    $errors4 = $this->validator->validate($educatorUser->getEmail(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);
                    $errors5 = $this->validator->validate($educatorUser->getTempPassword(), new NotBlank(['groups' => ['USER_IMPORT_USER_INFO']]), ['USER_IMPORT_USER_INFO']);

                    $constraintViolationList->addAll($errors1);
                    $constraintViolationList->addAll($errors2);
                    $constraintViolationList->addAll($errors3);
                    $constraintViolationList->addAll($errors4);
                    $constraintViolationList->addAll($errors5);

                    if (count($constraintViolationList) === 0) {
                        $userItemKeysToRemove[] = $userItemKey;
                    } else {
                        $totalUsersWithImportErrors++;
                    }
                }

                $userItemKey++;
            }

            if($totalUsersWithImportErrors > 0) {

                if ($userImport->getType() === 'Student') {
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

                if ($userImport->getType() === 'Educator') {
                    foreach($userItemKeysToRemove as $userItemKey) {

                        $f = $userItemFormElements->get($userItemKey);
                        $f->remove('firstName');
                        $f->remove('lastName');
                        $f->remove('email');
                        $f->remove('tempPassword');
                        $f->add('firstName', HiddenType::class);
                        $f->add('lastName', HiddenType::class);
                        $f->add('email', HiddenType::class);
                        $f->add('tempPassword', HiddenType::class);
                    }
                }

            } else {

                // todo no errors have occurred.
                // todo let's review the next selection of users right?

                $request = $this->requestStack->getCurrentRequest();
                $page = $request->query->getInt('page', 1);

                // todo I don't know if this logic is correct??
                $start = ($page - 1) * 100;
                $userItems = array_splice($this->userItems, $start, 100);

                $this->session->set('userItems', $userItems);

         /*       $request = $this->requestStack->getCurrentRequest();
                $page = $request->query->getInt('page', 1);
                $page++;

                $userItems = new ArrayCollection($this->userItems);
                $pagination = $this->paginator->paginate(
                    $userItems,
                    $page,
                    100
                );

                */

                $form->addError(new FormError("more users need to be imported!"));
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
