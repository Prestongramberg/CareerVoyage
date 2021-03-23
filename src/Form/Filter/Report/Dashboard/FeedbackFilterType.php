<?php

namespace App\Form\Filter\Report\Dashboard;

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
 * Class FeedbackFilterType
 *
 * @package App\Form\Filter
 */
class FeedbackFilterType extends AbstractType
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
        $builder->add('feedbackProvider', Filters\ChoiceFilterType::class, [
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'Feedback Provider',
                'choices' => [
                    // todo add All option
                    'Student' => 'Student',
                    'Educator' => 'Educator',
                    'Professional' => 'Professional',
                ],
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