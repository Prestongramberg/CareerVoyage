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
use App\Entity\State;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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

class EditCompanyExperienceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Company $company */
        $company = $options['company'];

        $builder
            ->add('title', TextType::class, [])
            ->add('briefDescription', TextType::class, [])
            ->add('about', TextareaType::class, [])
            ->add('type', ChoiceType::class, [
                'choices'  => Experience::$types
            ])
            ->add('careers', EntityType::class, [
                'class' => Career::class,
                'choice_label' => 'title',
                'expanded'  => true,
                'multiple'  => true,
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
            ->add('startDateAndTime', DateType::class, [
                'widget' => 'single_text',

                // prevents rendering it as type="date", to avoid HTML5 date pickers
                'html5' => false,

                // adds a class that can be selected in JavaScript
                'attr' => ['class' => 'js-datepicker'],
            ])
            ->add('endDateAndTime', DateType::class, [
                'widget' => 'single_text',

                // prevents rendering it as type="date", to avoid HTML5 date pickers
                'html5' => false,

                // adds a class that can be selected in JavaScript
                'attr' => ['class' => 'js-datepicker'],
            ])
            ->add('length', NumberType::class, []);

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
