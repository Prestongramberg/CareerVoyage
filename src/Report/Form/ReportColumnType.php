<?php

namespace App\Report\Form;

use App\Entity\ReportColumn;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReportColumnType
 * @package App\Report\Form
 */
class ReportColumnType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'attr' => [
                'style' => 'min-height: 1.5rem;'
            ]
        ]);
        $builder->add('defaultName', HiddenType::class, []);
        $builder->add('field', HiddenType::class, []);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReportColumn::class,
            'allow_extra_fields' => true
        ]);
    }
}