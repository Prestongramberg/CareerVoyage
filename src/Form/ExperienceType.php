<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\CompanyExperience;
use App\Entity\Course;
use App\Entity\Experience;
use App\Entity\Grade;
use App\Entity\Industry;
use App\Entity\Lesson;
use App\Entity\ProfessionalUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SchoolExperience;
use App\Entity\State;
use App\Entity\User;
use App\Repository\SecondaryIndustryRepository;
use App\Service\NotificationPreferencesManager;
use App\Util\TimeHelper;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use DoctrineExtensions\Query\Mysql\Exp;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
     * EditCompanyExperienceType constructor.
     *
     * @param SecondaryIndustryRepository $secondaryIndustryRepository
     */
    public function __construct(
        SecondaryIndustryRepository $secondaryIndustryRepository, UserRepository $userRepository)
    {
        $this->secondaryIndustryRepository = $secondaryIndustryRepository;
        $this->userRepository              = $userRepository;
    }

    public function mapDataToForms($viewData, $forms): void
    {
        /** @var Experience $viewData */

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
        $forms['timezone']->setData($viewData->getTimezone());
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


        //$forms['green']->setData($viewData->getGreen());
        //$forms['blue']->setData($viewData->getBlue());
    }

    public function mapFormsToData($forms, &$viewData): void
    {
        /** @var Experience $viewData */

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $startDate = $forms['startDate']->getData();
        $startTime = $forms['startTime']->getData();
        $endDate   = $forms['endDate']->getData();
        $endTime   = $forms['endTime']->getData();

        if ($startDate && $startTime) {
            $startDateAndTime = clone $startDate;
            [$hours, $minutes] = explode(":", $startTime);

            $startDateAndTime->add(new \DateInterval('PT' . $hours . 'H'));
            $startDateAndTime->add(new \DateInterval('PT' . $minutes . 'M'));
            $viewData->setStartDateAndTime($startDateAndTime);
        }

        if ($endDate && $endTime) {
            $endDateAndTime = clone $endDate;
            [$hours, $minutes] = explode(":", $endTime);

            $endDateAndTime->add(new \DateInterval('PT' . $hours . 'H'));
            $endDateAndTime->add(new \DateInterval('PT' . $minutes . 'M'));
            $viewData->setEndDateAndTime($endDateAndTime);
        }

        $viewData->setTitle($forms['title']->getData());
        $viewData->setAbout($forms['about']->getData());
        $viewData->setType($forms['type']->getData());
        $viewData->setExperienceAddressSearch($forms['experienceAddressSearch']->getData());
        $viewData->setSchoolContact($forms['schoolContact']->getData());
        $viewData->setState($forms['state']->getData());
        $viewData->setCity($forms['city']->getData());
        $viewData->setStreet($forms['street']->getData());
        $viewData->setTimezone($forms['timezone']->getData());
        $viewData->setZipcode($forms['zipcode']->getData());

        // as data is passed by reference, overriding it will change it in
        // the form object as well
        // beware of type inconsistency, see caution below
        /*$viewData = new Color(
            $forms['red']->getData(),
            $forms['green']->getData(),
            $forms['blue']->getData()
        );*/
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // todo you could have base experience form fields and then make it extend parent form types? SchoolExperienceType and
        //  CompanyExperienceType? Just a thought.

        $builder->setDataMapper($this);

        /** @var School $school */
        $school = $options['school'];

        $builder->add('title', TextType::class, [
            'attr' => [
                'placeholder' => 'How to Succeed in a Job Interview',
            ],
        ])->add('about', TextareaType::class, [

        ])->add('type', EntityType::class, [
                'class'         => RolesWillingToFulfill::class,
                'choice_label'  => 'eventName',
                'expanded'      => false,
                'multiple'      => false,
                'placeholder' => 'Please choose an experience type',
                'query_builder' => function (
                    EntityRepository $er) {
                    return $er->createQueryBuilder('r')->where('r.inSchoolEventDropdown = :inSchoolEventDropdown')->setParameter('inSchoolEventDropdown', true);
                },
            ])->add('schoolContact', EntityType::class, [
                'class'        => User::class,
                'choice_label' => 'fullName',
                'placeholder'  => 'Please choose a main point of contact for this experience',
                'expanded'     => false,
                'multiple'     => false,
                'choices'      => $this->userRepository->findContactsBySchool($school),
            ])->add('experienceAddressSearch', TextType::class, [
                'attr' => [
                    'autocomplete' => true,
                    'placeholder'  => 'Search for an address',
                ],
                // todo re-add for the edit view???
                // todo can we default to the school or no?
                //'data' => $company->getFormattedAddress(),
            ])->add('street', TextType::class, [

            ])->add('city', TextType::class, [

            ])->add('state', EntityType::class, [
                'class'        => State::class,
                'choice_label' => 'name',
                'expanded'     => false,
                'multiple'     => false,
            ])->add('zipcode', TextType::class, [])->add('startDateAndTime', HiddenType::class, [])->add('endDateAndTime', HiddenType::class, [])->add('startDate', DateType::class, [
                'mapped'      => false,
                'widget'      => 'single_text',
                'html5'       => false,
                'format'      => 'MM/dd/yyyy',
                'constraints' => [
                    new NotBlank(['message' => 'Please select a start date']),
                ],
            ])->add('endDate', DateType::class, [
                'mapped'      => false,
                'widget'      => 'single_text',
                'html5'       => false,
                'format'      => 'MM/dd/yyyy',
                'constraints' => [
                    new NotBlank(['message' => 'Please select an end date']),
                ],
            ]); /*->add('startTime', TextType::class, [
                'mapped' => false
            ])
            ->add('endTime', TextType::class, [
                'mapped' => false
            ]);*/

        $builder->add('startTime', ChoiceType::class, [
            'expanded' => false,
            'multiple' => false,
            'choices'  => $this->hoursRange(0, 86400, 60 * 30),
            'mapped'   => false,
        ]);

        $builder->add('endTime', ChoiceType::class, [
            'expanded' => false,
            'multiple' => false,
            'choices'  => $this->hoursRange(0, 86400, 60 * 30),
            'mapped'   => false,
        ]);

        $builder->add('timezone', ChoiceType::class, [
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
        ]);


        $builder->get('startDateAndTime')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Industry $industry */
            $data = $event->getForm()->getData();
            $name = "josh";

        });

        /*        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
                    $form = $event->getForm();
                    $data = $event->getData();

                    // todo this could work? But I feel like there are greater implications here
                    $data['startDateAndTime'] = sprintf("%s %s", $data['startDate'], $data['startTime']);
                    $data['endDateAndTime'] = sprintf("%s %s", $data['endDate'], $data['endTime']);

                    $event->setData($data);
                });*/


        // ->add('startDateAndTime', TextType::class, [])
        // ->add('endDateAndTime', TextType::class, []);

        $builder->add('secondaryIndustries', CollectionType::class, [
            'entry_type' => HiddenType::class,
            'label'      => false,
            'allow_add'  => true,
        ]);

        /*        $builder->get('secondaryIndustries')
                    ->addModelTransformer(new CallbackTransformer(
                        function ($secondaryIndustries) {
                            $ids = [];
                            foreach($secondaryIndustries as $secondaryIndustry) {
                                $ids[] = $secondaryIndustry->getId();
                            }

                            return $ids;
                        },
                        function ($ids) {

                            $collection = new ArrayCollection();
                            foreach($ids as $id) {
                                $collection->add($this->secondaryIndustryRepository->find($id));
                            }
                            return $collection;
                        }
                    ));*/

        /*        $builder->get('startDateAndTime')
                    ->addModelTransformer(new CallbackTransformer(
                        function ($date) {
                            if($date) {
                                return $date->format('m/d/Y g:i A');
                            }
                            return '';
                        },
                        function ($date) {
                            return DateTime::createFromFormat('m/d/Y H:i', $date);
                        }
                    ));

                $builder->get('endDateAndTime')
                    ->addModelTransformer(new CallbackTransformer(
                        function ($date) {
                            if($date) {
                                return $date->format('m/d/Y g:i A');
                            }
                            return '';
                        },
                        function ($date) {
                            return DateTime::createFromFormat('m/d/Y H:i', $date);
                        }
                    ));*/
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => SchoolExperience::class,
            'validation_groups' => ['SCHOOL_EXPERIENCE'],
        ]);

        $resolver->setRequired(['school']);
    }

    public function getBlockPrefix()
    {
        return 'experience';
    }
}
