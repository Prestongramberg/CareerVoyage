<?php

namespace App\Form;

use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\Region;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SecondaryIndustry;
use App\Entity\State;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
use App\Service\NotificationPreferencesManager;

class ProfessionalEditProfileFormType extends AbstractType
{
    /**
     * @var NotificationPreferencesManager $notificationPreferenceManager
     */
    private $notificationPreferenceManager;

    /**
     * ProfessionalEditProfileFormType constructor.
     * @param NotificationPreferencesManager $notificationPreferenceManager
     */
    public function __construct(NotificationPreferencesManager $notificationPreferenceManager)
    {
        $this->notificationPreferenceManager = $notificationPreferenceManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $options['user'];

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
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('schools', EntityType::class, [
                'class' => School::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
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
                'expanded' => false,
                'multiple' => true,
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'uk-checkbox', 'tooltip' => $choice->getDescription()];
                },
            ])
            ->add('regions', EntityType::class, [
                'class' => Region::class,
                'expanded' => false,
                'multiple' => true,
                'choice_label' => 'friendlyName',
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
	        ->add('geoZipCode', HiddenType::class, [])
            ->add('notificationPreferences', ChoiceType::class, [
                'expanded' => true,
                'multiple' => true,
                'choices'  => NotificationPreferencesManager::$choices,
                'mapped' => false
            ])
            ->add('notificationPreferenceMask', HiddenType::class);


        $builder->get('phone')->addModelTransformer(new CallbackTransformer(
            function ($phone) {
                return str_replace('-', '', $phone);
            },
            function ($phone) {
                return $this->localize_us_number($phone);
            }
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use($loggedInUser) {

            $data = $event->getData();

            $notificationPreferences = [];
            foreach(NotificationPreferencesManager::$choices as $label => $bit) {

                if($this->notificationPreferenceManager->isNotificationDisabled($bit, $loggedInUser)) {
                    $notificationPreferences[] = $bit;
                }
            }

            if(!empty($notificationPreferences)) {
                $this->modifyNotificationPreferencesField($event->getForm(), $notificationPreferences);
            }

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

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $notificationPreferenceMask = !empty($data['notificationPreferences']) ? array_sum($data['notificationPreferences']) : null;

            if($notificationPreferenceMask) {
                $data['notificationPreferenceMask'] = $notificationPreferenceMask;
            } else {
                $data['notificationPreferenceMask'] = null;
            }

            $event->setData($data);
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
            'expanded' => false,
            'multiple' => true,
            'choice_attr' => function($choice, $key, $value) {
                return ['class' => 'uk-checkbox'];
            }
        ]);

    }

    private function modifyNotificationPreferencesField(FormInterface $form, $notificationPreferences) {

        if(!empty($notificationPreferences)) {
            $form->remove('notificationPreferences');

            $form->add('notificationPreferences', ChoiceType::class, [
                'expanded' => true,
                'multiple' => true,
                'choices'  => NotificationPreferencesManager::$choices,
                'mapped' => false,
                'data' => $notificationPreferences
            ]);
        }
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
            'skip_validation',
            'user'
        ]);
    }

    private function localize_us_number($phone) {
        $numbers_only = preg_replace("/[^\d]/", "", $phone);
        return preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $numbers_only);
    }
}
