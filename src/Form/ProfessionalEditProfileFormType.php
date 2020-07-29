<?php

namespace App\Form;

use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SecondaryIndustry;
use App\Entity\State;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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

class ProfessionalEditProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /*if (!$professionalUser->getPhoto()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Please upload an image',
                'groups'  => ['EDIT']
            ]);
        }*/

        $builder
            ->add('firstName', TextType::class, [
                'block_prefix' => 'wrapped_text',
            ])
            ->add('lastName', TextType::class)
            ->add('email')
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password'
            ])
            ->add('primaryIndustry', EntityType::class, [
                'class' => Industry::class,
                'choice_label' => 'name',
                'placeholder' => 'Select Industry',
            ])
            ->add('schools', EntityType::class, [
                'class' => School::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'uk-checkbox'];
                },
            ])
            ->add('rolesWillingToFulfill', EntityType::class, [
                'class' => RolesWillingToFulfill::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('r')
                        ->where('r.inRoleDropdown = :true')
                        ->setParameter('true', true)
                        ->orderBy('r.name', 'ASC');
                },
                'expanded' => true,
                'multiple' => true,
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'uk-checkbox', 'tooltip' => $choice->getDescription()];
                },
            ])
            ->add('interests', TextareaType::class)
            ->add('street', TextType::class, [])
            ->add('city', TextType::class, [])
            ->add('state', EntityType::class, [
                'class' => State::class,
                'choice_label' => 'name',
                'expanded'  => false,
                'multiple'  => false,
            ])
            ->add('zipcode', TextType::class, [])
            ->add('briefBio', TextareaType::class)
            ->add('linkedinProfile', TextType::class)
            ->add('phone', TextType::class)
            ->add('phoneExt', TextType::class, [
                'attr' => [
                    'placeholder' => '123'
                ]
            ])
            ->add('isEmailHiddenFromProfile', ChoiceType::class, [
                'choices'  => [
                    'Yes' => true,

                    'No' => false,
                ],
            ])
            ->add('isPhoneHiddenFromProfile', ChoiceType::class, [
                'choices'  => [
                    'Yes' => true,
                    'No' => false,
                ],
            ])
            ->add('geoRadius', HiddenType::class, [])
	        ->add('geoZipCode', HiddenType::class, []);


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
                    ->setParameter('primaryIndustry', $industry->getId())
                    ->orderBy('si.name', 'ASC');
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
            'data_class' => ProfessionalUser::class,
            'validation_groups' => function (FormInterface $form) {

                $skipValidation = $form->getConfig()->getOption('skip_validation');

                if($skipValidation) {
                    return [];
                }

                /** @var ProfessionalUser $data */
                $data = $form->getData();
                if(!$data->getPrimaryIndustry()) {
                    return ['EDIT', 'PROFESSIONAL_USER'];
                }

                if($data->getPrimaryIndustry()) {
                    return ['EDIT', 'SECONDARY_INDUSTRY', 'PROFESSIONAL_USER'];
                }

                return ['EDIT', 'PROFESSIONAL_USER'];
            },
        ]);

        $resolver->setRequired([
            'skip_validation'
        ]);
    }

    private function localize_us_number($phone) {
        $numbers_only = preg_replace("/[^\d]/", "", $phone);
        return preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $numbers_only);
    }
}
