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
 * Class EducatorFilterType
 *
 * @package App\Form\Filter
 */
class EducatorFilterType extends AbstractType
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
            'nameOrInterest', Filters\TextFilterType::class, [
                                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                                    if (empty($values['value'])) {
                                        return null;
                                    }

                                    $searchTerm = $values['value'];

                                    $queryBuilder = $filterQuery->getQueryBuilder();

                                    $queryBuilder->andWhere('u.firstName LIKE :searchTerm OR u.lastName LIKE :searchTerm OR u.interests LIKE :searchTerm')
                                                 ->setParameter('searchTerm', '%' . $searchTerm . '%');

                                    $newFilterQuery = new ORMQuery($queryBuilder);

                                    $expression = $newFilterQuery->getExpr()->eq('1', '1');

                                    return $newFilterQuery->createCondition($expression);
                                },
                                'mapped'       => false,
                            ]
        );

        $builder->add(
            'school', Filters\EntityFilterType::class, [
                        'class'         => School::class,
                        'choice_label'  => 'name',
                        'expanded'      => false,
                        'multiple'      => false,
                        'placeholder'   => 'FILTER BY SCHOOL',
                        'query_builder' => function (\App\Repository\SchoolRepository $schoolRepository) {
                            return $schoolRepository->createQueryBuilder('s')
                                                    ->orderBy('s.name', 'ASC');
                        },
                    ]
        );

        $builder->add(
            'myCourses', Filters\EntityFilterType::class, [
                           'class'         => Course::class,
                           'choice_label'  => 'title',
                           'expanded'      => false,
                           'multiple'      => false,
                           'placeholder'   => 'FILTER BY COURSE',
                           'query_builder' => function (
                               \App\Repository\CourseRepository $courseRepository
                           ) {
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
                                 'apply_filter'  => function (QueryInterface $filterQuery, $field, $values) {

                                     if (empty($values['value'])) {
                                         return null;
                                     }

                                     /** @var Industry $primaryIndustry */
                                     $primaryIndustry = $values['value'];

                                     $queryBuilder = $filterQuery->getQueryBuilder();

                                     $queryBuilder->leftJoin('u.secondaryIndustries', 'si')
                                                  ->leftJoin('si.primaryIndustry', 'pi')
                                                  ->andWhere('pi.id = :id')
                                                  ->setParameter('id', $primaryIndustry->getId());

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
            $form     = $event->getForm()->getParent();

            if (!$form) {
                return;
            }

            if (!$industry) {
                if ($form->has('secondaryIndustries')) {
                    $form->remove('secondaryIndustries');
                }

                return;
            }

            $this->modifyForm($event->getForm()->getParent(), $industry);
        });


        $builder->add(
            'radius', Filters\ChoiceFilterType::class, [
                        'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {

                            if (empty($values['value'])) {
                                return null;
                            }

                            $request = $this->requestStack->getCurrentRequest();

                            $zipcode = $request->query->get('zipcode', null);
                            $radius  = $values['value'];

                            if ($zipcode && $coordinates = $this->geocoder->geocode($zipcode)) {
                                $lng = $coordinates['lng'];
                                $lat = $coordinates['lat'];
                                list($latN, $latS, $lonE, $lonW) = $this->geocoder->calculateSearchSquare($lat, $lng, $radius);

                                $queryBuilder = $filterQuery->getQueryBuilder();

                                $queryBuilder->andWhere("u.latitude <= :latN AND u.latitude >= :latS AND u.longitude <= :lonE AND u.longitude >= :lonW AND (u.latitude != :lat AND u.longitude != :lng)");

                                // todo make sure to change the lat long entity field types to decimal if you want this to work.
                                // todo see ProfessionalUser Entity
                                $queryBuilder->setParameter('latN', $latN, 'decimal');
                                $queryBuilder->setParameter('latS', $latS, 'decimal');
                                $queryBuilder->setParameter('lonE', $lonE, 'decimal');
                                $queryBuilder->setParameter('lonW', $lonW, 'decimal');
                                $queryBuilder->setParameter('lat', $lat, 'decimal');
                                $queryBuilder->setParameter('lng', $lng, 'decimal');


                                $newFilterQuery = new ORMQuery($queryBuilder);

                                $expression = $newFilterQuery->getExpr()->eq('1', '1');

                                return $newFilterQuery->createCondition($expression);

                            }

                            return $filterQuery->getExpr();
                        },
                        'mapped'       => false,
                        'expanded'     => false,
                        'multiple'     => false,
                        'required'     => false,
                        'choices'      => [
                            'FILTER BY RADIUS' => '',
                            '25 miles'         => '25',
                            '50 miles'         => '50',
                            '70 miles'         => '70',
                            '150 miles'        => '150',
                        ],
                    ]
        );


    }

    private function modifyForm(FormInterface $form, Industry $industry)
    {

        if ($form->has('secondaryIndustries')) {
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