<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\Industry;
use App\Entity\Lesson;
use App\Repository\CourseRepository;
use App\Repository\ImageRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LessonType extends AbstractType
{

    /**
     * @var ImageRepository
     */
    private $imageRepository;

    /**
     * LessonType constructor.
     *
     * @param ImageRepository $imageRepository
     */
    public function __construct(ImageRepository $imageRepository)
    {
        $this->imageRepository = $imageRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('title', TextType::class, [
            'attr' => [
                'placeholder' => 'Interviewing for a job',
            ],
        ])
                ->add('shortDescription', TextareaType::class, [
                    'attr' => [
                        'placeholder' => 'How can I prepare to succeed in a job interview',
                    ],
                ])
                ->add('summary', TextType::class, [])
                ->add('grades', EntityType::class, [
                    'class' => Grade::class,
                    'choice_label' => 'title',
                    'expanded' => false,
                    'multiple' => true,
                ]);

        $builder->add('primaryIndustries', EntityType::class, [
            'class' => Industry::class,
            'choice_label' => 'name',
            'expanded' => false,
            'multiple' => true,
        ])->add('primaryCourses', EntityType::class, [
            'class' => Course::class,
            'choice_label' => 'title',
            'expanded' => false,
            'multiple' => true,
            'query_builder' => function (CourseRepository $c) {
                return $c->createAlphabeticalSearch();
            },
        ]);


        $builder->add('learningOutcomes', TextareaType::class, [])
                ->add('educationalStandards', TextareaType::class, [])
                ->add('thumbnailImage', HiddenType::class)
                ->add('featuredImage', HiddenType::class);

        $builder->get('thumbnailImage')->addModelTransformer(new CallbackTransformer(
            function ($image) {

                if (!$image) {
                    return null;
                }

                return $image;
            },
            function ($image) {

                if (!$image) {
                    return null;
                }

                if(is_numeric($image)) {
                    $image = $this->imageRepository->find($image);
                    return $image->getFileName();
                }

                return $image;
            }
        ));

        $builder->get('featuredImage')->addModelTransformer(new CallbackTransformer(
            function ($image) {

                if (!$image) {
                    return null;
                }

                return $image;
            },
            function ($image) {

                if (!$image) {
                    return null;
                }

                if(is_numeric($image)) {
                    $image = $this->imageRepository->find($image);
                    return $image->getFileName();
                }

                return $image;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
            'validation_groups' => ['LESSON_GENERAL'],
        ]);
    }
}
