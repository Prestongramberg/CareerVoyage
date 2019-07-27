<?php

namespace App\Form;

use App\Entity\Career;
use App\Entity\Company;
use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\Industry;
use App\Entity\Lesson;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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

class NewLessonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $imageConstraints = [
            new Image([
                'maxSize' => '5M'
            ]),
            new NotBlank(['groups' => ['CREATE']])
        ];

        $builder
            ->add('title', TextType::class, [])
            ->add('careers', EntityType::class, [
                'class' => Career::class,
                'choice_label' => 'title',
                'expanded'  => true,
                'multiple'  => true,
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'uk-checkbox'];
                },
            ])
            ->add('grades', EntityType::class, [
                'class' => Grade::class,
                'choice_label' => 'title',
                'expanded'  => true,
                'multiple'  => true,
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'uk-checkbox'];
                },
            ])
            ->add('primaryCourse', EntityType::class, [
                'class' => Course::class,
                'choice_label' => 'title',
                'expanded'  => false,
                'multiple'  => false,
            ])
            ->add('secondaryCourses', EntityType::class, [
                'class' => Course::class,
                'choice_label' => 'title',
                'expanded'  => true,
                'multiple'  => true,
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'uk-checkbox'];
                },
            ])
            ->add('shortDescription', TextareaType::class, [])
            ->add('summary', TextType::class, [])
            ->add('learningOutcomes', TextareaType::class, [])
            ->add('educationalStandards', TextareaType::class, [])
            ->add('thumbnailImage', FileType::class, [
                'label' => 'Thumbnail image',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload files
                // everytime you edit the entity
                'required' => false,
            ])
            ->add('featuredImage', FileType::class, [
                'label' => 'Featured image',
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload files
                // everytime you edit the entity
                'required' => false,
            ])
            ->add('resources', LessonResourceType::class, array(
                'label' => false,
                'mapped' => false
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
            'validation_groups' => ['CREATE'],
        ]);

    }
}
