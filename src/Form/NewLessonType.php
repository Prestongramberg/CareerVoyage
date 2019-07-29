<?php

namespace App\Form;

use App\Entity\Career;
use App\Entity\Company;
use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\Industry;
use App\Entity\Lesson;
use App\Entity\ProfessionalUser;
use App\Entity\SecondaryIndustry;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
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
            ->add('primaryIndustry', EntityType::class, [
                'class' => Industry::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Select a primary Industry'
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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $data = $event->getData();
            if(!$data->getPrimaryIndustry()) {
                return;
            }
            $this->modifyForm($event->getForm(), $data->getPrimaryIndustry());
        });

        $builder->get('primaryIndustry')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Industry $industry */
            $industry = $event->getForm()->getData();

            if(!$industry) {
                return;
            }

            $this->modifyForm($event->getForm()->getParent(), $industry);
        });
    }

    private function modifyForm(FormInterface $form, Industry $industry) {

        $form->add('secondaryIndustries', EntityType::class, [
            'class' => SecondaryIndustry::class,
            'query_builder' => function (EntityRepository $er) use ($industry) {
                return $er->createQueryBuilder('si')
                    ->where('si.primaryIndustry = :primaryIndustry')
                    ->setParameter('primaryIndustry', $industry->getId());
            },
            'choice_label' => 'name',
            'expanded' => false,
            'multiple' => true
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
            'validation_groups' => function (FormInterface $form) {

                $skipValidation = $form->getConfig()->getOption('skip_validation');

                if($skipValidation) {
                    return [];
                }

                /** @var Company $data */
                $data = $form->getData();
                if(!$data->getPrimaryIndustry()) {
                    return ['CREATE'];
                }

                if($data->getPrimaryIndustry()) {
                    return ['CREATE', 'SECONDARY_INDUSTRY'];
                }

                return ['CREATE'];
            },
        ]);

        $resolver->setRequired(['skip_validation']);

    }
}
