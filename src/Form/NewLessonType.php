<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\Industry;
use App\Entity\Lesson;
use App\Entity\ProfessionalUser;
use App\Entity\SecondaryIndustry;
use App\Entity\User;
use App\Repository\SecondaryIndustryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
    /**
     * @var SecondaryIndustryRepository
     */
    private $secondaryIndustryRepository;

    /**
     * EditCompanyExperienceType constructor.
     * @param SecondaryIndustryRepository $secondaryIndustryRepository
     */
    public function __construct(SecondaryIndustryRepository $secondaryIndustryRepository)
    {
        $this->secondaryIndustryRepository = $secondaryIndustryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $imageConstraints = [
            new NotBlank(['groups' => ['CREATE']])
        ];

        $builder
            ->add('title', TextType::class, [])
            ->add(
                'grades',
                EntityType::class,
                [
                    'class' => Grade::class,
                    'choice_label' => 'title',
                    'expanded' => true,
                    'multiple' => true,
                    'choice_attr' => function ($choice, $key, $value) {
                        return ['class' => 'uk-checkbox'];
                    },
                ]
            )
            ->add(
                'primaryCourse',
                EntityType::class,
                [
                    'class' => Course::class,
                    'choice_label' => 'title',
                    'expanded' => false,
                    'multiple' => false,
                    'placeholder' => 'Please select a primary course',
                    'empty_data' => null,
                    'required' => true
                ]
            )
            ->add(
                'secondaryCourses',
                EntityType::class,
                [
                    'class' => Course::class,
                    'choice_label' => 'title',
                    'expanded' => true,
                    'multiple' => true,
                    'choice_attr' => function ($choice, $key, $value) {
                        return ['class' => 'uk-checkbox'];
                    },
                ]
            )->add('shortDescription', TextareaType::class, [])
            ->add('summary', TextType::class, [])
            ->add('learningOutcomes', TextareaType::class, [])
            ->add('educationalStandards', TextareaType::class, []);

        $builder->add(
            'secondaryIndustries',
            CollectionType::class,
            [
                'entry_type' => HiddenType::class,
                'label' => false,
                'allow_add' => true,
            ]
        );

        $builder->get('secondaryIndustries')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($secondaryIndustries) {
                        $ids = [];
                        foreach ($secondaryIndustries as $secondaryIndustry) {
                            $ids[] = $secondaryIndustry->getId();
                        }

                        return $ids;
                    },
                    function ($ids) {

                        $collection = new ArrayCollection();
                        foreach ($ids as $id) {
                            $collection->add($this->secondaryIndustryRepository->find($id));
                        }

                        return $collection;
                    }
                )
            );
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
