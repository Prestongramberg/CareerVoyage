<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\EducatorUser;
use App\Entity\Industry;
use App\Entity\ProfessionalUser;
use App\Entity\RolesWillingToFulfill;
use App\Entity\School;
use App\Entity\SchoolAdministrator;
use App\Entity\SecondaryIndustry;
use App\Entity\StudentUser;
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
        /** @var EducatorUser $educator */
        $educator = $options['educator'];

        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('displayName', TextType::class)
            ->add('educatorId', TextType::class, [
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('briefBio', TextareaType::class)
            ->add('linkedinProfile', TextType::class)
            ->add('phone', TextType::class)
            ->add('phoneExt', TextType::class, [
                'attr' => [
                    'placeholder' => '123'
                ]
            ])
            ->add('username')
            ->add('email')
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password'
            ])
            ->add('interests', TextAreaType::class)
            ->add('isEmailHiddenFromProfile', ChoiceType::class, [
                'choices'  => [
                    'Yes' => true,

                    'No' => false,
                ],
            ])
            ->add('isPhoneHiddenFromProfile', ChoiceType::class, [
                'choices'  => [
                    'Yes' => true,
                    'No' => false,
                ],
            ]);


        $builder->add('secondaryIndustries', CollectionType::class, [
            'entry_type' => HiddenType::class,
            'label' => false,
            'allow_add' => true,
        ]);



        $builder->get('phone')->addModelTransformer(new CallbackTransformer(
            function ($phone) {
                return str_replace('-', '', $phone);
            },
            function ($phone) {
                return $this->localize_us_number($phone);
            }
        ));

        $builder->add('studentUsers', EntityType::class, [
            'class' => StudentUser::class,
            'multiple' => true,
            'expanded' => true,
            'choice_attr' => function($choice, $key, $value) {
                return ['class' => 'uk-checkbox'];
            },
            'choice_label' => function (StudentUser $student) {
                return $student->getFullName();
            },
            'query_builder' => function (EntityRepository $er) use ($educator) {
                return $er->createQueryBuilder('s')
                    ->where('s.school = :school')
                    ->setParameter('school', $educator->getSchool())
                    ->orderBy('s.firstName', 'ASC');
            }
            ]);

        $builder->add('myCourses', EntityType::class, [
            'class' => Course::class,
            'multiple' => true,
            'expanded' => true,
            'choice_attr' => function($choice, $key, $value) {
                return ['class' => 'uk-checkbox'];
            },
            'choice_label' => function (Course $course) {
                return $course->getTitle();
            }
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

        $this->
        setupImmutableFields($builder, $options, [
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
            'skip_validation',
            'educator'
        ]);
    }

    private function localize_us_number($phone) {
        $numbers_only = preg_replace("/[^\d]/", "", $phone);
        return preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $numbers_only);
    }
}
