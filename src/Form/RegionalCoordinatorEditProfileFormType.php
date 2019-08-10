<?php

namespace App\Form;

use App\Entity\EducatorUser;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RegionalCoordinator;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SecondaryIndustry;
use App\Entity\StateCoordinator;
use App\Entity\StudentUser;
use App\Entity\User;
use App\Util\FormHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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

class RegionalCoordinatorEditProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('firstName', TextType::class, [])
            ->add('lastName', TextType::class, [])
            ->add('email')
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RegionalCoordinator::class,
            'validation_groups' => function (FormInterface $form) {
                return ['REGIONAL_COORDINATOR_EDIT'];
            },
        ]);

        $resolver->setRequired([
            'skip_validation'
        ]);
    }
}
