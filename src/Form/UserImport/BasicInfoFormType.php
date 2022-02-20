<?php

namespace App\Form\UserImport;

use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\BooleanFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class BasicInfoFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('autogenerateUsername', ChoiceType::class, [
            //'placeholder'  => 'Events with/without feedback',
            'choices'  => [
                'Yes' => true,
                'No'  => false,
            ],
            'required' => true,
            'label'    => 'Autogenerate Usernames?',
            'multiple' => false,
            'expanded' => true,
        ]);

        $builder->add('autogeneratePassword', ChoiceType::class, [
            //'placeholder'  => 'Events with/without feedback',
            'choices'  => [
                'Yes' => true,
                'No'  => false,
            ],
            'required' => true,
            'label'    => 'Autogenerate Passwords?',
            'multiple' => false,
            'expanded' => true,
        ]);

        $builder->add('type', ChoiceType::class, [
            'choices'     => [
                'Educator' => 'Educator',
                'Student'  => 'Student',
            ],
            'constraints' => [
                new NotNull(['message' => 'Please select which type of user you are importing.', 'groups' => ['USER_IMPORT_BASIC_INFO']]),
            ],
            'required'    => true,
            'multiple'    => false,
            'expanded'    => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'basicInfo';
    }

}
