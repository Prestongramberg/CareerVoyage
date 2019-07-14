<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;

class EditCompanyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Company $company */
        $company = $options['company'];

        $builder
            ->add('name', TextType::class, [])
            ->add('website', TextType::class, [])
            ->add('phone', TextType::class)
            ->add('emailAddress', TextType::class)
            ->add('primaryIndustry', EntityType::class, [
                'class' => Industry::class,
                'choice_label' => 'name',
            ])



            ->add('shortDescription', TextType::class, [])
            ->add('description', TextareaType::class)
            ->add('thumbnailImage', FileType::class, [
                'label' => 'Thumbnail image',
                'constraints' => $this->thumbnailImageConstraints($company),

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload files
                // everytime you edit the entity
                'required' => false
            ])
            ->add('featuredImage', FileType::class, [
                'label' => 'Featured image',
                'constraints' => $this->featuredImageConstraints($company),

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload files
                // everytime you edit the entity
                'required' => false
            ])
            ->add('photos', FileType::class, array(
                        'multiple' => true,
                        'label' => false,
                        'mapped' => false
            ));



            /*->add('photos', CollectionType::class, array(
                'entry_type' => ImageType::class,
                'allow_add' => true,
                'error_bubbling' => false,
                'prototype' => true,
                'prototype_name' => '__prototype_one__',
                'label' => false,
                'mapped' => false
            ));*/


        /*
            ->add('primaryContact', TextType::class)
            ->add('companyLinkedinPage', TextType::class)
            ->add('phone', TextType::class)
            ->add('logo', FileType::class, [
                'label' => 'Company logo',
                'constraints' => $this->logoImageConstraints($company),

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload files
                // everytime you edit the entity
                'required' => false
            ])
            ->add('heroImage', FileType::class, [
                'label' => 'Company hero image',
                'constraints' => $this->heroImageConstraints($company),

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload files
                // everytime you edit the entity
                'required' => false
            ]);*/
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
            'validation_groups' => ['EDIT'],
        ]);

        $resolver->setRequired('company');

    }

    /**
     * @param Company $company
     * @return array
     */
    private function thumbnailImageConstraints($company) {

        $imageConstraints = [
            new Image([
                'maxSize' => '5M',
                'groups'  => ['EDIT']
            ])
        ];

        if (!$company->getThumbnailImage()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Please upload a thumbnail image',
                'groups'  => ['EDIT']
            ]);
        }

        return $imageConstraints;
    }


    /**
     * @param Company $company
     * @return array
     */
    private function featuredImageConstraints($company) {

        $imageConstraints = [
            new Image([
                'maxSize' => '5M',
                'groups'  => ['EDIT']
            ])
        ];

        if (!$company->getFeaturedImage()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Please upload a featured image',
                'groups'  => ['EDIT']
            ]);
        }

        return $imageConstraints;
    }
}
