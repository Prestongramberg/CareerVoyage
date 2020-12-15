<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\Region;
use App\Entity\School;
use App\Entity\SecondaryIndustry;
use App\Entity\State;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
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
            ->add('street', TextType::class, [])
            ->add('city', TextType::class, [])
            ->add('state', EntityType::class, [
                'class' => State::class,
                'choice_label' => 'name',
                'expanded'  => false,
                'multiple'  => false,
            ])
            ->add('zipcode', TextType::class, [])
            ->add('website', TextType::class, [
                'attr' => [
                    'placeholder' => 'http://example.org'
                ]
            ])
            ->add('phone', TextType::class, [
                'attr' => [
                    'placeholder' => 'xxx-xxx-xxxx'
                ]
            ])
            ->add('phoneExt', TextType::class, [
                'attr' => [
                    'placeholder' => '123'
                ]
            ])
            ->add('emailAddress', TextType::class)
            ->add('primaryIndustry', EntityType::class, [
                'class' => Industry::class,
                'choice_label' => 'name',
                'placeholder' => 'Select Industry',
            ])
            ->add('regions', EntityType::class, [
                'class' => Region::class,
                'expanded' => true,
                'multiple' => true,
                'choice_label' => 'friendlyName',
            ])
            ->add('schools', EntityType::class, [
                'class' => School::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'uk-checkbox'];
                }
            ])
            ->add('companyLinkedinPage', TextType::class, [])
            ->add('companyFacebookPage', TextType::class, [])
            ->add('companyInstagramPage', TextType::class, [])
            ->add('companyTwitterPage', TextType::class, [])

            ->add('shortDescription', TextareaType::class, [])
            ->add('description', TextareaType::class)
            ->add('owner', EntityType::class, [
                'class' => ProfessionalUser::class,
                'query_builder' => function (EntityRepository $er) use ($company) {
                    return $er->createQueryBuilder('p')
                        ->where('p.company = :company')
                        ->setParameter('company', $company->getId());
                },
                'choice_label' => 'email'
            ])
	        ->add('geoRadius', HiddenType::class, [])
	        ->add('geoZipCode', HiddenType::class, []);;

        $builder->get('phone')->addModelTransformer(new CallbackTransformer(
            function ($phone) {
                return str_replace('-', '', $phone);
            },
            function ($phone) {
                return $this->localize_us_number($phone);
            }
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $data = $event->getData();
            if(!$data->getPrimaryIndustry()) {
                return;
            }
            $this->modifyForm($event->getForm(), $data->getPrimaryIndustry());
        });

        $builder->get('primaryIndustry')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Industry $industry */
            $industry = $event->getForm()->getData();

            if(!$industry) {
                return;
            }

            $this->modifyForm($event->getForm()->getParent(), $industry);
        });
    }

    private function modifyForm(FormInterface $form, Industry $industry) {

        $form->add('secondaryIndustries', EntityType::class, [
            'class' => SecondaryIndustry::class,
            'query_builder' => function (EntityRepository $er) use ($industry) {
                return $er->createQueryBuilder('si')
                    ->where('si.primaryIndustry = :primaryIndustry')
                    ->setParameter('primaryIndustry', $industry->getId());
            },
            'choice_label' => 'name',
            'expanded' => true,
            'multiple' => true,
            'choice_attr' => function($choice, $key, $value) {
                return ['class' => 'uk-checkbox'];
            }
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
            'validation_groups' => function (FormInterface $form) {

                // $skipValidation = $form->getConfig()->getOption('skip_validation');

                // if($skipValidation) {
                //     return [];
                // }

                /** @var Company $data */
                $data = $form->getData();
                if(!$data->getPrimaryIndustry()) {
                    return ['EDIT'];
                }

                if($data->getPrimaryIndustry()) {
                    return ['EDIT', 'SECONDARY_INDUSTRY'];
                }

                return ['EDIT'];
            },
        ]);

        $resolver->setRequired(['company', 'skip_validation']);

    }

    /**
     * @param Company $company
     * @return array
     */
    private function thumbnailImageConstraints($company) {

        $imageConstraints = [];

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

        $imageConstraints = [];

        if (!$company->getFeaturedImage()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Please upload a featured image',
                'groups'  => ['EDIT']
            ]);
        }

        return $imageConstraints;
    }

    private function localize_us_number($phone) {
        $numbers_only = preg_replace("/[^\d]/", "", $phone);
        return preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $numbers_only);
    }
}
