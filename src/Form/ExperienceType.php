<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\Experience;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SchoolExperience;
use App\Entity\State;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\SecondaryIndustryRepository;
use App\Repository\StateRepository;
use App\Repository\TagRepository;
use App\Service\Geocoder;
use App\Util\TimeHelper;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SKAgarwal\GoogleApi\PlacesApi;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Repository\UserRepository;


class ExperienceType extends AbstractType implements DataMapperInterface
{
    use TimeHelper;

    /**
     * @var SecondaryIndustryRepository
     */
    private $secondaryIndustryRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var StateRepository
     */
    private $stateRepository;

    /**
     * @var Geocoder
     */
    private $geocoder;

    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param SecondaryIndustryRepository $secondaryIndustryRepository
     * @param UserRepository              $userRepository
     * @param StateRepository             $stateRepository
     * @param Geocoder                    $geocoder
     * @param TagRepository               $tagRepository
     * @param EntityManagerInterface      $entityManager
     */
    public function __construct(
        SecondaryIndustryRepository $secondaryIndustryRepository, UserRepository $userRepository,
        StateRepository $stateRepository, Geocoder $geocoder, TagRepository $tagRepository,
        EntityManagerInterface $entityManager)
    {
        $this->secondaryIndustryRepository = $secondaryIndustryRepository;
        $this->userRepository              = $userRepository;
        $this->stateRepository             = $stateRepository;
        $this->geocoder                    = $geocoder;
        $this->tagRepository               = $tagRepository;
        $this->entityManager               = $entityManager;
    }

    public function mapDataToForms($viewData, $forms): void
    {
        /** @var SchoolExperience|CompanyExperience $viewData */

        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof Experience) {
            throw new UnexpectedTypeException($viewData, Experience::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['title']->setData($viewData->getTitle());
        $forms['about']->setData($viewData->getAbout());
        $forms['timezone']->setData($viewData->getTimezone());

        if(isset($forms['schoolContact'])) {
            $forms['schoolContact']->setData($viewData->getSchoolContact());
        }

        if(isset($forms['employeeContact'])) {
            $forms['employeeContact']->setData($viewData->getEmployeeContact());
        }

        $forms['addressSearch']->setData($viewData->getAddressSearch());
        $forms['type']->setData($viewData->getType());
        $forms['startDate']->setData(new DateTime());
        $forms['startTime']->setData('19:30');
        $forms['endDate']->setData(new DateTime('+1 day'));
        $forms['endTime']->setData('20:30');

        if ($startDateAndTime = $viewData->getStartDateAndTime()) {
            $forms['startDate']->setData($startDateAndTime);
            $forms['startTime']->setData($startDateAndTime->format('H:i'));
        }

        if ($endDateAndTime = $viewData->getEndDateAndTime()) {
            $forms['endDate']->setData($endDateAndTime);
            $forms['endTime']->setData($endDateAndTime->format('H:i'));
        }

        $tags = [];
        foreach ($viewData->getTags() as $tag) {
            $tags[] = [
                'value' => $tag->getName(),
                'id'    => $tag->getId(),
            ];
        }

        $forms['tags']->setData(json_encode($tags));
    }

    public function mapFormsToData($forms, &$viewData): void
    {
        /** @var SchoolExperience|CompanyExperience $viewData */

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $startDate = $forms['startDate']->getData();
        $startTime = $forms['startTime']->getData();
        $endDate   = $forms['endDate']->getData();
        $endTime   = $forms['endTime']->getData();
        $tags      = $forms['tags']->getData();

        if ($startDate && $startTime) {
            $startDateAndTime = clone $startDate;

            [$hours, $minutes] = explode(":", $startTime);

            $startDateAndTime->add(new \DateInterval('PT' . $hours . 'H'));
            $startDateAndTime->add(new \DateInterval('PT' . $minutes . 'M'));
            $viewData->setStartDateAndTime($startDateAndTime);

            $utcStartDateAndTime = clone $startDateAndTime;
            $utcStartDateAndTime->setTimezone(new DateTimeZone("UTC"));
            $viewData->setUtcStartDateAndTime($utcStartDateAndTime);
        }

        if ($endDate && $endTime) {
            $endDateAndTime = clone $endDate;
            [$hours, $minutes] = explode(":", $endTime);

            $endDateAndTime->add(new \DateInterval('PT' . $hours . 'H'));
            $endDateAndTime->add(new \DateInterval('PT' . $minutes . 'M'));
            $viewData->setEndDateAndTime($endDateAndTime);

            $utcEndDateAndTime = clone $endDateAndTime;
            $utcEndDateAndTime->setTimezone(new DateTimeZone("UTC"));
            $viewData->setUtcEndDateAndTime($utcEndDateAndTime);
        }

        $viewData->setTitle($forms['title']->getData());
        $viewData->setAbout($forms['about']->getData());
        $viewData->setType($forms['type']->getData());

        if($viewData instanceof SchoolExperience) {
            $viewData->setSchoolContact($forms['schoolContact']->getData());
        }

        if($viewData instanceof CompanyExperience) {
            $viewData->setEmployeeContact($forms['employeeContact']->getData());
        }

        $viewData->setTimezone($forms['timezone']->getData());
        $viewData->setAddressSearch($forms['addressSearch']->getData());
        $addressSearch = $forms['addressSearch']->getData();

        try {
            $addressComponents = $this->geocoder->getAddressComponentsFromSearchString($addressSearch);
            $viewData->setState($addressComponents['state']);
            $viewData->setCity($addressComponents['city']);
            $viewData->setStreet($addressComponents['street']);
            $viewData->setZipcode($addressComponents['postalCode']);

            if ($coordinates = $this->geocoder->geocode($viewData->getFormattedAddress())) {
                $viewData->setLongitude($coordinates['lng']);
                $viewData->setLatitude($coordinates['lat']);
            }

        } catch (\Exception $exception) {
            // do nothing
        }

        if (!empty($tags)) {
            $tags = json_decode($tags, true);

            $originalTags = new ArrayCollection();
            foreach ($viewData->getTags() as $tag) {
                $tag->removeExperience($viewData);
                $this->entityManager->persist($tag);
            }

            foreach ($tags as $tag) {
                $value = $tag['value'];
                $id    = $tag['id'] ?? null;

                if ($id && ($tag = $this->tagRepository->find($id))) {
                    $viewData->addTag($tag);
                } else {

                    $tag = $this->tagRepository->findOneBy([
                        'name' => $value,
                    ]);

                    if ($tag) {
                        $viewData->addTag($tag);
                    } else {
                        $tag = new Tag();
                        $tag->setName($value);
                        $tag->setSystemDefined(false);
                        $this->entityManager->persist($tag);
                        $viewData->addTag($tag);
                    }
                }
            }

        }

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->setDataMapper($this);

        /** @var School $school */
        $school = $options['school'];

        /** @var Company $company */
        $company = $options['company'];

        $builder->add('title', TextType::class, [
            'attr' => [
                'placeholder' => $school ? 'How to Succeed in a Job Interview' : 'Career Fair – Emergency Medical Services',
            ],
        ])
                ->add('about', TextareaType::class, [

                ])
                ->add('type', EntityType::class, [
                    'class'         => RolesWillingToFulfill::class,
                    'choice_label'  => 'eventName',
                    'expanded'      => false,
                    'multiple'      => false,
                    'placeholder'   => 'Tell attendees what type of event this is.',
                    'query_builder' => function (EntityRepository $er) use ($company, $school) {

                        if ($school) {
                            return $er->createQueryBuilder('r')
                                      ->where('r.inSchoolEventDropdown = :inSchoolEventDropdown')
                                      ->setParameter('inSchoolEventDropdown', true);
                        }

                        if ($company) {
                            return $er->createQueryBuilder('r')
                                      ->where('r.inEventDropdown = :inEventDropdown')
                                      ->setParameter('inEventDropdown', true);
                        }

                        throw new \Exception("Form type not setup for other event types");
                    },
                ])
                ->add('addressSearch', TextType::class, [
                    'attr' => [
                        'autocomplete' => true,
                        'placeholder'  => 'Enter a location.',
                    ],
                ])
                ->add('startDateAndTime', HiddenType::class, [])
                ->add('endDateAndTime', HiddenType::class, [])
                ->add('startDate', DateType::class, [
                    'mapped'      => false,
                    'widget'      => 'single_text',
                    'html5'       => false,
                    'format'      => 'MM/dd/yyyy',
                    'constraints' => [
                        new NotBlank(['message' => 'Please select a start date.']),
                    ],
                ])
                ->add('endDate', DateType::class, [
                    'mapped'      => false,
                    'widget'      => 'single_text',
                    'html5'       => false,
                    'format'      => 'MM/dd/yyyy',
                    'constraints' => [
                        new NotBlank(['message' => 'Please select an end date.']),
                    ],
                ])
                ->add('startTime', ChoiceType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'choices'  => $this->hoursRange(0, 86400, 60 * 30),
                    'mapped'   => false,
                ])
                ->add('endTime', ChoiceType::class, [
                    'expanded' => false,
                    'multiple' => false,
                    'choices'  => $this->hoursRange(0, 86400, 60 * 30),
                    'mapped'   => false,
                ])
                ->add('timezone', ChoiceType::class, [
                    'required' => true,
                    'expanded' => false,
                    'multiple' => false,
                    'choices'  => [
                        'Eastern Time'                  => 'America/New_York',
                        'Central Time'                  => 'America/Chicago',
                        'Mountain Time'                 => 'America/Denver',
                        'Mountain Time (no DST)'        => 'America/Phoenix',
                        'Pacific Time'                  => 'America/Los_Angeles',
                        'Alaska Time'                   => 'America/Anchorage',
                        'Hawaii-Aleutian'               => 'America/Adak',
                        'Hawaii-Aleutian Time (no DST)' => 'Pacific/Honolulu',
                    ],
                ])
                ->add('tags', TextType::class, [
                    'mapped' => false,
                    'attr'   => [
                        'placeholder' => 'Add search keywords to your event.',
                    ],
                ]);

        if ($school) {
            $builder->add('schoolContact', EntityType::class, [
                'class'        => User::class,
                'choice_label' => 'fullName',
                'placeholder'  => 'Tell attendees who is organizing this event.',
                'expanded'     => false,
                'multiple'     => false,
                'choices'      => $this->userRepository->findContactsBySchool($school),
            ]);
        }

        if ($company) {
            $builder->add('employeeContact', EntityType::class, [
                'class'         => ProfessionalUser::class,
                'choice_label'  => 'fullName',
                'placeholder'  => 'Tell attendees who is organizing this event.',
                'expanded'      => false,
                'multiple'      => false,
                'query_builder' => function (EntityRepository $er) use ($company) {
                    return $er->createQueryBuilder('p')
                              ->where('p.company = :company')
                              ->setParameter('company', $company);
                },
            ]);
        }


        $builder->get('startDateAndTime')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Industry $industry */
            $data = $event->getForm()->getData();
            $name = "josh";

        });

        $builder->add('secondaryIndustries', CollectionType::class, [
            'entry_type' => HiddenType::class,
            'label'      => false,
            'allow_add'  => true,
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'school'  => null,
            'company' => null,
        ]);

    }

    public function getBlockPrefix()
    {
        return 'experience';
    }
}
