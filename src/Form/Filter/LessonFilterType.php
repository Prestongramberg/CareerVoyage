<?php

namespace App\Form\Filter;

use App\Entity\Company;
use App\Entity\Course;
use App\Entity\EducatorUser;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\Region;
use App\Entity\RegionalCoordinator;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SecondaryIndustry;
use App\Entity\SiteAdminUser;
use App\Entity\State;
use App\Entity\StateCoordinator;
use App\Entity\StudentUser;
use App\Form\CompanyFilterType;
use App\Service\Geocoder;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 * Class LessonFilterType
 *
 * @package App\Form\Filter
 */
class LessonFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', Filters\TextFilterType::class, [
                       'condition_pattern' => FilterOperands::STRING_CONTAINS,
                   ]
        );

        $builder->add(
            'primaryCourse', Filters\EntityFilterType::class, [
                               'class'         => Course::class,
                               'choice_label'  => 'title',
                               'expanded'      => false,
                               'multiple'      => false,
                               'placeholder'   => 'FILTER BY COURSE',
                               'query_builder' => function (\App\Repository\CourseRepository $courseRepository) {
                                   return $courseRepository->createQueryBuilder('c')
                                                           ->orderBy('c.title', 'ASC');
                               },
                           ]
        );

        $builder->add(
            'primaryIndustry', Filters\EntityFilterType::class, [
                                 'class'         => Industry::class,
                                 'choice_label'  => 'name',
                                 'expanded'      => false,
                                 'multiple'      => false,
                                 'placeholder'   => 'FILTER BY INDUSTRY',
                                 'query_builder' => function (
                                     \App\Repository\IndustryRepository $industryRepository
                                 ) {
                                     return $industryRepository->createQueryBuilder('i')
                                                               ->orderBy('i.name', 'ASC');
                                 },
                             ]
        );

        $builder->add(
            'hasExpertPresenters', Filters\BooleanFilterType::class, [
                                     'placeholder' => 'Expert Presenter Available',
                                     'label'       => 'Expert Presenter Available',
                                 ]
        );

        $builder->add(
            'hasEducatorRequestors', Filters\BooleanFilterType::class, [
                                       'placeholder' => 'Educator Requested',
                                       'label'       => 'Educator Requested',
                                   ]
        );
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array (
                'csrf_protection'   => false,
                'validation_groups' => array ('filtering') // avoid NotBlank() constraint-related message
            )
        );
    }
}