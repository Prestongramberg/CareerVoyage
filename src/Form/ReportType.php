<?php

namespace App\Form;

use App\Entity\AdminUser;
use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\Company;
use App\Entity\CompanyFavorite;
use App\Entity\Course;
use App\Entity\EducatorUser;
use App\Entity\Experience;
use App\Entity\Feedback;
use App\Entity\Grade;
use App\Entity\Industry;
use App\Entity\Lesson;
use App\Entity\LessonFavorite;
use App\Entity\LessonTeachable;
use App\Entity\Region;
use App\Entity\RegionalCoordinator;
use App\Entity\Registration;
use App\Entity\Report;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SecondaryIndustry;
use App\Entity\Share;
use App\Entity\SiteAdminUser;
use App\Entity\State;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Report\Form\ReportColumnType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ReportType
 *
 * @package App\Form\Property
 */
class ReportType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('reportName', TextType::class, [
            'label' => 'Report Name',
            'constraints' => [new NotBlank()],
            'label_attr' => [
                'class' => 'field-label',
            ],
            'attr' => [
                'placeholder' => 'Report Name',
            ],
        ]);
        $builder->add('reportDescription', TextareaType::class, [
            'required' => false,
            'label' => 'Report Description',
            'label_attr' => [
                'class' => 'field-label',
            ],
            'attr' => [],
        ]);


        $builder->add('reportEntityClassName', ChoiceType::class, [
            'required' => true,
            'constraints' => [new NotBlank()],
            'placeholder' => '-- Select Entity --',
            'choices' => Report::$reportEntityClassNameMap,
            'label' => 'Entity',
        ]);

        $builder->add('reportRules', HiddenType::class, [
            'attr' => [
                'class' => 'js-rules',
            ],
        ]);

        $builder->add('reportColumns', CollectionType::class, array (
            'entry_type' => ReportColumnType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'prototype_name' => '__prototype_one__',
            'label' => false,
            'by_reference' => false,
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
        ])->setRequired([]);
    }
}