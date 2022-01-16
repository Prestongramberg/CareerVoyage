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

        $builder->add('startDateAndTime', Filters\DateRangeFilterType::class, [
                'left_date_options' => [
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    // prevents rendering it as type="date", to avoid HTML5 date pickers
                    'html5' => false,
                ],
                'right_date_options' => [
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    // prevents rendering it as type="date", to avoid HTML5 date pickers
                    'html5' => false,
                ],
            ]
        );
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