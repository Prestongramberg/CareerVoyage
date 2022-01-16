<?php

namespace App\Form;

use App\Entity\Industry;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class UserFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstName', Filters\TextFilterType::class, [
            'condition_pattern' => FilterOperands::STRING_CONTAINS,
        ]);

        $builder->add('lastName', Filters\TextFilterType::class, [
            'condition_pattern' => FilterOperands::STRING_CONTAINS,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'user_filter';
    }

    public function getParent()
    {
        return Filters\SharedableFilterType::class; // this allow us to use the "add_shared" option
    }
}