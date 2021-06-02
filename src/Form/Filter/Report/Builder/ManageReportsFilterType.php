<?php

namespace App\Form\Filter\Report\Builder;

use App\Entity\Report;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ManageReportsFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('reportName', TextType::class, [
            'required' => false,
            'label' => 'Name',
            'label_attr' => [
                'class' => 'field-label'
            ]
        ]);
        $builder->add('reportDescription', TextType::class, [
            'required' => false,
            'label' => 'Description',
            'label_attr' => [
                'class' => 'field-label'
            ]
        ]);

        $builder->add('reportEntityClassName', Filters\ChoiceFilterType::class, [
            'required' => false,
            'choices' => Report::$reportEntityClassNameMap,
            'placeholder' => '-- All Entities --',
            'label' => 'Entity',
            'label_attr' => [
                'class' => 'field-label'
            ],
            'expanded' => false,
            'multiple' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return 'search';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
            'allow_extra_fields' => true,
            'validation_groups' => array('filtering') // avoid NotBlank() constraint-related message
        ));
    }
}