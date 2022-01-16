<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\EducatorUser;
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
use App\Service\NotificationPreferencesManager;
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
     * @param  SecondaryIndustryRepository  $secondaryIndustryRepository
     * @param  UserRepository  $userRepository
     * @param  StateRepository  $stateRepository
     * @param  Geocoder  $geocoder
     * @param  TagRepository  $tagRepository
     * @param  EntityManagerInterface  $entityManager
     */
    public function __construct(
        SecondaryIndustryRepository $secondaryIndustryRepository,
        UserRepository $userRepository,
        StateRepository $stateRepository,
        Geocoder $geocoder,
        TagRepository $tagRepository,
        EntityManagerInterface $entityManager
    ) {
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

        if (isset($forms['title'])) {
            $forms['title']->setData($viewData->getTitle());
        }

        if (isset($forms['about'])) {
            $forms['about']->setData($viewData->getAbout());
        }

        if (isset($forms['timezone'])) {
            $forms['timezone']->setData($viewData->getTimezone());
        }

        if (isset($forms['isRecurring'])) {
            $forms['isRecurring']->setData($viewData->getIsRecurring());
        }

        if (isset($forms['schoolContact'])) {
            $forms['schoolContact']->setData($viewData->getSchoolContact());
        }

        if (isset($forms['employeeContact'])) {
            $forms['employeeContact']->setData($viewData->getEmployeeContact());
        }

        if (isset($forms['addressSearch'])) {
            $forms['addressSearch']->setData($viewData->getAddressSearch());
        }

        if (isset($forms['type'])) {
            $forms['type']->setData($viewData->getType());
        }

        if (isset($forms['startDate'])) {
            $forms['startDate']->setData(new DateTime());
        }

        if (isset($forms['startTime'])) {
            $forms['startTime']->setData('19:30');
        }

        if (isset($forms['endDate'])) {
            $forms['endDate']->setData(new DateTime('+1 day'));
        }

        if (isset($forms['endTime'])) {
            $forms['endTime']->setData('20:30');
        }

        if (isset($forms['startDate'], $forms['startTime'])
            && $startDateAndTime = $viewData->getStartDateAndTime()
        ) {
            $forms['startDate']->setData($startDateAndTime);
            $forms['startTime']->setData($startDateAndTime->format('H:i'));
        }

        if (isset($forms['endDate'], $forms['endTime'])
            && $endDateAndTime = $viewData->getEndDateAndTime()
        ) {
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

        if (isset($forms['tags'])) {
            $forms['tags']->setData(json_encode($tags));
        }
    }

    public function mapFormsToData($forms, &$viewData): void
    {
        /** @var SchoolExperience|CompanyExperience $viewData */

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $startDate = isset($forms['startDate']) ? $forms['startDate']->getData()
            : null;
        $startTime = isset($forms['startTime']) ? $forms['startTime']->getData()
            : null;
        $endDate   = isset($forms['endDate']) ? $forms['endDate']->getData()
            : null;
        $endTime   = isset($forms['endTime']) ? $forms['endTime']->getData()
            : null;
        $tags      = isset($forms['tags']) ? $forms['tags']->getData() : [];

        if ($startDate && $startTime) {
            $startDateAndTime = clone $startDate;

            [$hours, $minutes] = explode(":", $startTime);

            $startDateAndTime->add(new \DateInterval('PT'.$hours.'H'));
            $startDateAndTime->add(new \DateInterval('PT'.$minutes.'M'));
            $viewData->setStartDateAndTime($startDateAndTime);

            $utcStartDateAndTime = clone $startDateAndTime;
            $utcStartDateAndTime->setTimezone(new DateTimeZone("UTC"));
            $viewData->setUtcStartDateAndTime($utcStartDateAndTime);
        }

        if ($endDate && $endTime) {
            $endDateAndTime = clone $endDate;
            [$hours, $minutes] = explode(":", $endTime);

            $endDateAndTime->add(new \DateInterval('PT'.$hours.'H'));
            $endDateAndTime->add(new \DateInterval('PT'.$minutes.'M'));
            $viewData->setEndDateAndTime($endDateAndTime);

            $utcEndDateAndTime = clone $endDateAndTime;
            $utcEndDateAndTime->setTimezone(new DateTimeZone("UTC"));
            $viewData->setUtcEndDateAndTime($utcEndDateAndTime);
        }

        if (isset($forms['title'])) {
            $viewData->setTitle($forms['title']->getData());
        }

        if (isset($forms['about'])) {
            $viewData->setAbout($forms['about']->getData());
        }

        if (isset($forms['type'])) {
            $viewData->setType($forms['type']->getData());
        }

        if (isset($forms['isRecurring'])) {
            $viewData->setIsRecurring($forms['isRecurring']->getData());
        }

        if ($viewData instanceof SchoolExperience
            && isset($forms['schoolContact'])
        ) {
            $viewData->setSchoolContact($forms['schoolContact']->getData());
        }

        if ($viewData instanceof CompanyExperience
            && isset($forms['employeeContact'])
        ) {
            $viewData->setEmployeeContact($forms['employeeContact']->getData());
        }

        if (isset($forms['timezone'])) {
            $viewData->setTimezone($forms['timezone']->getData());
        }

        if (isset($forms['addressSearch'])) {
            $viewData->setAddressSearch($forms['addressSearch']->getData());
            $addressSearch = $forms['addressSearch']->getData();

            try {
                $addressComponents
                    = $this->geocoder->getAddressComponentsFromSearchString($addressSearch);
                $viewData->setState($addressComponents['state']);
                $viewData->setCity($addressComponents['city']);
                $viewData->setStreet($addressComponents['street']);
                $viewData->setZipcode($addressComponents['postalCode']);

                if ($coordinates
                    = $this->geocoder->geocode($viewData->getFormattedAddress())
                ) {
                    $viewData->setLongitude($coordinates['lng']);
                    $viewData->setLatitude($coordinates['lat']);
                }
            } catch (\Exception $exception) {
                // do nothing
            }
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

        /** @var Experience $experience */
        $experience = $builder->getData();

        $builder->add('title', TextType::class, [
            'attr' => [
                'placeholder' => $school ? 'How to Succeed in a Job Interview'
                    : 'Career Fair – Emergency Medical Services',
            ],
        ])->add('about', TextareaType::class, [

        ])->add('type', EntityType::class, [
            'class'         => RolesWillingToFulfill::class,
            'choice_label'  => 'eventName',
            'expanded'      => false,
            'multiple'      => false,
            'placeholder'   => 'Tell attendees what type of event this is.',
            'query_builder' => function (EntityRepository $er) use (
                $company,
                $school
            ) {
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
        ])->add('addressSearch', TextType::class, [
            'attr' => [
                'autocomplete' => true,
                'placeholder'  => 'Enter a location.',
            ],
        ])->add('timezone', ChoiceType::class, [
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
        ])->add('tags', TextType::class, [
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
                'placeholder'   => 'Tell attendees who is organizing this event.',
                'expanded'      => false,
                'multiple'      => false,
                'query_builder' => function (EntityRepository $er) use ($company
                ) {
                    return $er->createQueryBuilder('p')
                        ->where('p.company = :company')->setParameter('company',
                            $company);
                },
            ]);
        }

        // We do not allow child events to be changed into a recurring event
        if (!$experience->getParentEvent()) {
            $builder->add('isRecurring', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices'  => [
                    'Single Event'    => false,
                    'Recurring Event' => true,
                ],
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var Experience $experience */
                $experience = $event->getData();
                $form       = $event->getForm();

                $this->isRecurringEventHandler($form,
                    $experience->getIsRecurring());
            });

        if (!$experience->getParentEvent()) {
            $builder->get('isRecurring')
                ->addEventListener(FormEvents::POST_SUBMIT,
                    function (FormEvent $event) {
                        /** @var Industry $industry */
                        $data = $event->getForm()->getData();
                        $form = $event->getForm()->getParent();

                        if (!$form) {
                            return;
                        }

                        $this->isRecurringEventHandler($form, $data);
                    });
        }


        $builder->addEventListener(FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                if (isset($data['isRecurring'])) {
                    $isRecurring = !!$data['isRecurring'];

                    if (!$isRecurring) {
                        if (!isset($data['startDate'])) {
                            $data['startDate']
                                = (new DateTime())->format("m/d/Y");
                        }

                        if (!isset($data['startTime'])) {
                            $data['startTime'] = '19:30';
                        }

                        if (!isset($data['endDate'])) {
                            $data['endDate']
                                = (new DateTime('+1 day'))->format("m/d/Y");
                        }

                        if (!isset($data['endTime'])) {
                            $data['endTime'] = '20:30';
                        }
                    }
                }

                $event->setData($data);
            });
    }

    private function isRecurringEventHandler(
        FormInterface $form,
        bool $isRecurring
    ) {
        if ($isRecurring) {
            // if it is recurring remove fields not needed
            $form->remove('startDateAndTime')->remove('endDateAndTime')
                ->remove('startDate')->remove('endDate')->remove('startTime')
                ->remove('endTime');

            return;
        }

        if (!$form->has('startDateAndTime')) {
            $form->add('startDateAndTime', HiddenType::class, []);
        }

        if (!$form->has('endDateAndTime')) {
            $form->add('endDateAndTime', HiddenType::class, []);
        }

        if (!$form->has('startDate')) {
            $form->add('startDate', DateType::class, [
                'mapped'      => false,
                'widget'      => 'single_text',
                'html5'       => false,
                'format'      => 'MM/dd/yyyy',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a start date.',
                        'groups'  => ['EXPERIENCE'],
                    ]),
                ],
            ]);
        }

        if (!$form->has('endDate')) {
            $form->add('endDate', DateType::class, [
                'mapped'      => false,
                'widget'      => 'single_text',
                'html5'       => false,
                'format'      => 'MM/dd/yyyy',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select an end date.',
                        'groups'  => ['EXPERIENCE'],
                    ]),
                ],
            ]);
        }

        if (!$form->has('startTime')) {
            $form->add('startTime', ChoiceType::class, [
                'mapped'   => false,
                'expanded' => false,
                'multiple' => false,
                'choices'  => $this->hoursRange(0, 86400, 60 * 30),
            ]);
        }

        if (!$form->has('endTime')) {
            $form->add('endTime', ChoiceType::class, [
                'expanded' => false,
                'multiple' => false,
                'choices'  => $this->hoursRange(0, 86400, 60 * 30),
                'mapped'   => false,
            ]);
        }
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
