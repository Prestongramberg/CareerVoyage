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
use App\Entity\User;
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
 * Class LessonTeachableRelatedFilterType
 *
 * @package App\Form\Filter
 */
class LessonTeachableRelatedOptionsFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'hasExpertPresenter', Filters\BooleanFilterType::class, [
                'placeholder' => 'Expert Presenter Available',
                'label' => 'Expert Presenter Available',
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                    if (empty($values['value'])) {
                        return null;
                    }

                    $hasExpertPresenter = $values['value'] === 'y';

                    $queryBuilder = $filterQuery->getQueryBuilder();

                    if ($hasExpertPresenter) {
                        $queryBuilder->orWhere('expert_presenter_lesson_teachables_users.roles LIKE :role')
                                     ->setParameter('role', '%"' . User::ROLE_PROFESSIONAL_USER . '"%');
                    }

                    $newFilterQuery = new ORMQuery($queryBuilder);

                    return $newFilterQuery->getExpr();
                },
            ]
        );

        $builder->add(
            'hasEducatorRequester', Filters\BooleanFilterType::class, [
                'placeholder' => 'Educator Requested',
                'label' => 'Educator Requested',
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                    if (empty($values['value'])) {
                        return null;
                    }

                    $hasEducatorRequester = $values['value'] === 'y';

                    $queryBuilder = $filterQuery->getQueryBuilder();

                    if ($hasEducatorRequester) {
                        $queryBuilder->orWhere('educator_requester_lesson_teachables_users.roles LIKE :role')
                                     ->setParameter('role', '%"' . User::ROLE_EDUCATOR_USER . '"%');
                    }

                    $newFilterQuery = new ORMQuery($queryBuilder);

                    return $newFilterQuery->getExpr();
                },
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'lesson_teachable_related_options';
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
            'validation_groups' => array('filtering') // avoid NotBlank() constraint-related message
        ));
    }

    // this allow us to use the "add_shared" option
    public function getParent()
    {
        return Filters\SharedableFilterType::class;
    }
}