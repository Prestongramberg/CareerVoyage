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
use App\Repository\ImageRepository;
use App\Repository\SchoolRepository;
use App\Repository\SecondaryIndustryRepository;
use App\Service\Geocoder;
use App\Util\FormHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\PersistentCollection;
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

class CompanyFormType extends AbstractType
{
    use FormHelper;

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
     * @var ImageRepository
     */
    private $imageRepository;

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
     * CompanyFormType constructor.
     *
     * @param SchoolRepository            $schoolRepository
     * @param SecondaryIndustryRepository $secondaryIndustryRepository
     * @param Geocoder                    $geocoder
     * @param ImageRepository             $imageRepository
     */
    public function __construct(SchoolRepository $schoolRepository,
                                SecondaryIndustryRepository $secondaryIndustryRepository, Geocoder $geocoder,
                                ImageRepository $imageRepository
    ) {
        $this->schoolRepository            = $schoolRepository;
        $this->secondaryIndustryRepository = $secondaryIndustryRepository;
        $this->geocoder                    = $geocoder;
        $this->imageRepository             = $imageRepository;
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
                    'placeholder' => 'Filter by your company address',
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
            ])->add('shortDescription', TextareaType::class, [
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
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('thumbnailImage', HiddenType::class)
            ->add('featuredImage', HiddenType::class);

        if($company->getId()) {
            $builder->add('owner', EntityType::class, [
                'class' => ProfessionalUser::class,
                'query_builder' => function (EntityRepository $er) use ($company) {
                    return $er->createQueryBuilder('p')
                              ->where('p.company = :company')
                              ->setParameter('company', $company->getId());
                },
                'choice_label' => 'email'
            ]);
        }

        $builder->get('thumbnailImage')->addModelTransformer(new CallbackTransformer(
            function ($image) {

                if (!$image) {
                    return null;
                }

                return $image->getId();
            },
            function ($image) {

                if (!$image) {
                    return null;
                }

                return $this->imageRepository->find($image);
            }
        ));

        $builder->get('featuredImage')->addModelTransformer(new CallbackTransformer(
            function ($image) {

                if (!$image) {
                    return null;
                }

                return $image->getId();
            },
            function ($image) {

                if (!$image) {
                    return null;
                }

                return $this->imageRepository->find($image);
            }
        ));

        $builder->get('phone')->addModelTransformer(new CallbackTransformer(
            function ($phone) {
                return str_replace('-', '', $phone);
            },
            function ($phone) {
                return $this->localize_us_number($phone);
            }
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            /** @var Company $data */
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

        if (is_array($options['validation_groups']) && in_array('COMPANY_GENERAL', $options['validation_groups'], true)) {
            $this->setupImmutableFields($builder, $options, [
                'schools',
                'regions',
                'radiusSearch',
                'addressSearch',
            ]);
        }

        if (is_array($options['validation_groups']) && in_array('COMPANY_SCHOOLS', $options['validation_groups'], true)) {
            $this->setupImmutableFields($builder, $options, [
                'name',
                'companyAddressSearch',
                'street',
                'city',
                'state',
                'zipcode',
                'website',
                'phone',
                'phoneExt',
                'emailAddress',
                'primaryIndustry',
                'description',
                'shortDescription',
                'companyLinkedinPage',
                'companyFacebookPage',
                'companyInstagramPage',
                'companyTwitterPage',
                'thumbnailImage',
                'featuredImage'
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
                list($latN, $latS, $lonE, $lonW) = $this->geocoder->calculateSearchSquare($coordinates['lat'], $coordinates['lng'], $radiusSearch);
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

        $this->schools = $schools;

        return $schools;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);

        $resolver->setRequired(['company', 'skip_validation']);
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
