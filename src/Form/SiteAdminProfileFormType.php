<?php

namespace App\Form;

use App\Entity\AdminUser;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SecondaryIndustry;
use App\Entity\Site;
use App\Entity\SiteAdminUser;
use App\Entity\State;
use App\Entity\User;
use App\Repository\SiteRepository;
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

class SiteAdminProfileFormType extends AbstractType
{
    use FormHelper;

    /**
     * @var SiteRepository
     */
    private $siteRepository;

    /**
     * SiteAdminProfileFormType constructor.
     * @param SiteRepository $siteRepository
     */
    public function __construct(SiteRepository $siteRepository)
    {
        $this->siteRepository = $siteRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $imageConstraints = [
            new Image([
                'maxSize' => '5M'
            ])
        ];

        $builder
            ->add('firstName', TextType::class, [
                'block_prefix' => 'wrapped_text',
            ])
            ->add('lastName', TextType::class)
            ->add('email')
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password'
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'name',
                'expanded'  => false,
                'multiple'  => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s');
                },
                'attr' => [
                    'disabled' => true
                ]
            ]);

        $this->setupImmutableObjectField($builder, $options, ['site'], $this->siteRepository);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SiteAdminUser::class,
            'validation_groups' => ['EDIT']
        ]);

    }
}
