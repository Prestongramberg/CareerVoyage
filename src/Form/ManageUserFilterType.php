<?php

namespace App\Form;

use App\Entity\EducatorUser;
use App\Entity\ProfessionalUser;
use App\Entity\Region;
use App\Entity\RegionalCoordinator;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SiteAdminUser;
use App\Entity\State;
use App\Entity\StateCoordinator;
use App\Entity\StudentUser;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

class ManageUserFilterType extends AbstractType
{

    public function __construct() {
        $this->status = [];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstName', Filters\TextFilterType::class);
        $builder->add('lastName', Filters\TextFilterType::class);
        $builder->add('email', Filters\TextFilterType::class);
        $builder->add('username', Filters\TextFilterType::class);

        switch ($options['filter_type']) {
            case ProfessionalUser::class:

                $builder->add('profileCompleted', Filters\BooleanFilterType::class, [
                    'placeholder' => 'Profile Completed'
                ]);

                $builder->add('company', CompanyFilterType::class, array(
                    'add_shared' => function (FilterBuilderExecuterInterface $qbe) {
                        $closure = function (QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                            $filterBuilder->leftJoin($alias . '.company', $joinAlias);
                        };

                        $qbe->addOnce($qbe->getAlias().'.company', 'c', $closure);
                    }
                ));
                $builder->add('status', Filters\ChoiceFilterType::class, [
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        $status = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        if($status === 'complete'){
                            $queryBuilder->andWhere('u.city IS NOT NULL');
                        } elseif($status === 'incomplete') {
                            $queryBuilder->andWhere('u.city IS NULL');
                        }

                        $newFilterQuery = new ORMQuery($queryBuilder);
                        return $newFilterQuery->getExpr();
                    },
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'mapped'   => false,
                    'choices'  => [
                        'Filter by Status' => '',
                        'Profile Complete' => 'complete',
                        'Profile Incomplete' => 'incomplete'
                    ]
                ]);
                break;
            case SiteAdminUser::class:
                $builder->add('site', SiteFilterType::class, array(
                    'add_shared' => function (FilterBuilderExecuterInterface $qbe) {
                        $closure = function (QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                            $filterBuilder->leftJoin($alias . '.site', $joinAlias);
                        };

                        $qbe->addOnce($qbe->getAlias().'.site', 's', $closure);
                    }
                ));
                break;
            case StateCoordinator::class:
                $builder->add('state', Filters\EntityFilterType::class, [
                    'class' => State::class,
                    'choice_label' => 'name',
                    'expanded'  => false,
                    'multiple'  => false,
                    'placeholder' => 'State'
                ]);
                $builder->add('site', SiteFilterType::class, array(
                    'add_shared' => function (FilterBuilderExecuterInterface $qbe) {
                        $closure = function (QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                            $filterBuilder->leftJoin($alias . '.site', $joinAlias);
                        };

                        $qbe->addOnce($qbe->getAlias().'.site', 's', $closure);
                    }
                ));
                break;
            case RegionalCoordinator::class:
                $builder->add('region', Filters\EntityFilterType::class, [
                    'class' => Region::class,
                    'choice_label' => 'name',
                    'expanded'  => false,
                    'multiple'  => false,
                    'placeholder' => 'Region'
                ]);
                $builder->add('site', SiteFilterType::class, array(
                    'add_shared' => function (FilterBuilderExecuterInterface $qbe) {
                        $closure = function (QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                            $filterBuilder->leftJoin($alias . '.site', $joinAlias);
                        };

                        $qbe->addOnce($qbe->getAlias().'.site', 's', $closure);
                    }
                ));
                break;
            case SchoolAdministrator::class:
                $builder->add('schools', Filters\EntityFilterType::class, [
                    'class' => School::class,
                    'choice_label' => 'name',
                    'expanded'  => false,
                    'multiple'  => false,
                    'placeholder' => 'School',
                    'query_builder' => function(\App\Repository\SchoolRepository $s) {
                        return $s->createAlphabeticalSearch();
                    }
                ]);

                $builder->add('site', SiteFilterType::class, array(
                    'add_shared' => function (FilterBuilderExecuterInterface $qbe) {
                        $closure = function (QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                            $filterBuilder->leftJoin($alias . '.site', $joinAlias);
                        };

                        $qbe->addOnce($qbe->getAlias().'.site', 's', $closure);
                    }
                ));
                break;
            case StudentUser::class:

                // $builder->add('profileCompleted', Filters\BooleanFilterType::class, [
                //     'placeholder' => 'Profile Completed'
                // ]);
                $builder->add('profileCompleted', Filters\ChoiceFilterType::class, [
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        $status = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        if($status === 'complete'){
                            $queryBuilder->innerJoin('u.secondaryIndustries','si')
                                         ->andWhere('si.id IS NOT NULL');
                        } elseif($status === 'incomplete') {
                            $queryBuilder->leftJoin('u.secondaryIndustries','si')
                                         ->andWhere('si.id IS NULL');
                        }

                        $newFilterQuery = new ORMQuery($queryBuilder);
                        return $newFilterQuery->getExpr();
                    },
                    'mapped' => false,
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'choices'  => [
                        'Profile Completed' => '',
                        'Yes' => 'complete',
                        'No' => 'incomplete'
                    ]
                ]);

                $builder->add('school', Filters\EntityFilterType::class, [
                    'class' => School::class,
                    'choice_label' => 'name',
                    'expanded'  => false,
                    'multiple'  => false,
                    'placeholder' => 'School',
                    'query_builder' => function(\App\Repository\SchoolRepository $s) {
                        return $s->createAlphabeticalSearch();
                    }
                ]);

                $builder->add('site', SiteFilterType::class, array(
                    'add_shared' => function (FilterBuilderExecuterInterface $qbe) {
                        $closure = function (QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                            $filterBuilder->leftJoin($alias . '.site', $joinAlias);
                        };

                        $qbe->addOnce($qbe->getAlias().'.site', 's', $closure);
                    }
                ));

                $builder->add('status', Filters\ChoiceFilterType::class, [
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        $status = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        if($status === 'active'){
                            $queryBuilder->andWhere('u.activated = true');
                        } elseif($status === 'inactive') {
                            $queryBuilder->andWhere('u.activated = false');
                        }

                        $newFilterQuery = new ORMQuery($queryBuilder);
                        return $newFilterQuery->getExpr();
                    },
                    'mapped' => false,
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'choices'  => [
                        'Account Status' => '',
                        'Active' => 'active',
                        'Inactive' => 'inactive'
                    ]
                ]);
                break;
            case EducatorUser::class:

                $builder->add('profileCompleted', Filters\BooleanFilterType::class, [
                    'placeholder' => 'Profile Completed'
                ]);

                $builder->add('school', Filters\EntityFilterType::class, [
                    'class' => School::class,
                    'choice_label' => 'name',
                    'expanded'  => false,
                    'multiple'  => false,
                    'placeholder' => 'School',
                    'query_builder' => function(\App\Repository\SchoolRepository $s) {
                        return $s->createAlphabeticalSearch();
                    }
                ]);

                $builder->add('site', SiteFilterType::class, array(
                    'add_shared' => function (FilterBuilderExecuterInterface $qbe) {
                        $closure = function (QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                            $filterBuilder->leftJoin($alias . '.site', $joinAlias);
                        };

                        $qbe->addOnce($qbe->getAlias().'.site', 's', $closure);
                    }
                ));

                $builder->add('status', Filters\ChoiceFilterType::class, [
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        $status = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        if($status === 'complete'){
                            $queryBuilder->andWhere('u.briefBio IS NOT NULL');
                        } elseif($status === 'incomplete') {
                            $queryBuilder->andWhere('u.briefBio IS NULL');
                        }

                        $newFilterQuery = new ORMQuery($queryBuilder);
                        return $newFilterQuery->getExpr();
                    },
                    'mapped' => false,
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'choices'  => [
                        'Filter by Status' => '',
                        'Profile Complete' => 'complete',
                        'Profile Incomplete' => 'incomplete'
                    ]
                ]);
                break;
        }
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
            'validation_groups' => array('filtering') // avoid NotBlank() constraint-related message
        ))->setRequired('filter_type');
    }
}