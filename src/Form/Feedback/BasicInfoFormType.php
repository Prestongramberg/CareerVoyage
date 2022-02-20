<?php

namespace App\Form\Feedback;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class BasicInfoFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', TextType::class, [])
                ->add('fullName', TextType::class, [
                    'constraints' => [
                        new NotNull(['message' => 'Please enter your name.']),
                    ],
                ])
                ->add('feedbackProvider', ChoiceType::class, [
                    'choices'     => [
                        'Educator'     => 'Educator',
                        'Student'      => 'Student',
                        'Professional' => 'Professional',
                    ],
                    'constraints' => [
                        new NotNull(['message' => 'Please select your role.']),
                    ],
                    'multiple'    => false,
                    'expanded'    => true,
                ]);
    }

    public function getBlockPrefix()
    {
        return 'basicInfo';
    }

}
