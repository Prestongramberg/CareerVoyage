<?php

namespace App\Form;

use App\Entity\Industry;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class ManageRegistrationsFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('user', UserFilterType::class, array(
            'add_shared' => function (FilterBuilderExecuterInterface $qbe) {
                $closure = function (QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                    $filterBuilder->leftJoin($alias . '.user', $joinAlias);
                };

                $qbe->addOnce($qbe->getAlias().'.user', 'u', $closure);
            }
        ));

        /* $builder->add('title', Filters\TextFilterType::class, [
             'condition_pattern' => FilterOperands::STRING_CONTAINS,
         ]);

         $builder->add('isRecurring', Filters\BooleanFilterType::class, [
             'placeholder' => 'Is Recurring Event',
         ]);*/
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
            'validation_groups' => array('filtering')
            // avoid NotBlank() constraint-related message
        ));
    }
}