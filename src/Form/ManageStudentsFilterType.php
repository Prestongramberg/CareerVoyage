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
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

class ManageStudentsFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var School $school */
        $school = $options['school'];

        $builder->add('firstName', Filters\TextFilterType::class);
        $builder->add('lastName', Filters\TextFilterType::class);

        $builder->add('educatorUsers', Filters\EntityFilterType::class, array(
            'class' => EducatorUser::class,
            'query_builder' => function (EntityRepository $er) use($school) {
                return $er->createQueryBuilder('eu')
                    ->andWhere('eu.school = :school')
                    ->setParameter('school', $school);
            },
            'choice_label' => 'fullName',
            'placeholder' => '-- Filter By Supervising Educator --',
            'expanded' => false,
            'multiple' => false,
            'required' => false
        ));
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
        ))->setRequired('filter_type')
            ->setRequired('school');
    }
}