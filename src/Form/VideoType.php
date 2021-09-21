<?php

namespace App\Form;

use App\Validator\Constraints\YoutubeVideoId;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class VideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'constraints' => [new NotBlank(['message' => 'Please enter a title for your video', 'groups' => ['VIDEO']])],
            'empty_data' => ''
        ])->add('videoId', TextType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Please enter a valid Youtube Video ID', 'groups' => ['VIDEO']]),
                new YoutubeVideoId(['groups' => ['VIDEO']]),
            ],
            'empty_data' => ''
        ])->add('tags', TextareaType::class, []);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
            'validation_groups' => ['VIDEO']
        ]);
    }
}