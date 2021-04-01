<?php

namespace App\Form\Filter\Report\Dashboard;

use App\Entity\Feedback;
use App\Repository\FeedbackRepository;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Class FeedbackFilterType
 *
 * @package App\Form\Filter
 */
class FeedbackFilterType extends AbstractType
{
    public const CACHE_KEY = 'filter.report.dashboard.feedback_filters';

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var FeedbackRepository
     */
    private $feedbackRepository;

    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * ProfessionalFilterType constructor.
     *
     * @param RequestStack       $requestStack
     * @param FeedbackRepository $feedbackRepository
     * @param string             $cacheDirectory
     */
    public function __construct(RequestStack $requestStack, FeedbackRepository $feedbackRepository,
                                string $cacheDirectory
    ) {
        $this->requestStack       = $requestStack;
        $this->feedbackRepository = $feedbackRepository;
        $this->cacheDirectory     = $cacheDirectory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cache = new FilesystemAdapter('pintex', 3600, $this->cacheDirectory);

        /*$filters = $cache->get(self::CACHE_KEY, function (ItemInterface $item) {

            return $this->feedbackRepository->getFilters();
        });*/

        $filters = $this->feedbackRepository->getFilters();

        $builder->add('feedbackProvider', Filters\ChoiceFilterType::class, [
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'All',
                'choices' => $filters['feedback_provider'],
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                    if (empty($values['value'])) {
                        return null;
                    }

                    $feedbackProvider = $values['value'];

                    $queryBuilder = $filterQuery->getQueryBuilder();

                    $queryBuilder->andWhere('f.feedbackProvider = :feedbackProvider')
                                 ->setParameter('feedbackProvider', $feedbackProvider);

                    $newFilterQuery = new ORMQuery($queryBuilder);

                    $expression = $newFilterQuery->getExpr()->eq('1', '1');

                    return $newFilterQuery->createCondition($expression);
                },
            ]
        );

        $builder->add('experienceProvider', Filters\ChoiceFilterType::class, [
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'All',
                'choices' => $filters['experience_provider'],
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                    if (empty($values['value'])) {
                        return null;
                    }

                    $experienceProvider = $values['value'];

                    $queryBuilder = $filterQuery->getQueryBuilder();

                    $queryBuilder->andWhere('f.experienceProvider = :experienceProvider')
                                 ->setParameter('experienceProvider', $experienceProvider);

                    $newFilterQuery = new ORMQuery($queryBuilder);

                    $expression = $newFilterQuery->getExpr()->eq('1', '1');

                    return $newFilterQuery->createCondition($expression);
                },
            ]
        );

        $builder->add('experienceTypeName', Filters\ChoiceFilterType::class, [
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'All',
                'choices' => $filters['experience_type_name'],
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                    if (empty($values['value'])) {
                        return null;
                    }

                    $experienceTypeName = $values['value'];

                    $queryBuilder = $filterQuery->getQueryBuilder();

                    $queryBuilder->andWhere('f.experienceTypeName = :experienceTypeName')
                                 ->setParameter('experienceTypeName', $experienceTypeName);

                    $newFilterQuery = new ORMQuery($queryBuilder);

                    $expression = $newFilterQuery->getExpr()->eq('1', '1');

                    return $newFilterQuery->createCondition($expression);
                },
            ]
        );

        $builder->add('regionNames', Filters\ChoiceFilterType::class, [
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'All',
                'choices' => $filters['region_name'],
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                    if (empty($values['value'])) {
                        return null;
                    }

                    $regionName = $values['value'];

                    $queryBuilder = $filterQuery->getQueryBuilder();

                    $queryBuilder->andWhere('f.regionNames LIKE :regionName')
                                 ->setParameter('regionName', '%' . $regionName . '%');

                    $newFilterQuery = new ORMQuery($queryBuilder);

                    $expression = $newFilterQuery->getExpr()->eq('1', '1');

                    return $newFilterQuery->createCondition($expression);
                },
            ]
        );

        $builder->add('schoolNames', Filters\ChoiceFilterType::class, [
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'All',
                'choices' => $filters['school_name'],
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                    if (empty($values['value'])) {
                        return null;
                    }

                    $schoolName = $values['value'];

                    $queryBuilder = $filterQuery->getQueryBuilder();

                    $queryBuilder->andWhere('f.schoolNames LIKE :schoolName')
                                 ->setParameter('schoolName', '%' . $schoolName . '%');

                    $newFilterQuery = new ORMQuery($queryBuilder);

                    $expression = $newFilterQuery->getExpr()->eq('1', '1');

                    return $newFilterQuery->createCondition($expression);
                },
            ]
        );

        $builder->add('companyNames', Filters\ChoiceFilterType::class, [
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'All',
                'choices' => $filters['company_name'],
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                    if (empty($values['value'])) {
                        return null;
                    }

                    $companyName = $values['value'];

                    $queryBuilder = $filterQuery->getQueryBuilder();

                    $queryBuilder->andWhere('f.companyNames LIKE :companyName')
                                 ->setParameter('companyName', '%' . $companyName . '%');

                    $newFilterQuery = new ORMQuery($queryBuilder);

                    $expression = $newFilterQuery->getExpr()->eq('1', '1');

                    return $newFilterQuery->createCondition($expression);
                },
            ]
        );

        $builder->add('eventStartDate', Filters\DateRangeFilterType::class, [
                'left_date_options' => [
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    // prevents rendering it as type="date", to avoid HTML5 date pickers
                    'html5' => false,
                ],
                'right_date_options' => [
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    // prevents rendering it as type="date", to avoid HTML5 date pickers
                    'html5' => false,
                ],
            ]
        );


        /* REGION NAME FILTER CHANGE */
        $builder->get('regionNames')->addEventListener(
            FormEvents::POST_SUBMIT, function (FormEvent $event) {


            $regionName = $event->getForm()->getData();
            $form       = $event->getForm()->getParent();

            if (!$regionName) {
                return;
            }

            if (!$form) {
                return;
            }

            $feedbacks = $this->feedbackRepository->createQueryBuilder('f')
                                                  ->andWhere(sprintf("JSON_CONTAINS(f.regionNames, '\"%s\"', '$') = TRUE", $regionName))
                                                  ->getQuery()
                                                  ->getResult();

            $schoolChoices             = [];
            $companyChoices            = [];
            $feedbackProviderChoices   = [];
            $experienceProviderChoices = [];
            $experienceTypeNameChoices = [];
            /** @var Feedback $feedback */
            foreach ($feedbacks as $feedback) {
                foreach ($feedback->getSchoolNames() as $schoolName) {
                    $schoolChoices[$schoolName] = $schoolName;
                }

                foreach ($feedback->getCompanyNames() as $companyName) {
                    $companyChoices[$companyName] = $companyName;
                }

                $feedbackProviderChoices[$feedback->getFeedbackProvider()]     = $feedback->getFeedbackProvider();
                $experienceProviderChoices[$feedback->getExperienceProvider()] = $feedback->getExperienceProvider();
                $experienceTypeNameChoices[$feedback->getExperienceTypeName()] = $feedback->getExperienceTypeName();
            }

            $form->add('schoolNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $schoolChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $schoolName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.schoolNames LIKE :schoolName')
                                     ->setParameter('schoolName', '%' . $schoolName . '%');

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('companyNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $companyChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $companyName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.companyNames LIKE :companyName')
                                     ->setParameter('companyName', '%' . $companyName . '%');

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('feedbackProvider', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $feedbackProviderChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $feedbackProvider = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.feedbackProvider = :feedbackProvider')
                                     ->setParameter('feedbackProvider', $feedbackProvider);

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('experienceProvider', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $experienceProviderChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $experienceProvider = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.experienceProvider = :experienceProvider')
                                     ->setParameter('experienceProvider', $experienceProvider);

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('experienceTypeName', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $experienceTypeNameChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $experienceTypeName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.experienceTypeName = :experienceTypeName')
                                     ->setParameter('experienceTypeName', $experienceTypeName);

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

        });


        /* SCHOOL NAME FILTER CHANGE */
        $builder->get('schoolNames')->addEventListener(
            FormEvents::POST_SUBMIT, function (FormEvent $event) {


            $schoolName = $event->getForm()->getData();
            $form       = $event->getForm()->getParent();

            if (!$schoolName) {
                return;
            }

            if (!$form) {
                return;
            }

            $feedbacks = $this->feedbackRepository->createQueryBuilder('f')
                                                  ->andWhere(sprintf("JSON_CONTAINS(f.schoolNames, '\"%s\"', '$') = TRUE", $schoolName))
                                                  ->getQuery()
                                                  ->getResult();

            $regionChoices             = [];
            $companyChoices            = [];
            $feedbackProviderChoices   = [];
            $experienceProviderChoices = [];
            $experienceTypeNameChoices = [];
            /** @var Feedback $feedback */
            foreach ($feedbacks as $feedback) {

                foreach ($feedback->getRegionNames() as $regionName) {
                    $regionChoices[$regionName] = $regionName;
                }

                foreach ($feedback->getCompanyNames() as $companyName) {
                    $companyChoices[$companyName] = $companyName;
                }

                $feedbackProviderChoices[$feedback->getFeedbackProvider()]     = $feedback->getFeedbackProvider();
                $experienceProviderChoices[$feedback->getExperienceProvider()] = $feedback->getExperienceProvider();
                $experienceTypeNameChoices[$feedback->getExperienceTypeName()] = $feedback->getExperienceTypeName();
            }

            $form->add('regionNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $regionChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $regionName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.regionNames LIKE :regionName')
                                     ->setParameter('regionName', '%' . $regionName . '%');

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('companyNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $companyChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $companyName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.companyNames LIKE :companyName')
                                     ->setParameter('companyName', '%' . $companyName . '%');

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('feedbackProvider', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $feedbackProviderChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $feedbackProvider = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.feedbackProvider = :feedbackProvider')
                                     ->setParameter('feedbackProvider', $feedbackProvider);

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('experienceProvider', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $experienceProviderChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $experienceProvider = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.experienceProvider = :experienceProvider')
                                     ->setParameter('experienceProvider', $experienceProvider);

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('experienceTypeName', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $experienceTypeNameChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $experienceTypeName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.experienceTypeName = :experienceTypeName')
                                     ->setParameter('experienceTypeName', $experienceTypeName);

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );
        });

        /* COMPANY NAME FILTER CHANGE */
        $builder->get('companyNames')->addEventListener(
            FormEvents::POST_SUBMIT, function (FormEvent $event) {


            $companyName = $event->getForm()->getData();
            $form        = $event->getForm()->getParent();

            if (!$companyName) {
                return;
            }

            if (!$form) {
                return;
            }

            $feedbacks = $this->feedbackRepository->createQueryBuilder('f')
                                                  ->andWhere(sprintf("JSON_CONTAINS(f.companyNames, '\"%s\"', '$') = TRUE", $companyName))
                                                  ->getQuery()
                                                  ->getResult();

            $regionChoices             = [];
            $schoolChoices             = [];
            $feedbackProviderChoices   = [];
            $experienceProviderChoices = [];
            $experienceTypeNameChoices = [];
            /** @var Feedback $feedback */
            foreach ($feedbacks as $feedback) {

                foreach ($feedback->getRegionNames() as $regionName) {
                    $regionChoices[$regionName] = $regionName;
                }

                foreach ($feedback->getSchoolNames() as $schoolName) {
                    $schoolChoices[$schoolName] = $schoolName;
                }

                $feedbackProviderChoices[$feedback->getFeedbackProvider()]     = $feedback->getFeedbackProvider();
                $experienceProviderChoices[$feedback->getExperienceProvider()] = $feedback->getExperienceProvider();
                $experienceTypeNameChoices[$feedback->getExperienceTypeName()] = $feedback->getExperienceTypeName();
            }

            $form->add('regionNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $regionChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $regionName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.regionNames LIKE :regionName')
                                     ->setParameter('regionName', '%' . $regionName . '%');

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('schoolNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $schoolChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $schoolName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.schoolNames LIKE :schoolName')
                                     ->setParameter('schoolName', '%' . $schoolName . '%');

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('feedbackProvider', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $feedbackProviderChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $feedbackProvider = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.feedbackProvider = :feedbackProvider')
                                     ->setParameter('feedbackProvider', $feedbackProvider);

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('experienceProvider', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $experienceProviderChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $experienceProvider = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.experienceProvider = :experienceProvider')
                                     ->setParameter('experienceProvider', $experienceProvider);

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('experienceTypeName', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $experienceTypeNameChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $experienceTypeName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.experienceTypeName = :experienceTypeName')
                                     ->setParameter('experienceTypeName', $experienceTypeName);

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );
        });


        /* FEEDBACK PROVIDER FILTER CHANGE */
        $builder->get('feedbackProvider')->addEventListener(
            FormEvents::POST_SUBMIT, function (FormEvent $event) {


            $feedbackProvider = $event->getForm()->getData();
            $form             = $event->getForm()->getParent();

            if (!$feedbackProvider) {
                return;
            }

            if (!$form) {
                return;
            }

            $feedbacks = $this->feedbackRepository->createQueryBuilder('f')
                                                  ->andWhere('f.feedbackProvider = :feedbackProvider')
                                                  ->setParameter('feedbackProvider', $feedbackProvider)
                                                  ->getQuery()
                                                  ->getResult();

            $regionChoices             = [];
            $schoolChoices             = [];
            $companyChoices            = [];
            $experienceProviderChoices = [];
            $experienceTypeNameChoices = [];
            /** @var Feedback $feedback */
            foreach ($feedbacks as $feedback) {

                foreach ($feedback->getRegionNames() as $regionName) {
                    $regionChoices[$regionName] = $regionName;
                }

                foreach ($feedback->getSchoolNames() as $schoolName) {
                    $schoolChoices[$schoolName] = $schoolName;
                }

                foreach ($feedback->getCompanyNames() as $companyName) {
                    $companyChoices[$companyName] = $companyName;
                }

                $experienceProviderChoices[$feedback->getExperienceProvider()] = $feedback->getExperienceProvider();
                $experienceTypeNameChoices[$feedback->getExperienceTypeName()] = $feedback->getExperienceTypeName();
            }

            $form->add('regionNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $regionChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $regionName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.regionNames LIKE :regionName')
                                     ->setParameter('regionName', '%' . $regionName . '%');

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('schoolNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $schoolChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $schoolName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.schoolNames LIKE :schoolName')
                                     ->setParameter('schoolName', '%' . $schoolName . '%');

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('companyNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $companyChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $companyName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.companyNames LIKE :companyName')
                                     ->setParameter('companyName', '%' . $companyName . '%');

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('experienceProvider', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $experienceProviderChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $experienceProvider = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.experienceProvider = :experienceProvider')
                                     ->setParameter('experienceProvider', $experienceProvider);

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('experienceTypeName', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $experienceTypeNameChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $experienceTypeName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.experienceTypeName = :experienceTypeName')
                                     ->setParameter('experienceTypeName', $experienceTypeName);

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );
        });

        /* EXPERIENCE PROVIDER FILTER CHANGE */
        $builder->get('experienceProvider')->addEventListener(
            FormEvents::POST_SUBMIT, function (FormEvent $event) {


            $experienceProvider = $event->getForm()->getData();
            $form               = $event->getForm()->getParent();

            if (!$experienceProvider) {
                return;
            }

            if (!$form) {
                return;
            }

            $feedbacks = $this->feedbackRepository->createQueryBuilder('f')
                                                  ->andWhere('f.experienceProvider = :experienceProvider')
                                                  ->setParameter('experienceProvider', $experienceProvider)
                                                  ->getQuery()
                                                  ->getResult();

            $regionChoices             = [];
            $schoolChoices             = [];
            $companyChoices            = [];
            $feedbackProviderChoices   = [];
            $experienceTypeNameChoices = [];
            /** @var Feedback $feedback */
            foreach ($feedbacks as $feedback) {

                foreach ($feedback->getRegionNames() as $regionName) {
                    $regionChoices[$regionName] = $regionName;
                }

                foreach ($feedback->getSchoolNames() as $schoolName) {
                    $schoolChoices[$schoolName] = $schoolName;
                }

                foreach ($feedback->getCompanyNames() as $companyName) {
                    $companyChoices[$companyName] = $companyName;
                }

                $feedbackProviderChoices[$feedback->getFeedbackProvider()]     = $feedback->getFeedbackProvider();
                $experienceTypeNameChoices[$feedback->getExperienceTypeName()] = $feedback->getExperienceTypeName();
            }

            $form->add('regionNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $regionChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $regionName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.regionNames LIKE :regionName')
                                     ->setParameter('regionName', '%' . $regionName . '%');

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('schoolNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $schoolChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $schoolName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.schoolNames LIKE :schoolName')
                                     ->setParameter('schoolName', '%' . $schoolName . '%');

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('companyNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $companyChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $companyName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.companyNames LIKE :companyName')
                                     ->setParameter('companyName', '%' . $companyName . '%');

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('feedbackProvider', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $feedbackProviderChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $feedbackProvider = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.feedbackProvider = :feedbackProvider')
                                     ->setParameter('feedbackProvider', $feedbackProvider);

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('experienceTypeName', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $experienceTypeNameChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $experienceTypeName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.experienceTypeName = :experienceTypeName')
                                     ->setParameter('experienceTypeName', $experienceTypeName);

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );
        });


        /* EXPERIENCE PROVIDER FILTER CHANGE */
        $builder->get('experienceTypeName')->addEventListener(
            FormEvents::POST_SUBMIT, function (FormEvent $event) {


            $experienceTypeName = $event->getForm()->getData();
            $form               = $event->getForm()->getParent();

            if (!$experienceTypeName) {
                return;
            }

            if (!$form) {
                return;
            }

            $feedbacks = $this->feedbackRepository->createQueryBuilder('f')
                                                  ->andWhere('f.experienceTypeName = :experienceTypeName')
                                                  ->setParameter('experienceTypeName', $experienceTypeName)
                                                  ->getQuery()
                                                  ->getResult();

            $regionChoices             = [];
            $schoolChoices             = [];
            $companyChoices            = [];
            $feedbackProviderChoices   = [];
            $experienceProviderChoices = [];
            /** @var Feedback $feedback */
            foreach ($feedbacks as $feedback) {

                foreach ($feedback->getRegionNames() as $regionName) {
                    $regionChoices[$regionName] = $regionName;
                }

                foreach ($feedback->getSchoolNames() as $schoolName) {
                    $schoolChoices[$schoolName] = $schoolName;
                }

                foreach ($feedback->getCompanyNames() as $companyName) {
                    $companyChoices[$companyName] = $companyName;
                }

                $feedbackProviderChoices[$feedback->getFeedbackProvider()]     = $feedback->getFeedbackProvider();
                $experienceProviderChoices[$feedback->getExperienceProvider()] = $feedback->getExperienceProvider();
            }

            $form->add('regionNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $regionChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $regionName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.regionNames LIKE :regionName')
                                     ->setParameter('regionName', '%' . $regionName . '%');

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('schoolNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $schoolChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $schoolName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.schoolNames LIKE :schoolName')
                                     ->setParameter('schoolName', '%' . $schoolName . '%');

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('companyNames', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $companyChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $companyName = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.companyNames LIKE :companyName')
                                     ->setParameter('companyName', '%' . $companyName . '%');

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('feedbackProvider', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $feedbackProviderChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $feedbackProvider = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.feedbackProvider = :feedbackProvider')
                                     ->setParameter('feedbackProvider', $feedbackProvider);

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );

            $form->add('experienceProvider', Filters\ChoiceFilterType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => 'All',
                    'choices' => $experienceProviderChoices,
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                        if (empty($values['value'])) {
                            return null;
                        }

                        $experienceProvider = $values['value'];

                        $queryBuilder = $filterQuery->getQueryBuilder();

                        $queryBuilder->andWhere('f.experienceProvider = :experienceProvider')
                                     ->setParameter('experienceProvider', $experienceProvider);

                        $newFilterQuery = new ORMQuery($queryBuilder);

                        $expression = $newFilterQuery->getExpr()->eq('1', '1');

                        return $newFilterQuery->createCondition($expression);
                    },
                ]
            );
        });

    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array (
                'csrf_protection' => false,
                'validation_groups' => array ('filtering') // avoid NotBlank() constraint-related message
            )
        );
    }
}