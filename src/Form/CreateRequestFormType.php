<?php

namespace App\Form;

use App\Entity\Industry;
use App\Entity\Request;
use App\Entity\RolesWillingToFulfill;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('summary', TextType::class)
            ->add('description', TextareaType::class)
            ->add('volunteerRoles', EntityType::class, [
                'class' => RolesWillingToFulfill::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('r')
                              ->where('r.inRoleDropdown = :true')
                              ->setParameter('true', true)
                              ->orderBy('r.name', 'ASC');
                },
                'expanded' => false,
                'multiple' => true,
                'choice_attr' => function ($choice, $key, $value) {
                    return ['class' => 'uk-checkbox', 'title' => $choice->getDescription()];
                },
            ])
            ->add('primaryIndustries', EntityType::class, [
                'class' => Industry::class,
                'choice_label' => 'name',
                'placeholder' => 'Select Industry',
                'expanded' => false,
                'multiple' => true,
            ])
            ->add('opportunityType', ChoiceType::class, [
                'choices' => Request::$opportunityTypes,
                'expanded' => true,
                'multiple' => false,
            ]);

        $builder->add('postAndReview', SubmitType::class, [
            'label' => 'Post and review this request',
            'attr' => [
                'class' => 'uk-button uk-button-primary',
            ],
        ]);


        $builder->add('saveAndPreview', SubmitType::class, [
            'label' => 'Save draft and preview',
            'attr' => [
                'class' => 'uk-button uk-button-primary',
            ],
        ]);

        $builder->add('delete', SubmitType::class, [
            'label' => 'Delete this request',
            'attr' => [
                'class' => 'uk-button uk-button-primary',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Request::class,
            'validation_groups' => ['CREATE_REQUEST']
        ]);

        $resolver->setRequired([
            'skip_validation',
        ]);
    }
}
