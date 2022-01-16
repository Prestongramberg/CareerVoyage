<?php

namespace App\Form;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class ManageExperiencesFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', Filters\TextFilterType::class, [
            'condition_pattern' => FilterOperands::STRING_CONTAINS,
        ]);

        $builder->add('isRecurring', Filters\BooleanFilterType::class, [
            'placeholder' => 'Is Recurring Event',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
            'validation_groups' => array('filtering')
            // avoid NotBlank() constraint-related message
        ));
    }
}