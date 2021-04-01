<?php

namespace App\Form\Filter\Report\Dashboard;

use App\Repository\FeedbackRepository;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Class RegionFilterType
 *
 * @package App\Form\Filter
 */
class RegionFilterType extends AbstractType
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