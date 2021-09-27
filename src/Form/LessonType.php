<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\Industry;
use App\Entity\Lesson;
use App\Entity\SecondaryIndustry;
use App\Repository\SecondaryIndustryRepository;
use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LessonType extends AbstractType
{
    /**
     * @var SecondaryIndustryRepository
     */
    private $secondaryIndustryRepository;

    /**
     * EditCompanyExperienceType constructor.
     *
     * @param SecondaryIndustryRepository $secondaryIndustryRepository
     */
    public function __construct(SecondaryIndustryRepository $secondaryIndustryRepository)
    {
        $this->secondaryIndustryRepository = $secondaryIndustryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $imageConstraints = [
            new NotBlank(['groups' => ['CREATE']]),
        ];

        $builder->add('title', TextType::class, [
            'attr' => [
                'placeholder' => 'Interviewing for a job',
            ]
        ])
                ->add('shortDescription', TextareaType::class, [
                    'attr' => [
                        'placeholder' => 'How can I prepare to succeed in a job interview',
                    ]
                ])
                ->add('summary', TextType::class, [])
                ->add('grades', EntityType::class, [
                    'class' => Grade::class,
                    'choice_label' => 'title',
                    'expanded' => false,
                    'multiple' => true,
                ]);

        $builder->add('primaryIndustry', EntityType::class, [
            'class' => Industry::class,
            'choice_label' => 'name',
            'placeholder' => 'Please select the industry sector this topic relates to',
            'expanded' => false,
            'multiple' => false,
        ])->add('primaryCourse', EntityType::class, [
            'class' => Course::class,
            'choice_label' => 'title',
            'expanded' => false,
            'multiple' => false,
            'placeholder' => 'Please select a primary course',
            'empty_data' => null,
            'required' => true,
            'query_builder' => function (CourseRepository $c) {
                return $c->createAlphabeticalSearch();
            },
        ])->add(
            'secondaryCourses',
            EntityType::class,
            [
                'class' => Course::class,
                'choice_label' => 'title',
                'expanded' => false,
                'multiple' => true,
                'query_builder' => function (CourseRepository $c) {
                    return $c->createAlphabeticalSearch();
                },
            ]
        )->add('learningOutcomes', TextareaType::class, [

        ])->add('educationalStandards', TextareaType::class, [

        ]);


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            /** @var Lesson $data */
            $data = $event->getData();
            $form = $event->getForm();

            $this->modifyForm($event->getForm(), $data->getPrimaryIndustry());
        });

        $builder->get('primaryIndustry')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Industry $industry */
            $industry = $event->getForm()->getData();

            $this->modifyForm($event->getForm()->getParent(), $industry);
        });

    }

    private function modifyForm(FormInterface $form, Industry $industry = null)
    {

        if (!$industry) {
            $choices = [];
        } else {
            $choices = $this->secondaryIndustryRepository->findBy([
                'primaryIndustry' => $industry->getId(),
            ]);
        }

        $form->add('secondaryIndustries', EntityType::class, [
            'class' => SecondaryIndustry::class,
            'choices' => $choices,
            'choice_label' => 'name',
            'expanded' => false,
            'multiple' => true,
            'choice_attr' => function ($choice, $key, $value) {
                return ['class' => 'uk-checkbox'];
            },
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
        ]);

        $resolver->setRequired(['lesson', 'skip_validation']);

    }
}
