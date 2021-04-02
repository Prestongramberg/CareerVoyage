<?php

namespace App\Form;

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
use App\Model\GlobalShareFilters;
use App\Repository\IndustryRepository;
use App\Repository\SecondaryIndustryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;
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

class SearchFilterType extends AbstractType
{
    /**
     * @var SecondaryIndustryRepository
     */
    private $secondaryIndustryRepository;

    /**
     * SearchFilterType constructor.
     *
     * @param SecondaryIndustryRepository $secondaryIndustryRepository
     */
    public function __construct(SecondaryIndustryRepository $secondaryIndustryRepository)
    {
        $this->secondaryIndustryRepository = $secondaryIndustryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $userRole = $options['userRole'];

        $builder->add(
            'search', Filters\TextFilterType::class, [
                        'apply_filter' => function (QueryInterface $filterQuery, $field, $values) use ($userRole) {

                            if (empty($values['value'])) {
                                return null;
                            }

                            $searchTerm = $values['value'];

                            $queryBuilder = $filterQuery->getQueryBuilder();

                            if ($userRole === User::ROLE_PROFESSIONAL_USER) {
                                $queryBuilder->andWhere('u.firstName LIKE :searchTerm OR u.lastName LIKE :searchTerm OR u.email LIKE :searchTerm OR u.username Like :searchTerm OR u.interests LIKE :searchTerm')
                                             ->setParameter('searchTerm', '%' . $searchTerm . '%');
                            } else {
                                $queryBuilder->andWhere('u.firstName LIKE :searchTerm OR u.lastName LIKE :searchTerm OR u.email LIKE :searchTerm OR u.username Like :searchTerm')
                                             ->setParameter('searchTerm', '%' . $searchTerm . '%');
                            }

                            $newFilterQuery = new ORMQuery($queryBuilder);

                            $expression = $newFilterQuery->getExpr()->eq('1', '1');

                            return $newFilterQuery->createCondition($expression);
                        },
                        'mapped'       => false,
                        'label'        => $userRole === User::ROLE_PROFESSIONAL_USER ? 'Search by interest, name, email, or username' : 'Search by name, email, or username',
                    ]
        );

        $builder->add(
            'userRole', Filters\ChoiceFilterType::class, [
                          'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                              if (empty($values['value'])) {
                                  return null;
                              }

                              $roles = is_array($values['value']) ? $values['value'] : [$values['value']];

                              $queryBuilder = $filterQuery->getQueryBuilder();


                              foreach ($roles as $role) {
                                  $queries[] = sprintf('u.roles LIKE :%s', $role);
                              }

                              $queryString = implode(" OR ", $queries);

                              $queryBuilder->andWhere($queryString);

                              foreach ($roles as $role) {
                                  $queryBuilder->setParameter($role, '%"' . $role . '"%');
                              }

                              $newFilterQuery = new ORMQuery($queryBuilder);

                              return $newFilterQuery->getExpr();
                          },
                          'expanded'     => false,
                          'multiple'     => false,
                          'required'     => false,
                          'mapped'       => false,
                          'choices'      => [
                              'Educator'             => 'ROLE_EDUCATOR_USER',
                              'Professional'         => 'ROLE_PROFESSIONAL_USER',
                              'Student'              => 'ROLE_STUDENT_USER',
                              'School Administrator' => 'ROLE_SCHOOL_ADMINISTRATOR_USER',
                          ],
                          'label'        => 'FILTER BY USER ROLE',
                          'placeholder'  => 'FILTER BY USER ROLE',
                      ]
        );


        $builder->get('userRole')->addEventListener(
            FormEvents::POST_SUBMIT, function (FormEvent $event) {

            $userRole = $event->getForm()->getData();
            $form     = $event->getForm()->getParent();

            if (!$form) {
                return;
            }

            if (!$userRole) {
                return;
            }

            if ($userRole === User::ROLE_PROFESSIONAL_USER) {
                $this->addProfessionalFiltersToForm($event->getForm()->getParent());
            }

            if ($userRole === User::ROLE_EDUCATOR_USER) {
                $this->addEducatorFiltersToForm($event->getForm()->getParent());
            }

            if ($userRole === User::ROLE_STUDENT_USER) {
                $this->addStudentFiltersToForm($event->getForm()->getParent());
            }

            if ($userRole === User::ROLE_SCHOOL_ADMINISTRATOR_USER) {
                $this->addSchoolAdministratorFiltersToForm($event->getForm()->getParent());
            }
        }
        );

    }

    private function addProfessionalFiltersToForm(FormInterface $form)
    {

        if ($form->has('company')) {
            $form->remove('company');
        }

        if ($form->has('rolesWillingToFulfill')) {
            $form->remove('rolesWillingToFulfill');
        }

        if ($form->has('primaryIndustry')) {
            $form->remove('primaryIndustry');
        }

        $form->add(
            'company', Filters\EntityFilterType::class, [
                         'class'         => Company::class,
                         'choice_label'  => 'name',
                         'expanded'      => false,
                         'multiple'      => true,
                         'label'         => 'FILTER BY COMPANY',
                         'placeholder'   => 'FILTER BY COMPANY',
                         'query_builder' => function (\App\Repository\CompanyRepository $companyRepository) {
                             return $companyRepository->createQueryBuilder('c')
                                                      ->orderBy('c.name', 'ASC');
                         },
                     ]
        );

        $form->add(
            'rolesWillingToFulfill', Filters\EntityFilterType::class, [
                                       'class'         => RolesWillingToFulfill::class,
                                       'choice_label'  => 'name',
                                       'expanded'      => false,
                                       'multiple'      => true,
                                       'placeholder'   => 'FILTER BY ROLES',
                                       'label'         => 'FILTER BY ROLES',
                                       'query_builder' => function (
                                           \App\Repository\RolesWillingToFulfillRepository $rolesWillingToFulfillRepository
                                       ) {
                                           return $rolesWillingToFulfillRepository->createQueryBuilder('r')
                                                                                  ->orderBy('r.name', 'ASC');
                                       },
                                   ]
        );

        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'primaryIndustry', Filters\EntityFilterType::class, null, [
                                 'class'           => Industry::class,
                                 'choice_label'    => 'name',
                                 'expanded'        => false,
                                 'multiple'        => true,
                                 'placeholder'     => 'FILTER BY INDUSTRY',
                                 'label'           => 'FILTER BY INDUSTRY',
                                 'auto_initialize' => false,
                                 'query_builder'   => function (
                                     \App\Repository\IndustryRepository $industryRepository
                                 ) {
                                     return $industryRepository->createQueryBuilder('i')
                                                               ->orderBy('i.name', 'ASC');
                                 },
                             ]
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT, function (FormEvent $event) {

            $industries = $event->getForm()->getData();
            $form       = $event->getForm()->getParent();

            if (!$form) {
                return;
            }

            if ($form->has('secondaryIndustries')) {
                $form->remove('secondaryIndustries');
            }

            if (!$industries) {
                return;
            }

            if ($industries instanceof ArrayCollection) {
                $industries = $industries->toArray();
            }

            if (empty($industries)) {
                return;
            }

            $industryIds = array_map(
                function (Industry $industry) {
                    return $industry->getId();
                }, $industries
            );

            $form->add(
                'secondaryIndustries', Filters\EntityFilterType::class, [
                                         'class'         => SecondaryIndustry::class,
                                         'choice_label'  => 'name',
                                         'expanded'      => false,
                                         'multiple'      => true,
                                         'placeholder'   => 'FILTER BY CAREER',
                                         'label'         => 'FILTER BY CAREER',
                                         'query_builder' => function (
                                             \App\Repository\SecondaryIndustryRepository $secondaryIndustryRepository
                                         ) use ($industryIds) {
                                             return $secondaryIndustryRepository->createQueryBuilder('si')
                                                                                ->innerJoin('si.primaryIndustry', 'primaryIndustry')
                                                                                ->andWhere('primaryIndustry.id IN (:ids)')
                                                                                ->setParameter('ids', $industryIds)
                                                                                ->orderBy('si.name', 'ASC');
                                         },
                                     ]
            );


        }
        );

        $form->add($builder->getForm());

    }

    public function addEducatorFiltersToForm(FormInterface $form)
    {

        if ($form->has('school')) {
            $form->remove('school');
        }

        if ($form->has('myCourses')) {
            $form->remove('myCourses');
        }

        if ($form->has('primaryIndustry')) {
            $form->remove('primaryIndustry');
        }


        $form->add(
            'school', Filters\EntityFilterType::class, [
                        'class'         => School::class,
                        'choice_label'  => 'name',
                        'expanded'      => false,
                        'multiple'      => true,
                        'placeholder'   => 'FILTER BY SCHOOL',
                        'label'         => 'FILTER BY SCHOOL',
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('s')
                                      ->orderBy('s.name', 'ASC');
                        },
                    ]
        );

        $form->add(
            'myCourses', Filters\EntityFilterType::class, [
                           'class'         => Course::class,
                           'choice_label'  => 'title',
                           'expanded'      => false,
                           'multiple'      => true,
                           'placeholder'   => 'FILTER BY COURSE',
                           'label'         => 'FILTER BY COURSE',
                           'query_builder' => function (
                               EntityRepository $er
                           ) {
                               return $er->createQueryBuilder('c')
                                         ->orderBy('c.title', 'ASC');
                           },
                       ]
        );

        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'primaryIndustry', Filters\EntityFilterType::class, null, [
                                 'class'           => Industry::class,
                                 'choice_label'    => 'name',
                                 'expanded'        => false,
                                 'multiple'        => true,
                                 'placeholder'     => 'FILTER BY INDUSTRY',
                                 'auto_initialize' => false,
                                 'label'           => 'FILTER BY INDUSTRY',
                                 'query_builder'   => function (
                                     EntityRepository $er
                                 ) {
                                     return $er->createQueryBuilder('i')
                                               ->orderBy('i.name', 'ASC');
                                 },
                                 'apply_filter'    => function (QueryInterface $filterQuery, $field, $values) {

                                     $industries = $values['value'];
                                     if ($industries instanceof ArrayCollection) {
                                         $industries = $values['value']->toArray();
                                     }

                                     if (empty($industries)) {
                                         return null;
                                     }

                                     $industryIds = array_map(
                                         function (Industry $industry) {
                                             return $industry->getId();
                                         }, $industries
                                     );


                                     $queryBuilder = $filterQuery->getQueryBuilder();

                                     $queryBuilder->innerJoin('u.secondaryIndustries', 'si')
                                                  ->innerJoin('si.primaryIndustry', 'pi')
                                                  ->andWhere('pi.id IN (:ids)')
                                                  ->setParameter('ids', $industryIds);

                                     $newFilterQuery = new ORMQuery($queryBuilder);

                                     $expression = $newFilterQuery->getExpr()->eq('1', '1');

                                     return $newFilterQuery->createCondition($expression);
                                 },
                             ]
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT, function (FormEvent $event) {

            $industries = $event->getForm()->getData();
            $form       = $event->getForm()->getParent();

            if (!$form) {
                return;
            }

            if ($form->has('secondaryIndustries')) {
                $form->remove('secondaryIndustries');
            }

            if (!$industries) {
                return;
            }

            if ($industries instanceof ArrayCollection) {
                $industries = $industries->toArray();
            }

            if (empty($industries)) {
                return;
            }

            $industryIds = array_map(
                function (Industry $industry) {
                    return $industry->getId();
                }, $industries
            );

            $form->add(
                'secondaryIndustries', Filters\EntityFilterType::class, [
                                         'class'         => SecondaryIndustry::class,
                                         'choice_label'  => 'name',
                                         'expanded'      => false,
                                         'multiple'      => true,
                                         'placeholder'   => 'FILTER BY CAREER',
                                         'label'         => 'FILTER BY CAREER',
                                         'query_builder' => function (
                                             EntityRepository $er
                                         ) use ($industryIds) {
                                             return $er->createQueryBuilder('si')
                                                       ->innerJoin('si.primaryIndustry', 'primaryIndustry')
                                                       ->andWhere('primaryIndustry.id IN (:ids)')
                                                       ->setParameter('ids', $industryIds)
                                                       ->orderBy('si.name', 'ASC');
                                         },
                                         'apply_filter'  => function (QueryInterface $filterQuery, $field, $values) {

                                             $industries = $values['value'];

                                             if ($industries instanceof ArrayCollection) {
                                                 $industries = $values['value']->toArray();
                                             }

                                             if (empty($industries)) {
                                                 return null;
                                             }

                                             $industryIds = array_map(
                                                 function (SecondaryIndustry $industry) {
                                                     return $industry->getId();
                                                 }, $industries
                                             );


                                             $queryBuilder = $filterQuery->getQueryBuilder();

                                             $queryBuilder->andWhere('si.id IN (:ids)')
                                                          ->setParameter('ids', $industryIds);

                                             $newFilterQuery = new ORMQuery($queryBuilder);

                                             $expression = $newFilterQuery->getExpr()->eq('1', '1');

                                             return $newFilterQuery->createCondition($expression);
                                         },
                                     ]
            );


        }
        );

        $form->add($builder->getForm());
    }

    public function addStudentFiltersToForm(FormInterface $form)
    {

        if ($form->has('school')) {
            $form->remove('school');
        }

        if ($form->has('primaryIndustry')) {
            $form->remove('primaryIndustry');
        }


        $form->add(
            'school', Filters\EntityFilterType::class, [
                        'class'         => School::class,
                        'choice_label'  => 'name',
                        'expanded'      => false,
                        'multiple'      => true,
                        'placeholder'   => 'FILTER BY SCHOOL',
                        'label'         => 'FILTER BY SCHOOL',
                        'query_builder' => function (\App\Repository\SchoolRepository $schoolRepository) {
                            return $schoolRepository->createQueryBuilder('s')
                                                    ->orderBy('s.name', 'ASC');
                        },
                    ]
        );

        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'primaryIndustry', Filters\EntityFilterType::class, null, [
                                 'class'           => Industry::class,
                                 'choice_label'    => 'name',
                                 'expanded'        => false,
                                 'multiple'        => true,
                                 'placeholder'     => 'FILTER BY INDUSTRY',
                                 'auto_initialize' => false,
                                 'label'           => 'FILTER BY INDUSTRY',
                                 'query_builder'   => function (
                                     EntityRepository $er
                                 ) {
                                     return $er->createQueryBuilder('i')
                                               ->orderBy('i.name', 'ASC');
                                 },
                                 'apply_filter'    => function (QueryInterface $filterQuery, $field, $values) {

                                     $industries = $values['value'];
                                     if ($industries instanceof ArrayCollection) {
                                         $industries = $values['value']->toArray();
                                     }

                                     if (empty($industries)) {
                                         return null;
                                     }

                                     $industryIds = array_map(
                                         function (Industry $industry) {
                                             return $industry->getId();
                                         }, $industries
                                     );


                                     $queryBuilder = $filterQuery->getQueryBuilder();

                                     $queryBuilder->innerJoin('u.secondaryIndustries', 'si')
                                                  ->innerJoin('si.primaryIndustry', 'pi')
                                                  ->andWhere('pi.id IN (:ids)')
                                                  ->setParameter('ids', $industryIds);

                                     $newFilterQuery = new ORMQuery($queryBuilder);

                                     $expression = $newFilterQuery->getExpr()->eq('1', '1');

                                     return $newFilterQuery->createCondition($expression);
                                 },
                             ]
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT, function (FormEvent $event) {

            $industries = $event->getForm()->getData();
            $form       = $event->getForm()->getParent();

            if (!$form) {
                return;
            }

            if ($form->has('secondaryIndustries')) {
                $form->remove('secondaryIndustries');
            }

            if (!$industries) {
                return;
            }

            if ($industries instanceof ArrayCollection) {
                $industries = $industries->toArray();
            }

            if (empty($industries)) {
                return;
            }

            $industryIds = array_map(
                function (Industry $industry) {
                    return $industry->getId();
                }, $industries
            );

            $form->add(
                'secondaryIndustries', Filters\EntityFilterType::class, [
                                         'class'         => SecondaryIndustry::class,
                                         'choice_label'  => 'name',
                                         'expanded'      => false,
                                         'multiple'      => true,
                                         'placeholder'   => 'FILTER BY CAREER',
                                         'label'         => 'FILTER BY CAREER',
                                         'query_builder' => function (
                                             EntityRepository $er
                                         ) use ($industryIds) {
                                             return $er->createQueryBuilder('si')
                                                       ->innerJoin('si.primaryIndustry', 'primaryIndustry')
                                                       ->andWhere('primaryIndustry.id IN (:ids)')
                                                       ->setParameter('ids', $industryIds)
                                                       ->orderBy('si.name', 'ASC');
                                         },
                                         'apply_filter'  => function (QueryInterface $filterQuery, $field, $values) {

                                             $industries = $values['value'];

                                             if ($industries instanceof ArrayCollection) {
                                                 $industries = $values['value']->toArray();
                                             }

                                             if (empty($industries)) {
                                                 return null;
                                             }

                                             $industryIds = array_map(
                                                 function (SecondaryIndustry $industry) {
                                                     return $industry->getId();
                                                 }, $industries
                                             );


                                             $queryBuilder = $filterQuery->getQueryBuilder();

                                             $queryBuilder->andWhere('si.id IN (:ids)')
                                                          ->setParameter('ids', $industryIds);

                                             $newFilterQuery = new ORMQuery($queryBuilder);

                                             $expression = $newFilterQuery->getExpr()->eq('1', '1');

                                             return $newFilterQuery->createCondition($expression);
                                         },
                                     ]
            );


        }
        );

        $form->add($builder->getForm());
    }

    public function addSchoolAdministratorFiltersToForm(FormInterface $form)
    {

        if ($form->has('schools')) {
            $form->remove('schools');
        }

        $form->add(
            'schools', Filters\EntityFilterType::class, [
                         'class'         => School::class,
                         'choice_label'  => 'name',
                         'expanded'      => false,
                         'multiple'      => true,
                         'placeholder'   => 'FILTER BY SCHOOL',
                         'label'         => 'FILTER BY SCHOOL',
                         'query_builder' => function (\App\Repository\SchoolRepository $schoolRepository) {
                             return $schoolRepository->createQueryBuilder('s')
                                                     ->orderBy('s.name', 'ASC');
                         },
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
                'validation_groups' => array ('filtering'), // avoid NotBlank() constraint-related message
                'userRole'          => null,
            )
        );
    }
}