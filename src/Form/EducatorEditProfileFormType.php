<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\EducatorUser;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SecondaryIndustry;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Repository\SecondaryIndustryRepository;
use App\Repository\StudentUserRepository;
use App\Service\NotificationPreferencesManager;
use App\Util\FormHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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

class EducatorEditProfileFormType extends AbstractType
{
    use FormHelper;

    /**
     * @var SecondaryIndustryRepository
     */
    private $secondaryIndustryRepository;

    /**
     * @var NotificationPreferencesManager $notificationPreferenceManager
     */
    private $notificationPreferenceManager;

    /**
     * @var StudentUserRepository
     */
    private $studentUserRepository;

    /**
     * EducatorEditProfileFormType constructor.
     *
     * @param SecondaryIndustryRepository    $secondaryIndustryRepository
     * @param NotificationPreferencesManager $notificationPreferenceManager
     * @param StudentUserRepository          $studentUserRepository
     */
    public function __construct(
        SecondaryIndustryRepository $secondaryIndustryRepository,
        NotificationPreferencesManager $notificationPreferenceManager,
        StudentUserRepository $studentUserRepository
    ) {
        $this->secondaryIndustryRepository   = $secondaryIndustryRepository;
        $this->notificationPreferenceManager = $notificationPreferenceManager;
        $this->studentUserRepository         = $studentUserRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var EducatorUser $educator */
        $educator = $loggedInUser = $options['educator'];

        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('displayName', TextType::class, [
                'data' => $educator->getFullName(),
            ])
            ->add('briefBio', TextareaType::class)
            ->add('linkedinProfile', TextType::class)
            ->add('phone', TextType::class)
            ->add('phoneExt', TextType::class, [
                'attr' => [
                    'placeholder' => '123',
                ],
            ])
            ->add('username')
            ->add('email', EmailType::class, [])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password',
            ])
            ->add('interests', TextareaType::class)
            ->add('isEmailHiddenFromProfile', ChoiceType::class, [
                'choices' => [
                    'Yes' => true,

                    'No' => false,
                ],
            ])
            ->add('isPhoneHiddenFromProfile', ChoiceType::class, [
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
            ]);

        $builder->add('primaryIndustries', EntityType::class, [
            'class' => Industry::class,
            'choice_label' => 'name',
            'expanded' => false,
            'multiple' => true,
            'placeholder' => 'Select Industry',
            'choice_attr' => function ($choice, $key, $value) {
                return ['class' => 'uk-checkbox'];
            },
        ]);

        $builder->add('myCourses', EntityType::class, [
            'class' => Course::class,
            'multiple' => true,
            'expanded' => false,
            'choice_attr' => function ($choice, $key, $value) {
                return ['class' => 'uk-checkbox'];
            },
            'choice_label' => function (Course $course) {
                return $course->getTitle();
            },
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')
                          ->orderBy('c.title', 'ASC');
            },
        ]);

        $builder->add('studentUsers', EntityType::class, [
            'class' => StudentUser::class,
            'multiple' => true,
            'expanded' => false,
            'choice_attr' => function ($choice, $key, $value) {
                return ['class' => 'uk-checkbox'];
            },
            'choice_label' => function (StudentUser $student) {
                return $student->getFullName();
            },
            'query_builder' => function (EntityRepository $er) use ($educator) {
                return $er->createQueryBuilder('s')
                          ->where('s.school = :school')
                          ->andWhere('s.deleted = :deleted')
                          ->setParameter('school', $educator->getSchool())
                          ->setParameter('deleted', false)
                          ->orderBy('s.firstName', 'ASC');
            },
        ]);

        $builder->add('notificationPreferences', ChoiceType::class, [
            'expanded' => true,
            'multiple' => true,
            'choices' => NotificationPreferencesManager::$choices,
            'mapped' => false,
        ])->add('notificationPreferenceMask', HiddenType::class);


        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $notificationPreferenceMask = !empty($data['notificationPreferences']) ? array_sum($data['notificationPreferences']) : null;

            if ($notificationPreferenceMask) {
                $data['notificationPreferenceMask'] = $notificationPreferenceMask;
            } else {
                $data['notificationPreferenceMask'] = null;
            }

            if (!isset($data['secondaryIndustries'])) {
                $data['secondaryIndustries'] = [];
            }

            if (!isset($data['primaryIndustries'])) {
                $data['primaryIndustries'] = [];
            }

            $secondaryIndustryIds = [];

            if (!empty($data['secondaryIndustries'])) {

                $secondaryIndustries = $this->secondaryIndustryRepository->findBy([
                    'id' => $data['secondaryIndustries'],
                ]);

                foreach ($secondaryIndustries as $secondaryIndustry) {

                    if (in_array($secondaryIndustry->getPrimaryIndustry()->getId(), $data['primaryIndustries'])) {
                        $secondaryIndustryIds[] = $secondaryIndustry->getId();
                    }
                }
            }

            $data['secondaryIndustries'] = $secondaryIndustryIds;

            $event->setData($data);
        });

        $builder->get('primaryIndustries')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

            $industries = $event->getForm()->getData();

            $this->modifyForm($event->getForm()->getParent(), $industries);
        });


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($loggedInUser) {

            /** @var EducatorUser $data */
            $data = $event->getData();

            $notificationPreferences = [];
            foreach (NotificationPreferencesManager::$choices as $label => $bit) {

                if ($this->notificationPreferenceManager->isNotificationDisabled($bit, $loggedInUser)) {
                    $notificationPreferences[] = $bit;
                }
            }

            if (!empty($notificationPreferences)) {
                $this->modifyNotificationPreferencesField($event->getForm(), $notificationPreferences);
            }

            $this->modifyForm($event->getForm(), $data->getPrimaryIndustries());
        });

        $builder->get('phone')->addModelTransformer(new CallbackTransformer(
            function ($phone) {
                return str_replace('-', '', $phone);
            },
            function ($phone) {
                return $this->localize_us_number($phone);
            }
        ));

        if (is_array($options['validation_groups']) && in_array('EDUCATOR_PROFILE_PERSONAL', $options['validation_groups'], true)) {
            $this->setupImmutableFields($builder, $options, [
                'studentUsers',
                'username',
                'email',
                'plainPassword',
                'isPhoneHiddenFromProfile',
                'isEmailHiddenFromProfile',
                'notificationPreferenceMask',
            ]);
        }

        if (is_array($options['validation_groups']) && in_array('EDUCATOR_PROFILE_STUDENT', $options['validation_groups'], true)) {
            $this->setupImmutableFields($builder, $options, [
                'username',
                'email',
                'plainPassword',
                'isPhoneHiddenFromProfile',
                'isEmailHiddenFromProfile',
                'notificationPreferenceMask',
                'myCourses',
                'primaryIndustries',
                'firstName',
                'lastName',
                'displayName',
                'briefBio',
                'linkedinProfile',
                'phone',
                'phoneExt',
                'interests'
            ]);
        }

        if (is_array($options['validation_groups']) && in_array('EDUCATOR_PROFILE_ACCOUNT', $options['validation_groups'], true)) {
            $this->setupImmutableFields($builder, $options, [
                'studentUsers',
                'myCourses',
                'primaryIndustries',
                'firstName',
                'lastName',
                'displayName',
                'briefBio',
                'linkedinProfile',
                'phone',
                'phoneExt',
                'interests'
            ]);
        }

    }

    private function modifyForm(FormInterface $form, $industries)
    {
        $options = $form->getConfig()->getOptions();

        $industryIds = array_map(function (Industry $industry) {
            return $industry->getId();
        }, $industries->toArray());

        if (empty($industryIds)) {
            $choices = [];
        } else {
            $choices = $this->secondaryIndustryRepository->findBy([
                'primaryIndustry' => $industryIds,
            ]);
        }

        if ($form->has('secondaryIndustries')) {
            $form->remove('secondaryIndustries');
        }

        $form->add('secondaryIndustries', EntityType::class, [
            'class' => SecondaryIndustry::class,
            'choices' => $choices,
            'choice_label' => 'name',
            'expanded' => false,
            'multiple' => true,
            'choice_attr' => function ($choice, $key, $value) {
                return ['class' => 'uk-checkbox'];
            },
            'group_by' => function ($choice, $key, $value) {

                return $choice->getPrimaryIndustry()->getName();
            },
        ]);
    }

    private function modifyNotificationPreferencesField(FormInterface $form, $notificationPreferences)
    {

        if (!empty($notificationPreferences)) {
            $form->remove('notificationPreferences');

            $form->add('notificationPreferences', ChoiceType::class, [
                'expanded' => true,
                'multiple' => true,
                'choices' => NotificationPreferencesManager::$choices,
                'mapped' => false,
                'data' => $notificationPreferences,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EducatorUser::class,
            'csrf_protection' => true
        ]);

        $resolver->setRequired([
            'skip_validation',
            'educator',
        ]);
    }

    private function localize_us_number($phone)
    {
        $numbers_only = preg_replace("/[^\d]/", "", $phone);

        return preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $numbers_only);
    }
}
