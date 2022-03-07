<?php

namespace App\Form\UserImport;

use App\Entity\UserImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class ColumnMappingInfoFormType extends AbstractType
{

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

    }

    public function getBlockPrefix()
    {
        return 'columnMappingInfo';
    }

}
