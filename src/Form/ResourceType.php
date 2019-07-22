<?php

namespace App\Form;

use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\Video;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder ->add('file', FileType::class, array(
            'label' => false,
        ))->add('title', TextType::class, [])
            ->add('description', TextareaType::class, []);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'  => CompanyResource::class,
            'attr'        => ['novalidate' => 'novalidate'],
        ]);
    }
}