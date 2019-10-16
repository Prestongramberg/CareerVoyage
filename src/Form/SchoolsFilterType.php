<?php
/**
 * Created by PhpStorm.
 * User: joshcrawmer
 * Date: 2019-10-16
 * Time: 09:22
 */

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

/**
 * Embed filter type.
 */
class SchoolsFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', Filters\TextFilterType::class);
    }

    public function getBlockPrefix()
    {
        return 'options_filter';
    }
}