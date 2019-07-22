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
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;

class ProfessionalEditProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $imageConstraints = [
            new Image([
                'maxSize' => '5M'
            ])
        ];

        /** @var ProfessionalUser $professionalUser */
        $professionalUser = $options['professionalUser'];

        /*if (!$professionalUser->getPhoto()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Please upload an image',
                'groups'  => ['EDIT']
            ]);
        }*/

        $builder
            ->add('firstName', TextType::class, [
                'block_prefix' => 'wrapped_text',
            ])
            ->add('lastName', TextType::class)
            ->add('email')
            ->add('file', FileType::class, [
                'label' => 'Photo upload',
                'constraints' => $imageConstraints,

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload files
                // everytime you edit the entity
                'required' => false,
                ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password'
            ])
            ->add('rolesWillingToFulfill', ChoiceType::class, [
                'choices'  => [
                    'Guest instructor' => 'GUEST_INSTRUCTOR',
                    'Site visit host for students' => 'SITE_VISIT_HOST_FOR_STUDENTS',
                    'Field experiences' => 'FIELD_EXPERIENCES',
                    'Informational interviewer' => 'INFORMATIONAL_INTERVIEWER',
                    'Job shadow host' => 'JOB_SHADOW_HOST',
                ],
                'expanded' => true,
                'multiple' => true,
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'uk-checkbox'];
                },
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

        $resolver->setRequired([
           'professionalUser'
        ]);
    }
}
