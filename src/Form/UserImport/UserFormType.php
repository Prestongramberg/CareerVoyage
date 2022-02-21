<?php

namespace App\Form\UserImport;

use App\Entity\CompanyExperience;
use App\Entity\StudentUser;
use App\Entity\User;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\BooleanFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class UserFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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

        $builder->add('username', TextType::class, [
            'constraints'    => [
                new NotNull(['message' => 'Please enter a value.', 'groups' => ['USER_IMPORT_USER_INFO']]),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);

    }

}
