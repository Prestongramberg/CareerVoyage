<?php

namespace App\Form\UserImport;

use App\Entity\User;
use App\Entity\UserImport;
use App\Validator\Constraints\EducatorExists;
use App\Validator\Constraints\EmailAlreadyExists;
use App\Validator\Constraints\UsernameAlreadyExists;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class UserFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var UserImport $userImport */
        $userImport = $options['userImport'];

        $educatorEmailCache = $options['educatorEmailCache'];
        $usernameCache = $options['usernameCache'];
        $emailCache = $options['emailCache'];

        $builder->add('firstName', TextType::class, [
            'constraints'    => [
                new NotNull(['message' => 'Please enter a value.', 'groups' => ['USER_IMPORT_USER_INFO']]),
            ],
        ]);
        $builder->add('lastName', TextType::class, [
            'constraints'    => [
                new NotNull(['message' => 'Please enter a value.', 'groups' => ['USER_IMPORT_USER_INFO']]),
            ],
        ]);

        if($userImport->getType() === 'Student') {
            $builder->add('graduatingYear', TextType::class, [
                'constraints'    => [
                    new NotNull(['message' => 'Please enter a value.', 'groups' => ['USER_IMPORT_USER_INFO']]),
                ],
            ]);

            $builder->add('educatorEmail', TextType::class, [
                'constraints'    => [
                    new NotNull(['message' => 'Please enter a value.', 'groups' => ['USER_IMPORT_USER_INFO']]),
                    new EducatorExists($educatorEmailCache, ['groups' => ['USER_IMPORT_USER_INFO']])
                ],
            ]);

            $builder->add('username', TextType::class, [
                'constraints'    => [
                    new NotNull(['message' => 'Please enter a value.', 'groups' => ['USER_IMPORT_USER_INFO']]),
                    new UsernameAlreadyExists($usernameCache, ['groups' => ['USER_IMPORT_USER_INFO']]),
                ],
            ]);
        }

        if($userImport->getType() === 'Educator') {
            $builder->add('email', TextType::class, [
                'constraints'    => [
                    new NotNull(['message' => 'Please enter a value.', 'groups' => ['USER_IMPORT_USER_INFO']]),
                    new EmailAlreadyExists($emailCache, ['groups' => ['USER_IMPORT_USER_INFO']]),
                ],
            ]);
        }


        $builder->add('tempPassword', TextType::class, [
            'constraints'    => [
                new NotNull(['message' => 'Please enter a value.', 'groups' => ['USER_IMPORT_USER_INFO']]),
            ],
            'attr' => [
                'readonly' => true
            ]
        ]);

        $builder->add('tempPasswordEncrypted', HiddenType::class, []);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'educatorEmailCache' => [],
            'usernameCache' => [],
            'emailCache' => [],
        ]);

        $resolver->setRequired('userImport');

    }

}
