<?php

namespace App\Form;

use App\Entity\EducatorUser;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SecondaryIndustry;
use App\Entity\User;
use App\Repository\SecondaryIndustryRepository;
use App\Util\FormHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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

class EducatorEditProfileFormType extends AbstractType
{
    use FormHelper;

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

        $builder
            ->add('firstName', TextType::class, [
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('lastName', TextType::class, [
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('displayName', TextType::class)
            ->add('educatorId', TextType::class, [
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('username')
            ->add('email')
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password'
            ])
            ->add('interests', TextAreaType::class);


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

        $this->setupImmutableFields($builder, $options, [
            'firstName',
            'lastName',
            'educatorId'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EducatorUser::class,
            'validation_groups' => function (FormInterface $form) {
                return ['EDUCATOR_USER'];
            },
        ]);

        $resolver->setRequired([
            'skip_validation'
        ]);
    }
}