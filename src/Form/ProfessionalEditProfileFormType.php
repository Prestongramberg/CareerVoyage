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
use App\Service\Geocoder;
use App\Util\FormHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
     * @param Geocoder                       $geocoder
     */
    public function __construct(NotificationPreferencesManager $notificationPreferenceManager,
                                SchoolRepository $schoolRepository, Geocoder $geocoder
    ) {
        $this->notificationPreferenceManager = $notificationPreferenceManager;
        $this->schoolRepository              = $schoolRepository;
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
            ->add('email')
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
                    'placeholder' => 'Filter by your location',
                ],
            ])
            ->add('personalAddressSearch', TextType::class, [
                'attr' => [
                    'autocomplete' => true,
                    'placeholder' => 'Filter by your work location',
                ],
                'data' => $user->getFormattedAddress()
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

        $builder->add('schools', EntityType::class, [
            'class' => School::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('s')
                          ->orderBy('s.name', 'ASC');
            },
            'choice_label' => 'name',
            'placeholder' => 'Schools I volunteer at.',
            'multiple' => true,
            'expanded' => false,
            'choice_attr' => function ($choice, $key, $value) {
                return ['class' => 'uk-checkbox', 'data-latitude' => $choice->getLatitude(), 'data-longitude' => $choice->getLongitude(), 'data-school' => $choice->getName()];
            },
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

            // todo can't we remove this?
            //$this->schools = $data->getSchools();

            $notificationPreferences = [];
            foreach (NotificationPreferencesManager::$choices as $label => $bit) {

                if ($this->notificationPreferenceManager->isNotificationDisabled($bit, $user)) {
                    $notificationPreferences[] = $bit;
                }
            }

            if (!empty($notificationPreferences)) {
                $this->modifyNotificationPreferencesField($event->getForm(), $notificationPreferences);
            }

            if (!$data->getPrimaryIndustry()) {
                return;
            }
            $this->modifyForm($event->getForm(), $data->getPrimaryIndustry());
        });

        $builder->get('primaryIndustry')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Industry $industry */
            $industry = $event->getForm()->getData();

            if (!$industry) {
                return;
            }

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

            if(!isset($data['schools'])) {
                $data['schools'] = [];
            }

            $originalSchoolIds = $data['schools'];
            $schools = [];

            if(!empty($data['addressSearch']) && !empty($data['radiusSearch'])) {

                if ($coordinates = $this->geocoder->geocode($data['addressSearch'])) {
                    list($latN, $latS, $lonE, $lonW) = $this->geocoder->calculateSearchSquare($coordinates['lat'], $coordinates['lng'], $data['radiusSearch']);
                    $schools   = $this->schoolRepository->findByRadius($latN, $latS, $lonE, $lonW, $coordinates['lat'], $coordinates['lng']);

                    $schoolIds = [];
                    foreach ($schools as $school) {
                        $schoolIds[] = $school['id'];
                    }

                    $schools = $this->schoolRepository->getByArrayOfIds($schoolIds);
                }
            }

            if(!empty($data['regions'])) {

                if(count($schools)) {

                    $regionIds = $data['regions'];

                    $schools = array_filter($schools, function (School $school) use ($regionIds) {

                        if (!$school->getRegion()) {
                            return false;
                        }

                        return in_array($school->getRegion()->getId(), $regionIds);
                    });
                } else {

                    $schools = $this->schoolRepository->findBy([
                        'region' => $data['regions']
                    ]);
                }
            }

            $newSchoolIds = array_map(function(School $school) {
                return $school->getId();
            }, $schools);

            $schoolIds = array_intersect($originalSchoolIds, $newSchoolIds);

            // let's get the data in alphabetical order now
            // todo I don't think this is necessary though as we are doing that on the front end right?
            $schools = $this->schoolRepository->findBy([
                'id' => $schoolIds,
            ], ['name' => 'ASC']);

            $data['schools'] = array_map(function(School $school) {
                return $school->getId();
            }, $schools);

            $event->setData($data);
        });

        $builder->get('regions')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

            $regions = $event->getForm()->getData();

            if (empty($regions)) {
                return;
            }

            $form = $event->getForm()->getParent();

            if (!$form) {
                return;
            }

            if ($form->has('schools')) {
                $form->remove('schools');
            }

            $this->regions = $regions->toArray();

            $regionIds = array_map(function (Region $region) {
                return $region->getId();
            }, $this->regions);

            $schools = $this->schoolRepository->findBy([
                'region' => $regionIds,
            ], ['name' => 'ASC']);

            $this->schools = $schools;

            $form->add('schools', EntityType::class, [
                'class' => School::class,
                'choices' => $schools,
                'choice_label' => 'name',
                'placeholder' => 'Schools I volunteer at.',
                'multiple' => true,
                'expanded' => false,
                'choice_attr' => function ($choice, $key, $value) {
                    return ['class' => 'uk-checkbox', 'data-latitude' => $choice->getLatitude(), 'data-longitude' => $choice->getLongitude(), 'data-school' => $choice->getName()];
                },
            ]);

        });

        $builder->get('addressSearch')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

            // todo remove the geoAddress field from professional user then?

            // todo let's make it even more reductive and start with the region filtering then the radius and then the schools.
            // todo if we do this then we should be able to pass up all the data.
            $geoAddress = $event->getForm()->getData();

            if (empty($geoAddress)) {
                return;
            }

            $form = $event->getForm()->getParent();

            if (!$form) {
                return;
            }

            if ($coordinates = $this->geocoder->geocode($geoAddress)) {
                $this->longitude = $coordinates['lng'];
                $this->latitude  = $coordinates['lat'];
            }
        });

        $builder->get('radiusSearch')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

            // todo remove the geoAddress field from professional user then?
            $radiusSearch = $event->getForm()->getData();

            if (empty($radiusSearch)) {
                return;
            }

            $form = $event->getForm()->getParent();

            if (!$form) {
                return;
            }

            if (!$this->latitude || !$this->longitude) {
                return;
            }

            if ($form->has('schools')) {
                $form->remove('schools');
            }

            list($latN, $latS, $lonE, $lonW) = $this->geocoder->calculateSearchSquare($this->latitude, $this->longitude, $radiusSearch);
            $schools   = $this->schoolRepository->findByRadius($latN, $latS, $lonE, $lonW, $this->latitude, $this->longitude);
            $schoolIds = [];

            foreach ($schools as $school) {
                $schoolIds[] = $school['id'];
            }

            $schools = $this->schoolRepository->getByArrayOfIds($schoolIds);

            // if some region filters have been selected apply them as a filter
            if (count($this->regions)) {

                $regionIds = array_map(function (Region $region) {
                    return $region->getId();
                }, $this->regions);


                $schools = array_filter($schools, function (School $school) use ($regionIds) {

                    if (!$school->getRegion()) {
                        return false;
                    }

                    return in_array($school->getRegion()->getId(), $regionIds);
                });
            }

            $this->schools = $schools;

            $form->add('schools', EntityType::class, [
                'class' => School::class,
                'choices' => $this->schools,
                'choice_label' => 'name',
                'placeholder' => 'Schools I volunteer at.',
                'multiple' => true,
                'expanded' => false,
                'choice_attr' => function ($choice, $key, $value) {
                    return ['class' => 'uk-checkbox', 'data-latitude' => $choice->getLatitude(), 'data-longitude' => $choice->getLongitude(), 'data-school' => $choice->getName()];
                },
            ]);

        });

        if (is_array($options['validation_groups']) && in_array('PROFESSIONAL_PROFILE_PERSONAL', $options['validation_groups'], true)) {
            $this->setupImmutableFields($builder, $options, [
                'schools',
                'regions',
                'radiusSearch',
                'personalAddressSearch',
                'email',
                'plainPassword',
                'isPhoneHiddenFromProfile',
                'isEmailHiddenFromProfile',
                'notificationPreferenceMask'
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
                'interests'
            ]);
        }

        if (is_array($options['validation_groups']) && in_array('PROFESSIONAL_PROFILE_ACCOUNT', $options['validation_groups'], true)) {
            $this->setupImmutableFields($builder, $options, [
                'schools',
                'regions',
                'radiusSearch',
                'personalAddressSearch',
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
                'interests'
            ]);
        }

    }

    private function modifyForm(FormInterface $form, Industry $industry)
    {

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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProfessionalUser::class
        ]);

        $resolver->setRequired([
            'skip_validation',
            'user'
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

   /*     $schoolsForm = $form->get('schools');
        $d = $schoolsForm->getViewData();
        $schools = $schoolsForm->getData();
        $f = $schoolsForm->getNormData();
        $config = $schoolsForm->getConfig();
        $options = $config->getOptions();
        $choices = $config->getOption('choices');*/

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
