<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\Region;
use App\Entity\School;
use App\Entity\SecondaryIndustry;
use App\Entity\State;
use App\Entity\User;
use App\Repository\SchoolRepository;
use App\Repository\SecondaryIndustryRepository;
use App\Service\Geocoder;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class NewCompanyFormType extends AbstractType
{
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
     * NewCompanyFormType constructor.
     *
     * @param SchoolRepository            $schoolRepository
     * @param SecondaryIndustryRepository $secondaryIndustryRepository
     * @param Geocoder                    $geocoder
     */
    public function __construct(SchoolRepository $schoolRepository,
                                SecondaryIndustryRepository $secondaryIndustryRepository, Geocoder $geocoder
    ) {
        $this->schoolRepository            = $schoolRepository;
        $this->secondaryIndustryRepository = $secondaryIndustryRepository;
        $this->geocoder                    = $geocoder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Company $company */
        $company = $options['company'];

        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Bass Pro Shop',
                ],
            ])
            ->add('companyAddressSearch', TextType::class, [
                'attr' => [
                    'autocomplete' => true,
                    'placeholder' => 'Filter by your company location',
                ],
                'data' => $company->getFormattedAddress(),
            ])
            ->add('street', TextType::class, [])
            ->add('city', TextType::class, [])
            ->add('state', EntityType::class, [
                'class' => State::class,
                'choice_label' => 'name',
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('zipcode', TextType::class, [])
            ->add('website', TextType::class, [
                'attr' => [
                    'placeholder' => 'http://bassproshop.com',
                ],
            ])
            ->add('phone', TextType::class, [
                'attr' => [
                    'placeholder' => 'xxx-xxx-xxxx',
                ],
            ])
            ->add('phoneExt', TextType::class, [
                'attr' => [
                    'placeholder' => '123',
                ],
            ])
            ->add('emailAddress', TextType::class, [
                'attr' => [
                    'placeholder' => 'info@bassproshop.com',
                ],
            ])
            ->add('primaryIndustry', EntityType::class, [
                'class' => Industry::class,
                'choice_label' => 'name',
                'placeholder' => 'Select Industry',
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('regions', EntityType::class, [
                'class' => Region::class,
                'expanded' => false,
                'multiple' => true,
                'choice_label' => 'friendlyName',
            ])->add('schools', EntityType::class, [
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
                    return ['class' => 'uk-checkbox',
                            'data-latitude' => $choice->getLatitude(),
                            'data-longitude' => $choice->getLongitude(),
                            'data-school' => $choice->getName(),
                    ];
                },
            ])
            ->add('shortDescription', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Bass pro provides the highest quality fishing line and lure in the game. We specialize in fishing rods, bait & lure, and courses and training on learning how to fish.',
                ],
            ])
            ->add('description', TextareaType::class)
            ->add('companyLinkedinPage', TextType::class, [])
            ->add('companyFacebookPage', TextType::class, [])
            ->add('companyInstagramPage', TextType::class, [])
            ->add('companyTwitterPage', TextType::class, [])
            ->add('addressSearch', TextType::class, [
                'attr' => [
                    'placeholder' => 'Filter by your company location',
                ],
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
            ])
            ->add('thumbnailImage', HiddenType::class)
            ->add('featuredImage', HiddenType::class);

        $builder->get('phone')->addModelTransformer(new CallbackTransformer(
            function ($phone) {
                return str_replace('-', '', $phone);
            },
            function ($phone) {
                return $this->localize_us_number($phone);
            }
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            /** @var ProfessionalUser $data */
            $data = $event->getData();

            $this->modifyForm($event->getForm(), $data->getPrimaryIndustry());
        });

        $builder->get('primaryIndustry')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Industry $industry */
            $industry = $event->getForm()->getData();

            $this->modifyForm($event->getForm()->getParent(), $industry);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if (!isset($data['schools'])) {
                $data['schools'] = [];
            }

            $originalSchoolIds = $data['schools'];
            $schools           = [];

            if (!empty($data['addressSearch']) && !empty($data['radiusSearch'])) {

                if ($coordinates = $this->geocoder->geocode($data['addressSearch'])) {
                    list($latN, $latS, $lonE, $lonW) = $this->geocoder->calculateSearchSquare($coordinates['lat'], $coordinates['lng'], $data['radiusSearch']);
                    $schools = $this->schoolRepository->findByRadius($latN, $latS, $lonE, $lonW, $coordinates['lat'], $coordinates['lng']);

                    $schoolIds = [];
                    foreach ($schools as $school) {
                        $schoolIds[] = $school['id'];
                    }

                    $schools = $this->schoolRepository->getByArrayOfIds($schoolIds);
                }
            }

            if (!empty($data['regions'])) {

                if (count($schools)) {

                    $regionIds = $data['regions'];

                    $schools = array_filter($schools, function (School $school) use ($regionIds) {

                        if (!$school->getRegion()) {
                            return false;
                        }

                        return in_array($school->getRegion()->getId(), $regionIds);
                    });
                } else {

                    $schools = $this->schoolRepository->findBy([
                        'region' => $data['regions'],
                    ]);
                }
            }

            $newSchoolIds = array_map(function (School $school) {
                return $school->getId();
            }, $schools);

            $schoolIds = array_intersect($originalSchoolIds, $newSchoolIds);

            // let's get the data in alphabetical order now
            // todo I don't think this is necessary though as we are doing that on the front end right?
            $schools = $this->schoolRepository->findBy([
                'id' => $schoolIds,
            ], ['name' => 'ASC']);

            $data['schools'] = array_map(function (School $school) {
                return $school->getId();
            }, $schools);


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

            $data['secondaryIndustries'] = $secondaryIndustryIds;

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
                    return ['class' => 'uk-checkbox',
                            'data-latitude' => $choice->getLatitude(),
                            'data-longitude' => $choice->getLongitude(),
                            'data-school' => $choice->getName(),
                    ];
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
                    return ['class' => 'uk-checkbox',
                            'data-latitude' => $choice->getLatitude(),
                            'data-longitude' => $choice->getLongitude(),
                            'data-school' => $choice->getName(),
                    ];
                },
            ]);

        });
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);

        $resolver->setRequired(['company', 'skip_validation']);
    }

    /**
     * @param Company $company
     *
     * @return array
     */
    private function thumbnailImageConstraints($company)
    {

        $imageConstraints = [];

        if (!$company->getThumbnailImage()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Please upload a thumbnail image',
                'groups' => ['CREATE'],
            ]);
        }

        return $imageConstraints;
    }


    /**
     * @param Company $company
     *
     * @return array
     */
    private function featuredImageConstraints($company)
    {

        $imageConstraints = [];

        if (!$company->getFeaturedImage()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Please upload a featured image',
                'groups' => ['CREATE'],
            ]);
        }

        return $imageConstraints;
    }

    private function localize_us_number($phone)
    {
        $numbers_only = preg_replace("/[^\d]/", "", $phone);

        return preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $numbers_only);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {

        /** @var Company $company */
        $company = $form->getData();

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
