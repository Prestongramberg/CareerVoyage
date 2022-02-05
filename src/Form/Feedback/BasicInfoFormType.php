<?php

namespace App\Form\Feedback;

use App\Entity\AdminUser;
use App\Entity\CompanyExperience;
use App\Entity\Feedback;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SchoolExperience;
use App\Entity\SecondaryIndustry;
use App\Entity\StudentReviewCompanyExperienceFeedback;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
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

class BasicInfoFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', TextType::class, [])
                ->add('fullName', TextType::class, [
                    'constraints' => [
                        new NotNull(['message' => 'Please enter your name.']),
                    ],
                ])
                ->add('feedbackProvider', ChoiceType::class, [
                    'choices'     => [
                        'Educator'     => 'Educator',
                        'Student'      => 'Student',
                        'Professional' => 'Professional',
                    ],
                    'constraints' => [
                        new NotNull(['message' => 'Please select your role.']),
                    ],
                    'multiple'    => false,
                    'expanded'    => true,
                ]);
    }

    public function getBlockPrefix()
    {
        return 'basicInfo';
    }

}
