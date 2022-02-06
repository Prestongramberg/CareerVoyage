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
use App\Repository\SchoolRepository;
use App\Repository\SecondaryIndustryRepository;
use App\Service\Geocoder;
use App\Util\FormHelper;
use App\Validator\Constraints\EmailAlreadyExists;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Service\NotificationPreferencesManager;

class ProfessionalEditProfileFormType extends AbstractType
{
    use FormHelper;

    /**
     * @var NotificationPreferencesManager $notificationPreferenceManager
     */
    private $notificationPreferenceManager;

    /**
     * @var SchoolRepository
     */
    private $schoolRepository;

    /**
     * @var SecondaryIndustryRepository
     */
    private $secondaryIndustryRepository;

    /**
     * @var Geocoder
     */
    private $geocoder;

    /**
     * @var string
     */
    private $latitude;

    /**
     * @var string
     */
    private $longitude;

    /**
     * @var School[]
     */
    private $schools = [];

    /**
     * @var Region[]
     */
    private $regions = [];

    /**
     * ProfessionalEditProfileFormType constructor.
     *
     * @param NotificationPreferencesManager $notificationPreferenceManager
     * @param SchoolRepository               $schoolRepository
     * @param SecondaryIndustryRepository    $secondaryIndustryRepository
     * @param Geocoder                       $geocoder
     */
    public function __construct(NotificationPreferencesManager $notificationPreferenceManager,
                                SchoolRepository $schoolRepository,
                                SecondaryIndustryRepository $secondaryIndustryRepository, Geocoder $geocoder
    ) {
        $this->notificationPreferenceManager = $notificationPreferenceManager;
        $this->schoolRepository              = $schoolRepository;
        $this->secondaryIndustryRepository   = $secondaryIndustryRepository;
        $this->geocoder                      = $geocoder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ProfessionalUser $user */
        $user = $options['user'];

        $builder
            ->add('firstName', TextType::class, [
                'block_prefix' => 'wrapped_text',
            ])
            ->add('lastName', TextType::class)
            ->add('email', EmailType::class, [])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password',
            ])
            ->add('primaryIndustry', EntityType::class, [
                'class' => Industry::class,
                'choice_label' => 'name',
                'placeholder' => 'Select Industry',
                'expanded' => false,
                'multiple' => false,
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
                'choice_attr' => function ($choice, $key, $value) {
                    return ['class' => 'uk-checkbox', 'title' => $choice->getDescription()];
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
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('zipcode', TextType::class, [])
            ->add('briefBio', TextareaType::class)
            ->add('linkedinProfile', TextType::class)
            ->add('phone', TextType::class)
            ->add('phoneExt', TextType::class, [
                'attr' => [
                    'placeholder' => '123',
                ],
            ])
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
            ])
            ->add('notificationPreferences', ChoiceType::class, [
                'expanded' => true,
                'multiple' => true,
                'choices' => NotificationPreferencesManager::$choices,
                'mapped' => false,
            ])
            ->add('notificationPreferenceMask', HiddenType::class)
            ->add('addressSearch', TextType::class, [
                'attr' => [
                    'placeholder' => 'Filter your volunteer schools by address',
                ],
            ])
            ->add('personalAddressSearch', TextType::class, [
                'attr' => [
                    'autocomplete' => true,
                    'placeholder' => 'Filter by your work location',
                ],
                'data' => $user->getFormattedAddress(),
            ])
            ->add('radiusSearch', ChoiceType::class, [
                'choices' => [
                    '25 miles' => 25,
                    '50 miles' => 50,
                    '75 miles' => 75,
                    '150 miles' => 150,
                ],
                'data' => 150,
                'expanded' => false,
                'multiple' => false,
            ]);

        $builder->get('phone')->addModelTransformer(new CallbackTransformer(
            function ($phone) {
                return str_replace('-', '', $phone);
            },
            function ($phone) {
                return $this->localize_us_number($phone);
            }
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {

            /** @var ProfessionalUser $data */
            $data = $event->getData();
            $form = $event->getForm();

            $addressSearch = $data->getAddressSearch() ?? '';
            $radiusSearch  = $data->getRadiusSearch() ?? '';

            $regionIds     = array_map(function (Region $region) {
                return $region->getId();
            }, $data->getRegions()->toArray());

            $allowableSchools = $this->getAllowableSchools($regionIds, $addressSearch, $radiusSearch);

            $form->add('schools', EntityType::class, [
                'class' => School::class,
                'choices' => $allowableSchools,
                'choice_label' => 'name',
                'placeholder' => 'Schools I volunteer at.',
                'multiple' => true,
                'expanded' => false,
                'choice_attr' => function ($choice, $key, $value) {
                    return ['class' => 'uk-checkbox',
                            'data-latitude' => $choice->getLatitude(),
                            'data-longitude' => $choice->getLongitude(),
                            'data-school' => $choice->getName(),
                    ];
                },
            ]);

            $notificationPreferences = [];
            foreach (NotificationPreferencesManager::$choices as $label => $bit) {

                if ($this->notificationPreferenceManager->isNotificationDisabled($bit, $user)) {
                    $notificationPreferences[] = $bit;
                }
            }

            if (!empty($notificationPreferences)) {
                $this->modifyNotificationPreferencesField($form, $notificationPreferences);
            }

            $this->modifyForm($form, $data->getPrimaryIndustry());
        });

        $builder->get('primaryIndustry')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Industry $industry */
            $industry = $event->getForm()->getData();

            $this->modifyForm($event->getForm()->getParent(), $industry);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $notificationPreferenceMask = !empty($data['notificationPreferences']) ? array_sum($data['notificationPreferences']) : null;

            if ($notificationPreferenceMask) {
                $data['notificationPreferenceMask'] = $notificationPreferenceMask;
            } else {
                $data['notificationPreferenceMask'] = null;
            }

            $addressSearch   = $data['addressSearch'] ?? '';
            $radiusSearch    = $data['radiusSearch'] ?? '';
            $regionIds       = $data['regions'] ?? [];
            $data['schools'] = $data['schools'] ?? [];

            $allowableSchools = $this->getAllowableSchools($regionIds, $addressSearch, $radiusSearch);

            $allowableSchoolIds = array_map(function (School $school) {
                return $school->getId();
            }, $allowableSchools);

            $data['schools'] = array_intersect($data['schools'], $allowableSchoolIds);

            if (!isset($data['secondaryIndustries'])) {
                $data['secondaryIndustries'] = [];
            }

            if (!isset($data['primaryIndustry'])) {
                $data['primaryIndustry'] = null;
            }

            $secondaryIndustryIds = [];

            if (!empty($data['secondaryIndustries'])) {

                $secondaryIndustries = $this->secondaryIndustryRepository->findBy([
                    'id' => $data['secondaryIndustries'],
                ]);

                foreach ($secondaryIndustries as $secondaryIndustry) {

                    if ($secondaryIndustry->getPrimaryIndustry()->getId() == $data['primaryIndustry']) {
                        $secondaryIndustryIds[] = $secondaryIndustry->getId();
                    }
                }
            }

            if ($form->has('schools')) {
                $form->remove('schools');
            }

            $form->add('schools', EntityType::class, [
                'class' => School::class,
                'choices' => $allowableSchools,
                'choice_label' => 'name',
                'placeholder' => 'Schools I volunteer at.',
                'multiple' => true,
                'expanded' => false,
                'choice_attr' => function ($choice, $key, $value) {
                    return ['class' => 'uk-checkbox',
                            'data-latitude' => $choice->getLatitude(),
                            'data-longitude' => $choice->getLongitude(),
                            'data-school' => $choice->getName(),
                    ];
                },
            ]);

            $data['secondaryIndustries'] = $secondaryIndustryIds;

            $event->setData($data);
        });

        if (is_array($options['validation_groups']) && in_array('PROFESSIONAL_PROFILE_PERSONAL', $options['validation_groups'], true)) {
            $this->setupImmutableFields($builder, $options, [
                'schools',
                'regions',
                'radiusSearch',
                'addressSearch',
                'email',
                'plainPassword',
                'isPhoneHiddenFromProfile',
                'isEmailHiddenFromProfile',
                'notificationPreferenceMask',
            ]);
        }

        if (is_array($options['validation_groups']) && in_array('PROFESSIONAL_PROFILE_REGION', $options['validation_groups'], true)) {
            $this->setupImmutableFields($builder, $options, [
                'email',
                'plainPassword',
                'isPhoneHiddenFromProfile',
                'isEmailHiddenFromProfile',
                'notificationPreferenceMask',
                'rolesWillingToFulfill',
                'primaryIndustry',
                'firstName',
                'lastName',
                'street',
                'city',
                'state',
                'zipcode',
                'personalAddressSearch',
                'briefBio',
                'linkedinProfile',
                'phone',
                'phoneExt',
                'interests',
            ]);
        }

        if (is_array($options['validation_groups']) && in_array('PROFESSIONAL_PROFILE_ACCOUNT', $options['validation_groups'], true)) {
            $this->setupImmutableFields($builder, $options, [
                'schools',
                'regions',
                'radiusSearch',
                'personalAddressSearch',
                'addressSearch',
                'rolesWillingToFulfill',
                'primaryIndustry',
                'firstName',
                'lastName',
                'street',
                'city',
                'state',
                'zipcode',
                'briefBio',
                'linkedinProfile',
                'phone',
                'phoneExt',
                'interests',
            ]);
        }

    }

    private function modifyForm(FormInterface $form, Industry $industry = null)
    {

        if (!$industry) {
            $choices = [];
        } else {
            $choices = $this->secondaryIndustryRepository->findBy([
                'primaryIndustry' => $industry->getId(),
            ]);
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

    /**
     * @param array $regionIds
     * @param null  $addressSearch
     * @param null  $radiusSearch
     *
     * @return School[]|array|mixed|mixed[]
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function getAllowableSchools($regionIds = [], $addressSearch = null, $radiusSearch = null)
    {
        $schools = [];

        $returnAllSchools = (
            empty($regionIds) &&
            (empty($addressSearch) || empty($radiusSearch))
        );

        if ($returnAllSchools) {
            $schools = $this->schoolRepository->findAll();
        }

        if (!empty($addressSearch) && !empty($radiusSearch)) {

            if ($coordinates = $this->geocoder->geocode($addressSearch)) {
                [$latN, $latS, $lonE, $lonW] = $this->geocoder->calculateSearchSquare($coordinates['lat'], $coordinates['lng'], $radiusSearch);
                $schools = $this->schoolRepository->findByRadius($latN, $latS, $lonE, $lonW, $coordinates['lat'], $coordinates['lng']);

                $schoolIds = [];
                foreach ($schools as $school) {
                    $schoolIds[] = $school['id'];
                }

                $schools = $this->schoolRepository->getByArrayOfIds($schoolIds);
            }
        }

        if (!empty($regionIds)) {

            if (count($schools)) {

                $schools = array_filter($schools, function (School $school) use ($regionIds) {

                    if (!$school->getRegion()) {
                        return false;
                    }

                    return in_array($school->getRegion()->getId(), $regionIds);
                });
            } else {

                $schools = $this->schoolRepository->findBy([
                    'region' => $regionIds,
                ]);
            }
        }

        usort($schools, function($a, $b) {
            return strcmp($a->getName(), $b->getName());
        });

        $this->schools = $schools;

        return $schools;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProfessionalUser::class,
        ]);

        $resolver->setRequired([
            'skip_validation',
            'user',
        ]);
    }

    private function localize_us_number($phone)
    {
        $numbers_only = preg_replace("/[^\d]/", "", $phone);

        return preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $numbers_only);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {

        /** @var ProfessionalUser $professionalUser */
        $professionalUser = $form->getData();

        $schools = $options['data']->getSchools();

        $schoolsJson = [];
        foreach ($schools as $school) {

            if (!$school->getLongitude() || !$school->getLatitude()) {
                continue;
            }

            $schoolsJson[] = [
                'name' => $school->getName(),
                'latitude' => $school->getLatitude(),
                'longitude' => $school->getLongitude(),
            ];
        }

        $view->vars['schools'] = $schoolsJson;
    }
}
