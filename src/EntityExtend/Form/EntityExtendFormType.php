<?php

namespace App\EntityExtend\Form;

use App\Entity\BuildingBlock;
use App\Entity\Organization;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

trait EntityExtendFormType
{

    /**
     * @param FormBuilderInterface $builder
     * @param Organization $organization
     */
    public function extend(FormBuilderInterface $builder, Organization $organization) {

        $builder->add('useEntityExtendMapping', CheckboxType::class, []);

        $builder->add('doctrineEntityInheritanceMapping', EntityType::class, [
            'required' => false,
            'placeholder' => '-- Select Entity To Extend --',
            'class' => BuildingBlock::class,
            'choice_label' => 'name',
            'query_builder' => function(EntityRepository $er) use($organization) {
                return $er->createQueryBuilder('b')
                    ->where('b.organization = :organization')
                    ->setParameter('organization', $organization)
                    ->orderBy('b.name', 'ASC');
            },
        ]);

    }

}