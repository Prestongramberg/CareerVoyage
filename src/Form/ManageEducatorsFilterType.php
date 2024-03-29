<?php

namespace App\Form;

use App\Entity\EducatorUser;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\User;
use App\Repository\SchoolRepository;
use App\Repository\StudentUserRepository;
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class ManageEducatorsFilterType extends AbstractType
{

    /**
     * @var StudentUserRepository
     */
    private $studentUserRepository;

    /**
     * @var SchoolRepository
     */
    private $schoolRepository;

    /**
     * @param StudentUserRepository $studentUserRepository
     * @param SchoolRepository      $schoolRepository
     */
    public function __construct(StudentUserRepository $studentUserRepository, SchoolRepository $schoolRepository)
    {
        $this->studentUserRepository = $studentUserRepository;
        $this->schoolRepository      = $schoolRepository;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $schoolIds = $options['schoolIds'];

        $builder->add('firstName', Filters\TextFilterType::class, [
            'condition_pattern' => FilterOperands::STRING_CONTAINS,
        ]);
        $builder->add('lastName', Filters\TextFilterType::class, [
            'condition_pattern' => FilterOperands::STRING_CONTAINS,
        ]);
        $builder->add('email', Filters\TextFilterType::class, [
            'condition_pattern' => FilterOperands::STRING_CONTAINS,
        ]);
        $builder->add('username', Filters\TextFilterType::class, [
            'condition_pattern' => FilterOperands::STRING_CONTAINS,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array (
            'schoolIds'         => [],
            'csrf_protection'   => false,
            'validation_groups' => array ('filtering') // avoid NotBlank() constraint-related message
        ))->setRequired('filter_type');
    }
}