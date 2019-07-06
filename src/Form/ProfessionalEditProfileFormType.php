<?php

namespace App\Form;

use App\Entity\ProfessionalUser;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class ProfessionalEditProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'block_prefix' => 'wrapped_text',
            ])
            ->add('lastName', TextType::class)
            ->add('email')
            ->add('username')
            ->add('file', FileType::class, [
                'label' => 'Photo upload',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload files
                // everytime you edit the entity
                'required' => false,
                ])
            ->add('password', PasswordType::class)
            ->add('rolesWillingToFulfill', ChoiceType::class, [
                'choices'  => [
                    'Guest instructor' => 'GUEST_INSTRUCTOR',
                    'Site visit host for students' => 'SITE_VISIT_HOST_FOR_STUDENTS',
                    'Field experiences' => 'FIELD_EXPERIENCES',
                    'Informational interviewer' => 'INFORMATIONAL_INTERVIEWER',
                    'Job shadow host' => 'JOB_SHADOW_HOST',
                ],
                'expanded' => true,
                'multiple' => true
            ])
            ->add('interests', TextareaType::class)
            ->add('briefBio', TextareaType::class)
            ->add('linkedinProfile', TextType::class)
            ->add('phone', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProfessionalUser::class,
            'validation_groups' => ['EDIT'],
        ]);
    }
}
