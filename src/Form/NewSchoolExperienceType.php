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
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;


use App\Repository\UserRepository;

class NewSchoolExperienceType extends AbstractType
{
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
     * @param SecondaryIndustryRepository $secondaryIndustryRepository
     */
    public function __construct(SecondaryIndustryRepository $secondaryIndustryRepository, UserRepository $userRepository)
    {
        $this->secondaryIndustryRepository = $secondaryIndustryRepository;
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var School $school */
        $school = $options['school'];

        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'placeholder' => 'How to Succeed in a Job Interview'
                ]
            ])->add('about', TextareaType::class, [])
            ->add('type', EntityType::class, [
                'class' => RolesWillingToFulfill::class,
                'choice_label' => 'eventName',
                'expanded'  => false,
                'multiple'  => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('r')
                        ->where('r.inSchoolEventDropdown = :inSchoolEventDropdown')
                        ->setParameter('inSchoolEventDropdown', true);
                },
            ])
            // ->add('schoolContact', EntityType::class, [
            //     'class' => SchoolAdministrator::class,
            //     'choice_label' => 'fullName',
            //     'expanded'  => false,
            //     'multiple'  => false,
            //     'query_builder' => function (EntityRepository $er) use ($school) {
            //         return $er->createQueryBuilder('sa')
            //             ->innerJoin('sa.schools', 'schools')
            //             ->where('schools.id = :id')
            //             ->setParameter('id', $school->getId());
            //     },
            // ])
            ->add('schoolContact', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullName',
                'placeholder' => 'Please select a school administrator or educator as the main point of contact for this experience',
                'expanded' => false,
                'multiple' => false,
                'choices' => $this->userRepository->findContactsBySchool($school)
            ])
            ->add('experienceAddressSearch', TextType::class, [
                'attr' => [
                    'autocomplete' => true,
                    'placeholder' => 'Search for an address',
                ],
                // todo re-add for the edit view???
                // todo can we default to the school or no?
                //'data' => $company->getFormattedAddress(),
            ])->add('street', TextType::class, [])
            ->add('city', TextType::class, [])
            ->add('state', EntityType::class, [
                'class' => State::class,
                'choice_label' => 'name',
                'expanded'  => false,
                'multiple'  => false,
            ])
            ->add('zipcode', TextType::class, [])
            ->add('startDateAndTime', HiddenType::class, [])
            ->add('endDateAndTime', HiddenType::class, [])

            ->add('startDate', TextType::class, [
                'mapped' => false
            ])
            ->add('endDate', TextType::class, [
                'mapped' => false
            ]) ->add('startTime', TextType::class, [
                'mapped' => false
            ])
            ->add('endTime', TextType::class, [
                'mapped' => false
            ]);



            // ->add('startDateAndTime', TextType::class, [])
            // ->add('endDateAndTime', TextType::class, []);

        $builder->add('secondaryIndustries', CollectionType::class, [
            'entry_type' => HiddenType::class,
            'label' => false,
            'allow_add' => true,
        ]);

        $builder->get('secondaryIndustries')
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
            ));

        $builder->get('startDateAndTime')
            ->addModelTransformer(new CallbackTransformer(
                function ($date) {
                    if($date) {
                        return $date->format('m/d/Y g:i A');
                    }
                    return '';
                },
                function ($date) {
                    return DateTime::createFromFormat('m/d/Y g:i A', $date);
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
                    return DateTime::createFromFormat('m/d/Y g:i A', $date);
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SchoolExperience::class,
            'validation_groups' => ['SCHOOL_EXPERIENCE'],
        ]);

        $resolver->setRequired(['school']);
    }

    public function getBlockPrefix()
    {
        return 'experience';
    }
}
