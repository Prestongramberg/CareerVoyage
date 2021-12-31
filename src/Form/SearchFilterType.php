<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Course;
use App\Entity\EducatorUser;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\Region;
use App\Entity\RegionalCoordinator;
use App\Entity\Request;
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
        /** @var Request $requestEntity */
        $requestEntity = $options['requestEntity'];
        $schoolIds     = $options['schoolIds'];
        /** @var User $loggedInUser */
        $loggedInUser  = $options['loggedInUser'];

        if ($requestEntity && $requestEntity->getRequestType() === Request::REQUEST_TYPE_JOB_BOARD) {
            $searchLabel = 'Search by professional\'s first and/or last name';
        } else {
            $searchLabel = 'Search by name, email, or username';
        }

        $builder->add('search', Filters\TextFilterType::class, [
            'apply_filter' => function (QueryInterface $filterQuery, $field, $values) use ($userRole) {

                if (empty($values['value'])) {
                    return null;
                }

                $searchTerm = $values['value'];

                $queryBuilder = $filterQuery->getQueryBuilder();

                $queryBuilder->andWhere("u.firstName IS NOT NULL and u.lastName IS NOT NULL and u.firstName != '' and u.lastName != ''")
                             ->andWhere('CONCAT(u.firstName, \' \', u.lastName) LIKE :searchTerm')
                             ->setParameter('searchTerm', '%' . $searchTerm . '%');

                $newFilterQuery = new ORMQuery($queryBuilder);

                $expression = $newFilterQuery->getExpr()->eq('1', '1');

                return $newFilterQuery->createCondition($expression);
            },
            'mapped'       => false,
            'label'        => $searchLabel,
        ]);

        $userRoleChoices = [
            'Educator'             => 'ROLE_EDUCATOR_USER',
            'Professional'         => 'ROLE_PROFESSIONAL_USER',
            'School Administrator' => 'ROLE_SCHOOL_ADMINISTRATOR_USER',
            'Student'              => 'ROLE_STUDENT_USER',
        ];

        if($loggedInUser instanceof StudentUser || $loggedInUser instanceof ProfessionalUser) {
            $userRoleChoices = [
                'Educator'             => 'ROLE_EDUCATOR_USER',
                'School Administrator' => 'ROLE_SCHOOL_ADMINISTRATOR_USER',
                'Student'              => 'ROLE_STUDENT_USER',
            ];
        }

        $builder->add('userRole', Filters\ChoiceFilterType::class, [
            'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                if (empty($values['value'])) {
                    return null;
                }

                $roles = is_array($values['value']) ? $values['value'] : [$values['value']];

                $queryBuilder = $filterQuery->getQueryBuilder();


                foreach ($roles as $role) {

                    if ($role === User::ROLE_PROFESSIONAL_USER) {
                        $queryBuilder->innerJoin(ProfessionalUser::class, 'pu', \Doctrine\ORM\Query\Expr\Join::WITH, 'u.id = pu.id');
                    }

                    if ($role === User::ROLE_EDUCATOR_USER) {
                        $queryBuilder->innerJoin(EducatorUser::class, 'eu', \Doctrine\ORM\Query\Expr\Join::WITH, 'u.id = eu.id');
                    }

                    if ($role === User::ROLE_STUDENT_USER) {
                        $queryBuilder->innerJoin(StudentUser::class, 'su', \Doctrine\ORM\Query\Expr\Join::WITH, 'u.id = su.id');
                    }

                    if ($role === User::ROLE_SCHOOL_ADMINISTRATOR_USER) {
                        $queryBuilder->innerJoin(SchoolAdministrator::class, 'sa', \Doctrine\ORM\Query\Expr\Join::WITH, 'u.id = sa.id');
                    }

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
            'choices'      => $userRoleChoices,
            'label'        => 'FILTER BY USER ROLE',
            'placeholder'  => 'FILTER BY USER ROLE',
        ]);


        $builder->get('userRole')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

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
        });
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

        $form->add('company', Filters\EntityFilterType::class, [
            'class'         => Company::class,
            'choice_label'  => 'name',
            'mapped'        => false,
            'expanded'      => false,
            'multiple'      => true,
            'label'         => 'FILTER BY COMPANY',
            'placeholder'   => 'FILTER BY COMPANY',
            'query_builder' => function (\App\Repository\CompanyRepository $companyRepository) {
                return $companyRepository->createQueryBuilder('c')->orderBy('c.name', 'ASC');
            },
            'apply_filter'  => function (QueryInterface $filterQuery, $field, $values) {

                if (empty($values['value'])) {
                    return null;
                }

                if (!$values['value'] instanceof ArrayCollection) {
                    return null;
                }

                if (!$values['value']->count()) {
                    return null;
                }

                $companyIds = $values['value']->map(function (Company $company) {
                    return $company->getId();
                })->toArray();

                $queryBuilder = $filterQuery->getQueryBuilder()
                                            ->innerJoin('pu.company', 'c')
                                            ->andWhere('c.id IN (:companyIds)')
                                            ->setParameter('companyIds', $companyIds);


                $newFilterQuery = new ORMQuery($queryBuilder);

                $expression = $newFilterQuery->getExpr()->eq('1', '1');

                return $newFilterQuery->createCondition($expression);
            },
        ]);

        $form->add('rolesWillingToFulfill', Filters\EntityFilterType::class, [
            'class'         => RolesWillingToFulfill::class,
            'choice_label'  => 'name',
            'expanded'      => false,
            'mapped'        => false,
            'multiple'      => true,
            'placeholder'   => 'FILTER BY ROLES',
            'label'         => 'FILTER BY ROLES',
            'query_builder' => function (
                \App\Repository\RolesWillingToFulfillRepository $rolesWillingToFulfillRepository) {
                return $rolesWillingToFulfillRepository->createQueryBuilder('r')->orderBy('r.name', 'ASC');
            },
            'apply_filter'  => function (QueryInterface $filterQuery, $field, $values) {

                if (empty($values['value'])) {
                    return null;
                }

                if (!$values['value'] instanceof ArrayCollection) {
                    return null;
                }

                if (!$values['value']->count()) {
                    return null;
                }

                $roleIds = $values['value']->map(function (RolesWillingToFulfill $rolesWillingToFulfill) {
                    return $rolesWillingToFulfill->getId();
                })->toArray();

                $queryBuilder = $filterQuery->getQueryBuilder()
                                            ->innerJoin('pu.rolesWillingToFulfill', 'r')
                                            ->andWhere('r.id IN (:roleIds)')
                                            ->setParameter('roleIds', $roleIds);


                $newFilterQuery = new ORMQuery($queryBuilder);

                $expression = $newFilterQuery->getExpr()->eq('1', '1');

                return $newFilterQuery->createCondition($expression);
            },
        ]);

        $builder = $form->getConfig()
                        ->getFormFactory()
                        ->createNamedBuilder('primaryIndustry', Filters\EntityFilterType::class, null, [
                            'class'           => Industry::class,
                            'choice_label'    => 'name',
                            'expanded'        => false,
                            'multiple'        => true,
                            'mapped'          => false,
                            'placeholder'     => 'FILTER BY INDUSTRY',
                            'label'           => 'FILTER BY INDUSTRY',
                            'auto_initialize' => false,
                            'query_builder'   => function (
                                \App\Repository\IndustryRepository $industryRepository) {
                                return $industryRepository->createQueryBuilder('i')->orderBy('i.name', 'ASC');
                            },
                            'apply_filter'    => function (QueryInterface $filterQuery, $field, $values) {

                                if (empty($values['value'])) {
                                    return null;
                                }

                                if (!$values['value'] instanceof ArrayCollection) {
                                    return null;
                                }

                                if (!$values['value']->count()) {
                                    return null;
                                }

                                $primaryIndustryIds = $values['value']->map(function (
                                    Industry $industry) {
                                    return $industry->getId();
                                })->toArray();

                                $queryBuilder = $filterQuery->getQueryBuilder()
                                                            ->innerJoin('pu.primaryIndustry', 'pi')
                                                            ->andWhere('pi.id IN (:primaryIndustryIds)')
                                                            ->setParameter('primaryIndustryIds', $primaryIndustryIds);


                                $newFilterQuery = new ORMQuery($queryBuilder);

                                $expression = $newFilterQuery->getExpr()->eq('1', '1');

                                return $newFilterQuery->createCondition($expression);
                            },
                        ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

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

            $industryIds = array_map(function (Industry $industry) {
                return $industry->getId();
            }, $industries);

            $form->add('secondaryIndustries', Filters\EntityFilterType::class, [
                'class'         => SecondaryIndustry::class,
                'choice_label'  => 'name',
                'expanded'      => false,
                'multiple'      => true,
                'mapped'        => false,
                'placeholder'   => 'FILTER BY CAREER',
                'label'         => 'FILTER BY CAREER',
                'query_builder' => function (
                    \App\Repository\SecondaryIndustryRepository $secondaryIndustryRepository) use ($industryIds) {
                    return $secondaryIndustryRepository->createQueryBuilder('si')
                                                       ->innerJoin('si.primaryIndustry', 'primaryIndustry')
                                                       ->andWhere('primaryIndustry.id IN (:ids)')
                                                       ->setParameter('ids', $industryIds)
                                                       ->orderBy('si.name', 'ASC');
                },
                'apply_filter'  => function (QueryInterface $filterQuery, $field, $values) {

                    if (empty($values['value'])) {
                        return null;
                    }

                    if (!$values['value'] instanceof ArrayCollection) {
                        return null;
                    }

                    if (!$values['value']->count()) {
                        return null;
                    }

                    $secondaryIndustryIds = $values['value']->map(function (
                        SecondaryIndustry $secondaryIndustry) {
                        return $secondaryIndustry->getId();
                    })->toArray();

                    $queryBuilder = $filterQuery->getQueryBuilder()
                                                ->innerJoin('pu.secondaryIndustries', 'si')
                                                ->andWhere('si.id IN (:secondaryIndustryIds)')
                                                ->setParameter('secondaryIndustryIds', $secondaryIndustryIds);


                    $newFilterQuery = new ORMQuery($queryBuilder);

                    $expression = $newFilterQuery->getExpr()->eq('1', '1');

                    return $newFilterQuery->createCondition($expression);
                },
            ]);


        });

        $form->add($builder->getForm());

    }

    public function addEducatorFiltersToForm(FormInterface $form)
    {
        $options   = $form->getConfig()->getOptions();
        $schoolIds = $options['schoolIds'];

        if ($form->has('school')) {
            $form->remove('school');
        }

        if ($form->has('myCourses')) {
            $form->remove('myCourses');
        }

        if ($form->has('primaryIndustry')) {
            $form->remove('primaryIndustry');
        }


        $form->add('school', Filters\EntityFilterType::class, [
            'class'         => School::class,
            'choice_label'  => 'name',
            'expanded'      => false,
            'multiple'      => true,
            'mapped'        => false,
            'placeholder'   => 'FILTER BY SCHOOL',
            'label'         => 'FILTER BY SCHOOL',
            'query_builder' => function (EntityRepository $er) use ($schoolIds) {
                $queryBuilder = $er->createQueryBuilder('s');

                if (!empty($schoolIds)) {
                    $queryBuilder->andWhere('s.id IN (:schoolIds)')->setParameter('schoolIds', $schoolIds);
                }

                $queryBuilder->orderBy('s.name', 'ASC');

                return $queryBuilder;
            },
            'apply_filter'  => function (QueryInterface $filterQuery, $field, $values) {

                if (empty($values['value'])) {
                    return null;
                }

                if (!$values['value'] instanceof ArrayCollection) {
                    return null;
                }

                if (!$values['value']->count()) {
                    return null;
                }

                $schoolIds = $values['value']->map(function (School $school) {
                    return $school->getId();
                })->toArray();

                $queryBuilder = $filterQuery->getQueryBuilder()
                                            ->innerJoin('eu.school', 'school')
                                            ->andWhere('school.id IN (:schoolIds)')
                                            ->setParameter('schoolIds', $schoolIds);


                $newFilterQuery = new ORMQuery($queryBuilder);

                $expression = $newFilterQuery->getExpr()->eq('1', '1');

                return $newFilterQuery->createCondition($expression);
            },
        ]);

        $form->add('myCourses', Filters\EntityFilterType::class, [
            'class'         => Course::class,
            'choice_label'  => 'title',
            'expanded'      => false,
            'multiple'      => true,
            'mapped'        => false,
            'placeholder'   => 'FILTER BY COURSE',
            'label'         => 'FILTER BY COURSE',
            'query_builder' => function (
                EntityRepository $er) {
                return $er->createQueryBuilder('c')->orderBy('c.title', 'ASC');
            },
            'apply_filter'  => function (QueryInterface $filterQuery, $field, $values) {

                if (empty($values['value'])) {
                    return null;
                }

                if (!$values['value'] instanceof ArrayCollection) {
                    return null;
                }

                if (!$values['value']->count()) {
                    return null;
                }

                $courseIds = $values['value']->map(function (Course $course) {
                    return $course->getId();
                })->toArray();

                $queryBuilder = $filterQuery->getQueryBuilder()
                                            ->innerJoin('eu.myCourses', 'course')
                                            ->andWhere('course.id IN (:courseIds)')
                                            ->setParameter('courseIds', $courseIds);


                $newFilterQuery = new ORMQuery($queryBuilder);

                $expression = $newFilterQuery->getExpr()->eq('1', '1');

                return $newFilterQuery->createCondition($expression);
            },
        ]);

        $builder = $form->getConfig()
                        ->getFormFactory()
                        ->createNamedBuilder('primaryIndustry', Filters\EntityFilterType::class, null, [
                            'class'           => Industry::class,
                            'choice_label'    => 'name',
                            'expanded'        => false,
                            'multiple'        => true,
                            'placeholder'     => 'FILTER BY INDUSTRY',
                            'auto_initialize' => false,
                            'label'           => 'FILTER BY INDUSTRY',
                            'query_builder'   => function (
                                EntityRepository $er) {
                                return $er->createQueryBuilder('i')->orderBy('i.name', 'ASC');
                            },
                            'apply_filter'    => function (QueryInterface $filterQuery, $field, $values) {

                                $industries = $values['value'];
                                if ($industries instanceof ArrayCollection) {
                                    $industries = $values['value']->toArray();
                                }

                                if (empty($industries)) {
                                    return null;
                                }

                                $industryIds = array_map(function (Industry $industry) {
                                    return $industry->getId();
                                }, $industries);


                                $queryBuilder = $filterQuery->getQueryBuilder();

                                $queryBuilder->innerJoin('eu.primaryIndustries', 'pi')
                                             ->andWhere('pi.id IN (:ids)')
                                             ->setParameter('ids', $industryIds);

                                $newFilterQuery = new ORMQuery($queryBuilder);

                                $expression = $newFilterQuery->getExpr()->eq('1', '1');

                                return $newFilterQuery->createCondition($expression);
                            },
                        ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

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

            $industryIds = array_map(function (Industry $industry) {
                return $industry->getId();
            }, $industries);

            $form->add('secondaryIndustries', Filters\EntityFilterType::class, [
                'class'         => SecondaryIndustry::class,
                'choice_label'  => 'name',
                'expanded'      => false,
                'multiple'      => true,
                'placeholder'   => 'FILTER BY CAREER',
                'label'         => 'FILTER BY CAREER',
                'query_builder' => function (
                    EntityRepository $er) use ($industryIds) {
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

                    $industryIds = array_map(function (SecondaryIndustry $industry) {
                        return $industry->getId();
                    }, $industries);

                    $queryBuilder = $filterQuery->getQueryBuilder()
                                                ->innerJoin('eu.secondaryIndustries', 'si')
                                                ->andWhere('si.id IN (:secondaryIndustryIds)')
                                                ->setParameter('secondaryIndustryIds', $industryIds);

                    $newFilterQuery = new ORMQuery($queryBuilder);

                    $expression = $newFilterQuery->getExpr()->eq('1', '1');

                    return $newFilterQuery->createCondition($expression);
                },
            ]);


        });

        $form->add($builder->getForm());
    }

    public function addStudentFiltersToForm(FormInterface $form)
    {
        $options   = $form->getConfig()->getOptions();
        $schoolIds = $options['schoolIds'];

        if ($form->has('school')) {
            $form->remove('school');
        }

        if ($form->has('primaryIndustry')) {
            $form->remove('primaryIndustry');
        }


        $form->add('school', Filters\EntityFilterType::class, [
            'class'         => School::class,
            'choice_label'  => 'name',
            'expanded'      => false,
            'multiple'      => true,
            'mapped'        => false,
            'placeholder'   => 'FILTER BY SCHOOL',
            'label'         => 'FILTER BY SCHOOL',
            'query_builder' => function (\App\Repository\SchoolRepository $schoolRepository) use ($schoolIds) {
                $queryBuilder = $schoolRepository->createQueryBuilder('s');

                if (!empty($schoolIds)) {
                    $queryBuilder->andWhere('s.id IN (:schoolIds)')->setParameter('schoolIds', $schoolIds);
                }

                $queryBuilder->orderBy('s.name', 'ASC');

                return $queryBuilder;

            },
            'apply_filter'  => function (QueryInterface $filterQuery, $field, $values) {

                if (empty($values['value'])) {
                    return null;
                }

                if (!$values['value'] instanceof ArrayCollection) {
                    return null;
                }

                if (!$values['value']->count()) {
                    return null;
                }

                $schoolIds = $values['value']->map(function (School $school) {
                    return $school->getId();
                })->toArray();

                $queryBuilder = $filterQuery->getQueryBuilder()
                                            ->innerJoin('su.school', 'school')
                                            ->andWhere('school.id IN (:schoolIds)')
                                            ->setParameter('schoolIds', $schoolIds);


                $newFilterQuery = new ORMQuery($queryBuilder);

                $expression = $newFilterQuery->getExpr()->eq('1', '1');

                return $newFilterQuery->createCondition($expression);
            },
        ]);

        $builder = $form->getConfig()
                        ->getFormFactory()
                        ->createNamedBuilder('primaryIndustry', Filters\EntityFilterType::class, null, [
                            'class'           => Industry::class,
                            'choice_label'    => 'name',
                            'expanded'        => false,
                            'multiple'        => true,
                            'placeholder'     => 'FILTER BY INDUSTRY',
                            'auto_initialize' => false,
                            'label'           => 'FILTER BY INDUSTRY',
                            'query_builder'   => function (
                                EntityRepository $er) {
                                return $er->createQueryBuilder('i')->orderBy('i.name', 'ASC');
                            },
                            'apply_filter'    => function (QueryInterface $filterQuery, $field, $values) {

                                $industries = $values['value'];
                                if ($industries instanceof ArrayCollection) {
                                    $industries = $values['value']->toArray();
                                }

                                if (empty($industries)) {
                                    return null;
                                }

                                $industryIds = array_map(function (Industry $industry) {
                                    return $industry->getId();
                                }, $industries);


                                $queryBuilder = $filterQuery->getQueryBuilder();

                                $queryBuilder->innerJoin('su.secondaryIndustries', 'si')
                                             ->innerJoin('si.primaryIndustry', 'pi')
                                             ->andWhere('pi.id IN (:ids)')
                                             ->setParameter('ids', $industryIds);

                                $newFilterQuery = new ORMQuery($queryBuilder);

                                $expression = $newFilterQuery->getExpr()->eq('1', '1');

                                return $newFilterQuery->createCondition($expression);
                            },
                        ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

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

            $industryIds = array_map(function (Industry $industry) {
                return $industry->getId();
            }, $industries);

            $form->add('secondaryIndustries', Filters\EntityFilterType::class, [
                'class'         => SecondaryIndustry::class,
                'choice_label'  => 'name',
                'expanded'      => false,
                'multiple'      => true,
                'placeholder'   => 'FILTER BY CAREER',
                'label'         => 'FILTER BY CAREER',
                'query_builder' => function (
                    EntityRepository $er) use ($industryIds) {
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

                    $industryIds = array_map(function (SecondaryIndustry $industry) {
                        return $industry->getId();
                    }, $industries);

                    $queryBuilder = $filterQuery->getQueryBuilder();

                    $queryBuilder->andWhere('si.id IN (:ids)')->setParameter('ids', $industryIds);

                    $newFilterQuery = new ORMQuery($queryBuilder);

                    $expression = $newFilterQuery->getExpr()->eq('1', '1');

                    return $newFilterQuery->createCondition($expression);
                },
            ]);


        });

        $form->add($builder->getForm());
    }

    public function addSchoolAdministratorFiltersToForm(FormInterface $form)
    {
        $options   = $form->getConfig()->getOptions();
        $schoolIds = $options['schoolIds'];

        if ($form->has('schools')) {
            $form->remove('schools');
        }

        $form->add('schools', Filters\EntityFilterType::class, [
            'class'         => School::class,
            'choice_label'  => 'name',
            'expanded'      => false,
            'multiple'      => true,
            'mapped'        => false,
            'placeholder'   => 'FILTER BY SCHOOL',
            'label'         => 'FILTER BY SCHOOL',
            'query_builder' => function (\App\Repository\SchoolRepository $schoolRepository) use ($schoolIds) {
                $queryBuilder = $schoolRepository->createQueryBuilder('s');

                if (!empty($schoolIds)) {
                    $queryBuilder->andWhere('s.id IN (:schoolIds)')->setParameter('schoolIds', $schoolIds);
                }

                $queryBuilder->orderBy('s.name', 'ASC');

                return $queryBuilder;
            },
            'apply_filter'  => function (QueryInterface $filterQuery, $field, $values) {

                if (empty($values['value'])) {
                    return null;
                }

                if (!$values['value'] instanceof ArrayCollection) {
                    return null;
                }

                if (!$values['value']->count()) {
                    return null;
                }

                $schoolIds = $values['value']->map(function (School $school) {
                    return $school->getId();
                })->toArray();

                $queryBuilder = $filterQuery->getQueryBuilder()
                                            ->innerJoin('sa.schools', 'schools')
                                            ->andWhere('schools.id IN (:schoolIds)')
                                            ->setParameter('schoolIds', $schoolIds);


                $newFilterQuery = new ORMQuery($queryBuilder);

                $expression = $newFilterQuery->getExpr()->eq('1', '1');

                return $newFilterQuery->createCondition($expression);
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array (
            'csrf_protection'    => false,
            'validation_groups'  => array ('filtering'), // avoid NotBlank() constraint-related message
            'userRole'           => null,
            'requestEntity'      => null,
            'schoolIds'          => [],
            'allow_extra_fields' => true,
        ))->setRequired(['loggedInUser']);
    }
}