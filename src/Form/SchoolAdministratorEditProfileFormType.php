<?php

namespace App\Form;

use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SecondaryIndustry;
use App\Entity\User;
use App\Service\NotificationPreferencesManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

class SchoolAdministratorEditProfileFormType extends AbstractType
{
    /**
     * @var NotificationPreferencesManager $notificationPreferenceManager
     */
    private $notificationPreferenceManager;

    /**
     * SchoolAdministratorEditProfileFormType constructor.
     * @param NotificationPreferencesManager $notificationPreferenceManager
     */
    public function __construct(NotificationPreferencesManager $notificationPreferenceManager)
    {
        $this->notificationPreferenceManager = $notificationPreferenceManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $site = $options['data']->getSite()->getId();

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
            ->add('phone', TextType::class)
            ->add('schools', EntityType::class, [
                'class' => School::class,
                'choice_label' => 'name',
                'expanded'  => true,
                'multiple'  => true,
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'uk-checkbox'];
                },
                'query_builder' => function (EntityRepository $er) use ($site) {
                    return $er->createQueryBuilder('s')
                        ->where('s.site = :site')
                        ->setParameter('site', $site)
                        ->orderBy('s.name', 'ASC');
                },
            ])->add('notificationPreferences', ChoiceType::class, [
                'expanded' => true,
                'multiple' => true,
                'choices'  => NotificationPreferencesManager::$choices,
                'mapped' => false
            ])->add('notificationPreferenceMask', HiddenType::class);


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

        $builder->get('phone')->addModelTransformer(new CallbackTransformer(
            function ($phone) {
                return str_replace('-', '', $phone);
            },
            function ($phone) {
                return $this->localize_us_number($phone);
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SchoolAdministrator::class,
            'validation_groups' => function (FormInterface $form) {
                return ['EDIT'];
            },
        ]);

        $resolver->setRequired([
            'skip_validation',
            'user'
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


    private function localize_us_number($phone) {
        $numbers_only = preg_replace("/[^\d]/", "", $phone);
        return preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $numbers_only);
    }
}
