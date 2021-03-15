<?php

namespace App\Form\Filter;

use App\Entity\Company;
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
 * Class ProfessionalVideoFilterType
 *
 * @package App\Form\Filter
 */
class ProfessionalVideoFilterType extends AbstractType
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Geocoder
     */
    private $geocoder;

    /**
     * ProfessionalFilterType constructor.
     *
     * @param RequestStack $requestStack
     * @param Geocoder     $geocoder
     */
    public function __construct(RequestStack $requestStack, Geocoder $geocoder)
    {
        $this->requestStack = $requestStack;
        $this->geocoder     = $geocoder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add(
            'keywordOrProfession', Filters\TextFilterType::class, [
                                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                                    if (empty($values['value'])) {
                                        return null;
                                    }

                                    $searchTerm = $values['value'];

                                    $queryBuilder = $filterQuery->getQueryBuilder();

                                    $queryBuilder->andWhere('pv.name LIKE :searchTerm OR pv.tags LIKE :searchTerm OR pi.name LIKE :searchTerm OR si.name LIKE :searchTerm')
                                                 ->setParameter('searchTerm', '%' . $searchTerm . '%');

                                    $newFilterQuery = new ORMQuery($queryBuilder);

                                    $expression = $newFilterQuery->getExpr()->eq('1', '1');

                                    return $newFilterQuery->createCondition($expression);
                                },
                                'mapped'       => false,
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
                                 'apply_filter'  => function (QueryInterface $filterQuery, $field, $values) {

                                     if (empty($values['value'])) {
                                         return null;
                                     }

                                     /** @var Industry $primaryIndustry */
                                     $primaryIndustry = $values['value'];

                                     $queryBuilder = $filterQuery->getQueryBuilder();

                                     $queryBuilder->andWhere('p.primaryIndustry = :primaryIndustry')
                                                  ->setParameter('primaryIndustry', $primaryIndustry);

                                     $newFilterQuery = new ORMQuery($queryBuilder);

                                     $expression = $newFilterQuery->getExpr()->eq('1', '1');

                                     return $newFilterQuery->createCondition($expression);
                                 },
                             ]
        );


        $builder->get('primaryIndustry')->addEventListener(
            FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Industry $industry */
            $industry = $event->getForm()->getData();
            $form = $event->getForm()->getParent();

            if(!$form) {
                return;
            }

            if (!$industry) {
                if($form->has('secondaryIndustries')) {
                    $form->remove('secondaryIndustries');
                }

                return;
            }

            $this->modifyForm($event->getForm()->getParent(), $industry);
        }
        );

    }

    private function modifyForm(FormInterface $form, Industry $industry)
    {

        if($form->has('secondaryIndustries')) {
            $form->remove('secondaryIndustries');
        }

        $form->add(
            'secondaryIndustries', Filters\EntityFilterType::class, [
                                     'class'         => SecondaryIndustry::class,
                                     'choice_label'  => 'name',
                                     'expanded'      => false,
                                     'multiple'      => false,
                                     'placeholder'   => 'FILTER BY CAREER',
                                     'query_builder' => function (
                                         \App\Repository\SecondaryIndustryRepository $secondaryIndustryRepository
                                     ) use ($industry) {
                                         return $secondaryIndustryRepository->createQueryBuilder('si')
                                                                            ->where('si.primaryIndustry = :primaryIndustry')
                                                                            ->setParameter('primaryIndustry', $industry->getId())
                                                                            ->orderBy('si.name', 'ASC');
                                     },
                                     'apply_filter'  => function (QueryInterface $filterQuery, $field, $values) {

                                         if (empty($values['value'])) {
                                             return null;
                                         }

                                         /** @var SecondaryIndustry $secondaryIndustry */
                                         $secondaryIndustry = $values['value'];

                                         $queryBuilder = $filterQuery->getQueryBuilder();

                                         $queryBuilder->andWhere('si.id = :secondaryIndustry')
                                                      ->setParameter('secondaryIndustry', $secondaryIndustry);

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
                'csrf_protection'   => false,
                'validation_groups' => array ('filtering') // avoid NotBlank() constraint-related message
            )
        );
    }
}