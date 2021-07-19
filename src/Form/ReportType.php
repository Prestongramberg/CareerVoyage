<?php

namespace App\Form;

use App\Entity\Report;
use App\Entity\ReportGroup;
use App\Report\Form\ReportColumnType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ReportType
 *
 * @package App\Form\Property
 */
class ReportType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('reportName', TextType::class, [
            'label' => 'Report Name',
            'constraints' => [new NotBlank()],
            'label_attr' => [
                'class' => 'field-label',
            ],
            'attr' => [
                'placeholder' => 'Report Name',
            ],
        ]);
        $builder->add('reportDescription', TextareaType::class, [
            'required' => false,
            'label' => 'Report Description',
            'label_attr' => [
                'class' => 'field-label',
            ],
            'attr' => [],
        ]);

        $builder->add('reportGroups', EntityType::class, [
                'class'         => ReportGroup::class,
                'choice_label'  => 'name',
                'expanded'      => false,
                'multiple'      => true,
                'placeholder'   => '-- All Groups --',
            ]
        );

        $builder->add('reportEntityClassName', ChoiceType::class, [
            'required' => true,
            'constraints' => [new NotBlank()],
            'placeholder' => '-- Select Entity --',
            'choices' => Report::$reportEntityClassNameMap,
            'label' => 'Entity',
        ]);

        $builder->add('reportRules', HiddenType::class, [
            'attr' => [
                'class' => 'js-rules',
            ],
        ]);

        $builder->add('reportColumns', CollectionType::class, array (
            'entry_type' => ReportColumnType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'prototype_name' => '__prototype_one__',
            'label' => false,
            'by_reference' => false,
        ));

        $builder->add('reportShare', ReportShareType::class);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
        ])->setRequired([]);
    }
}