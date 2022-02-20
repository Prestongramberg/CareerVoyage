<?php

namespace App\Form\UserImport;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class ColumnMappingInfoFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
    }

    public function getBlockPrefix()
    {
        return 'columnMappingInfo';
    }

}
