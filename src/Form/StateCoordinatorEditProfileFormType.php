<?php

namespace App\Form;

use App\Entity\EducatorUser;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SecondaryIndustry;
use App\Entity\StateCoordinator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Service\NotificationPreferencesManager;
use App\Util\FormHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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

class StateCoordinatorEditProfileFormType extends AbstractType
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

        /** @var User $loggedInUser */
        $loggedInUser = $options['user'];

        $builder
            ->add('firstName', TextType::class, [])
            ->add('lastName', TextType::class, [])
            ->add('email', EmailType::class, [])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password'
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
            'data_class' => StateCoordinator::class,
            'validation_groups' => function (FormInterface $form) {
                return ['STATE_COORDINATOR_EDIT'];
            },
        ]);

        $resolver->setRequired([
            'skip_validation',
            'user'
        ]);
    }
}
