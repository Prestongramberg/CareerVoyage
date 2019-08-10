<?php

namespace App\Form;

use App\Entity\Career;
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
use DateTime;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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

class NewSchoolExperienceType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var School $school */
        $school = $options['school'];

        $builder
            ->add('title', TextType::class, [])
            ->add('briefDescription', TextareaType::class, [])
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
            ->add('careers', EntityType::class, [
                'class' => Career::class,
                'choice_label' => 'title',
                'expanded'  => true,
                'multiple'  => true,
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'uk-checkbox'];
                }
            ])
            ->add('availableSpaces', NumberType::class, [])
            ->add('payment', NumberType::class, [
                'required' => false,
            ])
            ->add('paymentShownIsPer', ChoiceType::class, [
                'choices'  => Experience::$paymentTypes,
                'expanded'  => false,
                'multiple'  => false,
                'required' => false
            ])
            ->add('schoolContact', EntityType::class, [
                'class' => SchoolAdministrator::class,
                'choice_label' => 'fullName',
                'expanded'  => false,
                'multiple'  => false,
                'query_builder' => function (EntityRepository $er) use ($school) {
                    return $er->createQueryBuilder('sa')
                        ->innerJoin('sa.schools', 'schools')
                        ->where('schools.id = :id')
                        ->setParameter('id', $school->getId());
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
            ->add('startDateAndTime', TextType::class, [])
            ->add('endDateAndTime', TextType::class, []);

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
            'validation_groups' => ['CREATE'],
        ]);

        $resolver->setRequired(['school']);

    }
}
