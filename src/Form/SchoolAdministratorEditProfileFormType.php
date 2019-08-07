<?php

namespace App\Form;

use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SecondaryIndustry;
use App\Entity\User;
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

class SchoolAdministratorEditProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('firstName', TextType::class, [
                'block_prefix' => 'wrapped_text',
            ])
            ->add('lastName', TextType::class)
            ->add('email')
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SchoolAdministrator::class,
            'validation_groups' => function (FormInterface $form) {
                return ['EDIT'];
            },
        ]);

        $resolver->setRequired([
            'skip_validation'
        ]);
    }

    private function localize_us_number($phone) {
        $numbers_only = preg_replace("/[^\d]/", "", $phone);
        return preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $numbers_only);
    }
}
