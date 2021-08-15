<?php

namespace App\Form;

use App\Entity\AdminUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SuggestNewDatesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('dateOptionOne', DateTimeType::class, [
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy h:mm a',
            'html5' => false,
            'constraints' => [
                new NotBlank(['message' => 'Please select a date'])
            ],
        ]);

        $builder->add('dateOptionTwo', DateTimeType::class, [
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy h:mm a',
            'html5' => false,
            'constraints' => [
                new NotBlank(['message' => 'Please select a date'])
            ],
        ]);

        $builder->add('dateOptionThree', DateTimeType::class, [
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy h:mm a',
            'html5' => false,
            'constraints' => [
                new NotBlank(['message' => 'Please select a date'])
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);

    }
}
