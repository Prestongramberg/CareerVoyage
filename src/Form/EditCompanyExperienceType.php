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

class EditCompanyExperienceType extends AbstractType
{
    /**
     * @var SecondaryIndustryRepository
     */
    private $secondaryIndustryRepository;

    /**
     * EditCompanyExperienceType constructor.
     * @param SecondaryIndustryRepository $secondaryIndustryRepository
     */
    public function __construct(SecondaryIndustryRepository $secondaryIndustryRepository)
    {
        $this->secondaryIndustryRepository = $secondaryIndustryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Company $company */
        $company = $options['company'];

        $builder
            ->add('title', TextType::class, [])
            ->add('briefDescription', TextType::class, [])
            ->add('about', TextareaType::class, [])
            ->add('type', EntityType::class, [
                'class' => RolesWillingToFulfill::class,
                'choice_label' => 'eventName',
                'expanded'  => false,
                'multiple'  => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('r')
                        ->where('r.inEventDropdown = :inEventDropdown')
                        ->setParameter('inEventDropdown', true);
                },
            ])
            ->add('availableSpaces', NumberType::class, [])
            ->add('payment', TextType::class, [])
            ->add('paymentShownIsPer', ChoiceType::class, [
                'choices'  => Experience::$paymentTypes,
                'expanded'  => false,
                'multiple'  => false,
                'required' => false
            ])
            ->add('employeeContact', EntityType::class, [
                'class' => ProfessionalUser::class,
                'choice_label' => 'fullName',
                'expanded'  => false,
                'multiple'  => false,
                'required' => true,
                'query_builder' => function (EntityRepository $er) use ($company) {
                    return $er->createQueryBuilder('p')
                        ->where('p.company = :company')
                        ->setParameter('company', $company);
                },
            ])
            ->add('email', TextType::class, [])
            ->add('street', TextType::class, [])
            ->add('city', TextType::class, [])
            ->add('state', EntityType::class, [
                'class' => State::class,
                'choice_label' => 'name',
                'expanded'  => false,
                'multiple'  => false,
            ])
            ->add('zipcode', TextType::class, [])
            ->add('dateRange', null, array("mapped" => false))
            ->add('startDateAndTime', hiddenType::class, [])
            ->add('endDateAndTime', HiddenType::class, []);
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
                        if(!$id) {
                            continue;
                        }
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
            'data_class' => CompanyExperience::class,
            'validation_groups' => ['EDIT'],
        ]);

        $resolver->setRequired(['company']);

    }
}
